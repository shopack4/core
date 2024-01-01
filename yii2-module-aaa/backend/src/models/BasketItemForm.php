<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\models;

use Yii;
use yii\base\Model;
use yii\web\UnauthorizedHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\db\Expression;
use yii\web\NotFoundHttpException;
use Ramsey\Uuid\Uuid;
use shopack\base\common\helpers\Json;
use shopack\base\common\helpers\ArrayHelper;
use shopack\base\common\validators\GroupRequiredValidator;
use shopack\base\backend\helpers\AuthHelper;
use shopack\aaa\common\enums\enuUserStatus;
use shopack\base\common\security\RsaPublic;
use shopack\aaa\backend\models\VoucherModel;
use shopack\aaa\common\enums\enuVoucherType;
use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\aaa\backend\models\BasketForm;

class BasketItemForm extends Model
{
  public static function addItem()
  {
		$service = $_POST['service'];
		$data = $_POST['data'];

		if (empty(Yii::$app->controller->module->servicesPublicKeys[$service]))
			$data = base64_decode($data);
		else
			$data = RsaPublic::model(Yii::$app->controller->module->servicesPublicKeys[$service])->decrypt($data);

		$data = Json::decode($data);

		$userid = $data['userid'];
    $items  = $data['items'];

    //voucher
    $voucherModel = BasketForm::getCurrentBasket($userid);

    if ($voucherModel == null) {
      $voucherModel = new VoucherModel();
      $voucherModel->vchOwnerUserID = $userid;
      $voucherModel->vchType        = enuVoucherType::Basket;
      $voucherModel->vchAmount      = 0;
    }

    $vchItems = $voucherModel->vchItems ?? [];

    foreach ($items as $itemToAdd) {
      $service   = $itemToAdd['service'];
      // $slbkey    = $itemToAdd['slbkey'];
      $slbid     = $itemToAdd['slbid'];
      $desc      = $itemToAdd['desc'];
      $qty       = $itemToAdd['qty'];
      $maxqty    = $itemToAdd['maxqty'] ?? null;
      $unitprice = $itemToAdd['unitprice'];
      //additives
      $discount  = $itemToAdd['discount'] ?? 0;
      //tax
      //totalprice

      $voucherModel->vchAmount += ($unitprice * $qty);
      if ($discount > 0)
        $voucherModel->vchDiscountAmount = ($voucherModel->vchDiscountAmount ?? 0) + $discount;

      //check current items
      if (empty($maxqty) == false) {
        $curqty = 0;
        foreach ($vchItems as $vchItem) {
          if ($vchItem['service'] == $service
            // && $vchItem['slbkey'] == $slbkey
            && $vchItem['slbid'] == $slbid
          ) {
            $curqty += $vchItem['qty'];

            if ($curqty >= $maxqty)
              throw new UnprocessableEntityHttpException('Max qty of this item exists in basket');
          }
        }
      }

      if (empty($itemToAdd['key']))
        $itemToAdd['key'] = Uuid::uuid4()->toString();

      $vchItems[] = array_merge($itemToAdd, [
        // 'service'   => $service,
        // 'slbkey'    => $slbkey,
        // 'slbid'     => $slbid,
        // 'desc'      => $desc,
        // 'qty'       => $qty,
        // 'unitprice' => $unitprice,
      ]);
    }

    $voucherModel->vchItems = $vchItems;

    //clear basket delivery settings
    $voucherModel->vchDeliveryMethodID = null;
    $voucherModel->vchDeliveryAmount = null;

    $voucherModel->vchTotalAmount =
        $voucherModel->vchAmount
      - ($voucherModel->vchDiscountAmount ?? 0);

    return $voucherModel->save();
  }

  public static function removeItem($key)
  {
    $voucherModel = BasketForm::getCurrentBasket();

    if ($voucherModel == null)
      throw new NotFoundHttpException('Basket not found');

    $vchItems = $voucherModel->vchItems ?? [];

    //check current items
    foreach ($vchItems as $k => $vchItem) {
      if ($vchItem['key'] == $key) {

        //start transaction
		  	$transaction = Yii::$app->db->beginTransaction();

        try {
          unset($vchItems[$k]);

          $voucherModel->vchAmount -= ($vchItem['unitPrice'] * $vchItem['qty']);
          $voucherModel->vchItems = $vchItems;

          if (($voucherModel->vchPaidByWallet ?? 0) > $voucherModel->vchAmount) {
            $walletReturnAmount = $voucherModel->vchPaidByWallet - $voucherModel->vchAmount;

            $walletModel = WalletModel::ensureIHaveDefaultWallet();

            //2.1: create wallet transaction
            $walletTransactionModel = new WalletTransactionModel();
            $walletTransactionModel->wtrWalletID		= $walletModel->walID;
            $walletTransactionModel->wtrVoucherID		= $voucherModel->vchID;
            $walletTransactionModel->wtrAmount			= $walletReturnAmount;
            $walletTransactionModel->save();

            //2.2: increase wallet amount
            $walletTableName = WalletModel::tableName();
            $qry =<<<SQL
  UPDATE {$walletTableName}
     SET walRemainedAmount = walRemainedAmount + {$walletReturnAmount}
   WHERE walID = {$walletModel->walID}
SQL;
            $rowsCount = Yii::$app->db->createCommand($qry)->execute();

            //3: save to the voucher
            $voucherModel->vchPaidByWallet = $voucherModel->vchAmount;
            $voucherModel->vchTotalPaid = $voucherModel->vchTotalPaid - $walletReturnAmount;
          }

          if ($voucherModel->vchStatus == enuVoucherStatus::Settled)
            $voucherModel->vchStatus == enuVoucherStatus::New;

          //clear basket delivery settings
          $voucherModel->vchDeliveryMethodID = null;
          $voucherModel->vchDeliveryAmount = null;

          $voucherModel->vchTotalAmount = $voucherModel->vchAmount;

          if ($voucherModel->save() !== true)
            throw new UnprocessableEntityHttpException('Error in updating voucher');

          //commit
	        $transaction->commit();

          return true;

        } catch (\Exception $e) {
          if (isset($transaction))
            $transaction->rollBack();
          throw $e;
        } catch (\Throwable $e) {
          if (isset($transaction))
            $transaction->rollBack();
          throw $e;
        }
      }
    }

    throw new NotFoundHttpException('Basket item not found');
  }

}
