<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\models;

use Yii;
use yii\base\Model;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\UnprocessableEntityHttpException;
use shopack\base\common\helpers\HttpHelper;
use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\aaa\common\enums\enuVoucherType;
use shopack\aaa\common\enums\enuWalletStatus;
use yii\web\ForbiddenHttpException;

class OrderPaymentForm extends Model
{
	public $vchID;
	public $walletID;
  public $gatewayType;
  public $callbackUrl;

	public function rules()
	{
		return [
			['vchID', 'required'],
			['walletID', 'safe'],
      ['gatewayType', 'safe'],
			['callbackUrl', 'safe'],
		];
	}

	public function process()
	{
		if ($this->vchID == '')				$this->vchID = null;
		if ($this->walletID == '')		$this->walletID = null;
		if ($this->gatewayType == '')	$this->gatewayType = null;
		if ($this->callbackUrl == '')	$this->callbackUrl = null;

		//validation
		if (Yii::$app->user->isGuest)
			throw new UnauthorizedHttpException("This process is not for guest.");

		if ($this->validate() == false)
			throw new UnauthorizedHttpException(implode("\n", $this->getFirstErrors()));

		$voucherModel = VoucherModel::find()
			->andWhere(['vchID' => $this->vchID])
			->andWhere(['vchType' => enuVoucherType::Invoice])
			->one();

		if ($voucherModel == null)
			throw new NotFoundHttpException('The requested item does not exist.');

		if ($voucherModel->vchStatus != enuVoucherStatus::WaitForPayment)
			throw new UnauthorizedHttpException('وضعیت سفارش باید منتظر پرداخت باشد.');

		if ($voucherModel->vchOwnerUserID != Yii::$app->user->id)
			throw new ForbiddenHttpException('Basket is not yours');

		//process
		$remainedAmount = $voucherModel->vchTotalAmount - $voucherModel->vchTotalPaid;

		if (($this->walletID === null) && empty($this->gatewayType) && ($remainedAmount > 0))
			throw new UnprocessableEntityHttpException('One of the wallet or payment type must be selected');

		$walletAmount = 0;
		if (($remainedAmount > 0) && ($this->walletID !== null) && ($this->walletID >= 0)) {
			$walletModel = WalletModel::find()
				->andWhere(['walOwnerUserID' => Yii::$app->user->id])
				->andWhere(['!=', 'walStatus', enuWalletStatus::Removed]);

			if ($this->walletID == 0)
				$walletModel->andWhere(['walIsDefault' => true]);
			else
				$walletModel->andWhere(['walID' => $this->walletID]);

			$walletModel = $walletModel->one();
			if ($walletModel == null)
				throw new NotFoundHttpException('Wallet not found');

			$this->walletID = $walletModel->walID;

			if ($walletModel->walRemainedAmount > $remainedAmount) {
				$walletAmount = $remainedAmount;
			} else {
				$walletAmount = $walletModel->walRemainedAmount;
			}
		}

		$remainedAmount -= $walletAmount;
		if (($remainedAmount > 0) && empty($this->gatewayType))
			throw new UnprocessableEntityHttpException('Payment type not provided');

    //start transaction
		if ($walletAmount > 0 || $remainedAmount > 0)
			$transaction = Yii::$app->db->beginTransaction();

		$walletTableName = WalletModel::tableName();
		$voucherTableName = VoucherModel::tableName();

		$fnGetConstQouted = function($value) { return "'{$value}'"; };

		try {
			if ($walletAmount > 0) {
				//2.1: create wallet transaction
				$walletTransactionModel = new WalletTransactionModel();
				$walletTransactionModel->wtrWalletID	= $this->walletID;
				$walletTransactionModel->wtrVoucherID	= $voucherModel->vchID;
				$walletTransactionModel->wtrAmount		= (-1) * $walletAmount;
				$walletTransactionModel->save();

				//2.2: decrease wallet amount
				$qry =<<<SQL
	UPDATE	{$walletTableName}
		 SET	walRemainedAmount = walRemainedAmount - {$walletAmount}
	 WHERE	walID = {$walletTransactionModel->wtrWalletID}
SQL;
				$rowsCount = Yii::$app->db->createCommand($qry)->execute();

				//3: save to the voucher
				$qry =<<<SQL
	UPDATE	{$voucherTableName}
		 SET	vchPaidByWallet = IFNULL(vchPaidByWallet, 0) + {$walletAmount}
		 	 ,	vchTotalPaid = IFNULL(vchTotalPaid, 0) + {$walletAmount}
	 WHERE	vchID = {$voucherModel->vchID}
SQL;
				$rowsCount = Yii::$app->db->createCommand($qry)->execute();

				$qry =<<<SQL
	UPDATE	{$voucherTableName}
		 SET	vchStatus = IF(vchTotalAmount = IFNULL(vchTotalPaid, 0),
			 			{$fnGetConstQouted(enuVoucherStatus::Settled)},
						{$fnGetConstQouted(enuVoucherStatus::WaitForPayment)}
			 		)
	 WHERE	vchID = {$voucherModel->vchID}
SQL;
				$rowsCount = Yii::$app->db->createCommand($qry)->execute();

				$voucherModel->refresh();
			}

			//------------------------
			if ($remainedAmount == 0) {
				if ($voucherModel->vchStatus != enuVoucherStatus::Settled) {
					$voucherModel->vchStatus = enuVoucherStatus::Settled;
					$voucherModel->save();
				}

				if (isset($transaction))
					$transaction->commit();

				return $voucherModel->processVoucher();
			}
			// else : create online payment out of transaction

			//commit
			if (isset($transaction))
				$transaction->commit();

    } catch (\Exception $e) {
			if (isset($transaction))
	      $transaction->rollBack();
      throw $e;
		} catch (\Throwable $e) {
			if (isset($transaction))
				$transaction->rollBack();
      throw $e;
    }

		if ($remainedAmount > 0) {
			//create online payment
			$onpResult = Yii::$app->paymentManager->createOnlinePayment(
				$voucherModel,
				$this->gatewayType,
				$this->callbackUrl,
				null, //$this->walletID
			);

			if ($onpResult instanceof \Throwable)
				throw $onpResult;

			list ($onpUUID, $paymentUrl) = $onpResult;
			return [
				'onpkey' => $onpUUID,
				'paymentUrl' => $paymentUrl,
			];
		}

	}

}
