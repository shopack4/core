<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\components;

use Yii;
use yii\db\Expression;
use yii\base\Component;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnprocessableEntityHttpException;
use Ramsey\Uuid\Uuid;
use shopack\base\common\helpers\Url;
use shopack\aaa\backend\classes\BasePaymentGateway;
use shopack\aaa\backend\models\GatewayModel;
use shopack\aaa\common\enums\enuGatewayStatus;
use shopack\aaa\backend\models\VoucherModel;
use shopack\aaa\common\enums\enuVoucherType;
use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\aaa\backend\models\WalletModel;
use shopack\aaa\backend\models\WalletTransactionModel;
use shopack\aaa\backend\models\OnlinePaymentModel;
use shopack\aaa\common\enums\enuOnlinePaymentStatus;
use shopack\aaa\backend\models\OfflinePaymentModel;
use shopack\aaa\common\enums\enuOfflinePaymentStatus;

class PaymentManager extends Component
{
  //used for central payment callback. e.g: *www.x.com -> api.x.com -> ui.x.com
  public $topmostPayCallback = null;

  public function log($message, $type='INFO')
  {
		if (Yii::$app->isConsole == false)
			return;

    if ($message instanceof \Throwable) {
			$message = $message->getMessage();
      $type = 'ERROR';
    }

		if (empty($type))
    	echo "[" . date('Y/m/d H:i:s') . "] {$message}\n";
		else
    	echo "[" . date('Y/m/d H:i:s') . "][{$type}] {$message}\n";
  }

  /**
   * return [onpkey, paymentUrl]
   * or $exp
   */
  public function createOnlinePayment(
    $voucherModel,
    $gatewayType,
    $callbackUrl,
    $walletID = null
  ) {
    if (empty($walletID)) {
      $walletModel = WalletModel::ensureIHaveDefaultWallet();
      $walletID = $walletModel->walID;
    }

    $payAmount = $voucherModel->vchTotalAmount - ($voucherModel->vchTotalPaid ?? 0);

    //1: find gateway
    $gatewayModel = $this->findBestPaymentGateway($gatewayType, $payAmount);
    if ($gatewayModel == null)
      throw new NotFoundHttpException('Payment gateway not found');

    //2: create online payment
    $onlinePaymentModel = new OnlinePaymentModel;
    // $onlinePaymentModel->onpID
    $onlinePaymentModel->onpUUID				= Uuid::uuid4()->toString();
    $onlinePaymentModel->onpGatewayID		= $gatewayModel->gtwID;
    $onlinePaymentModel->onpVoucherID		= $voucherModel->vchID;
    $onlinePaymentModel->onpAmount			= $payAmount;
    $onlinePaymentModel->onpCallbackUrl	= $callbackUrl;
    $onlinePaymentModel->onpWalletID		= $walletID;
    if ($onlinePaymentModel->save() == false)
      throw new ServerErrorHttpException('It is not possible to create an online payment');

    //3: prepare gateway
    $backendCallback = Url::to([
      '/aaa/online-payment/callback',
      'paymentkey' => $onlinePaymentModel->onpUUID,
    ], true);

    if (empty($this->topmostPayCallback) == false) {
      // if (str_ends_with($this->topmostPayCallback, '/') == false)
      //   $this->topmostPayCallback .= '/';

      $ch = (strpos($this->topmostPayCallback, '?') === false ? '?' : '&');
      $backendCallback = $this->topmostPayCallback . $ch . 'done=' . urlencode($backendCallback);
    }

    $gatewayClass = $gatewayModel->getGatewayClass();

    try {
      list ($response, $paymentToken, $paymentUrl) = $gatewayClass->prepare(
        $gatewayModel,
        $onlinePaymentModel,
        $backendCallback
      );

      $paymentUrl = Url::to([
        '/aaa/online-payment/pay',
        'paymentkey' => $onlinePaymentModel->onpUUID,
      ], true);

    } catch (\Throwable $exp) {
      $onlinePaymentModel->onpResult = [
        'error' => $exp->getMessage(),
      ];
      $onlinePaymentModel->onpStatus = enuOnlinePaymentStatus::Error;
      if ($onlinePaymentModel->save() == false)
        throw new ServerErrorHttpException('It is not possible to update online payment');

      return $exp;

      // throw $exp;
    }

    //4: save to onp
    $onlinePaymentModel->onpPaymentToken	= $paymentToken;
    $onlinePaymentModel->onpResult		  	= (array)$response;
    $onlinePaymentModel->onpStatus			  = enuOnlinePaymentStatus::Pending;
    if ($onlinePaymentModel->save() == false)
      throw new ServerErrorHttpException('It is not possible to update online payment');

    //5: update gateway usage
    $fnGetConst = function($value) { return $value; };
    $gatewayTableName = GatewayModel::tableName();

    $qry =<<<SQL
  UPDATE {$gatewayTableName}
     SET gtwUsages = JSON_MERGE_PATCH(
           COALESCE(JSON_REMOVE(gtwUsages, '$.{$fnGetConst(BasePaymentGateway::USAGE_LAST_TRANSACTION_DATE)}', '$.{$fnGetConst(BasePaymentGateway::USAGE_TODAY_USED_AMOUNT)}'), '{}'),
           JSON_OBJECT(
             '{$fnGetConst(BasePaymentGateway::USAGE_LAST_TRANSACTION_DATE)}', CURDATE(),
             '{$fnGetConst(BasePaymentGateway::USAGE_TODAY_USED_AMOUNT)}', IF(
               JSON_EXTRACT(gtwUsages, '$.{$fnGetConst(BasePaymentGateway::USAGE_LAST_TRANSACTION_DATE)}') IS NOT NULL
                 AND JSON_UNQUOTE(JSON_EXTRACT(gtwUsages, '$.{$fnGetConst(BasePaymentGateway::USAGE_LAST_TRANSACTION_DATE)}')) = CURDATE()
                 AND JSON_CONTAINS_PATH(gtwUsages, 'one', '$.{$fnGetConst(BasePaymentGateway::USAGE_TODAY_USED_AMOUNT)}')
               , CAST(JSON_EXTRACT(gtwUsages, '$.{$fnGetConst(BasePaymentGateway::USAGE_TODAY_USED_AMOUNT)}') AS UNSIGNED) + {$payAmount}
               ,{$payAmount}
             )
           )
         )
   WHERE gtwID = {$gatewayModel->gtwID}
SQL;
    Yii::$app->db->createCommand($qry)->execute();

    //
    return [$onlinePaymentModel->onpUUID, $paymentUrl];
  }

  public function findBestPaymentGateway(
    $gatewayType,
    $amount
  ) {
    $gatewayNames = [];
    $extensions = Yii::$app->controller->module->GatewayList('payment');
    foreach ($extensions as $pluginName => $extension) {
      $gtwclass = Yii::$app->controller->module->GatewayClass($pluginName);
      if ($gtwclass->getPaymentGatewayType() == $gatewayType) {
        $gatewayNames[] = $pluginName;
      }
    }

    if (empty($gatewayNames))
      return null;

    $fnGetConst = function($value) { return $value; };
    $gatewayTableName = GatewayModel::tableName();

    $gatewayModel = GatewayModel::find()
      ->select([
        "{$gatewayTableName}.*",
        'tmptbl_inner.inner_pgwSumTodayPaidAmount',
        'tmptbl_inner.inner_pgwTransactionFeeAmount',
      ])
      ->innerJoin([
        'tmptbl_inner' => GatewayModel::find()
          ->select([
            'gtwID',

            "IF(JSON_EXTRACT(gtwPluginParameters, '$.{$fnGetConst(BasePaymentGateway::PARAM_GATEWAY_COMMISSION_TYPE)}') = '%'

              , JSON_UNQUOTE(JSON_EXTRACT(gtwPluginParameters, '$.{$fnGetConst(BasePaymentGateway::PARAM_GATEWAY_COMMISSION)}')) * {$amount} / 100

              , JSON_UNQUOTE(JSON_EXTRACT(gtwPluginParameters, '$.{$fnGetConst(BasePaymentGateway::PARAM_GATEWAY_COMMISSION)}'))
            ) AS `inner_pgwTransactionFeeAmount`",

            "IF(JSON_EXTRACT(gtwUsages, '$.{$fnGetConst(BasePaymentGateway::USAGE_LAST_TRANSACTION_DATE)}') IS NULL
              OR JSON_UNQUOTE(JSON_EXTRACT(gtwUsages, '$.{$fnGetConst(BasePaymentGateway::USAGE_LAST_TRANSACTION_DATE)}')) < CURDATE()

              , 0

              , JSON_UNQUOTE(JSON_EXTRACT(gtwUsages, '$.{$fnGetConst(BasePaymentGateway::USAGE_TODAY_USED_AMOUNT)}'))
            ) AS `inner_pgwSumTodayPaidAmount`",

            // "COALESCE(JSON_UNQUOTE(JSON_EXTRACT(tblPaymentGatewayTypesI18N.i18nData, '$.pgtName.'fa')), tblPaymentGatewayTypes.pgtName) AS `pgtName`",
          ])
          // LEFT JOIN tblPaymentGatewayTypes
          // 		 ON tblPaymentGatewayTypes.pgtType = {$gatewayTableName}.pgwType
          // LEFT JOIN tblPaymentGatewayTypesI18N
          // 		 ON tblPaymentGatewayTypesI18N.i18nPID = tblPaymentGatewayTypes.pgtID
          ->andWhere("gtwStatus != '{$fnGetConst(enuGatewayStatus::Removed)}'")
          ->andWhere(['IN', 'gtwPluginName', $gatewayNames])
          ->andWhere(['OR',
            "JSON_EXTRACT(gtwRestrictions, '$.{$fnGetConst(BasePaymentGateway::RESTRICTION_MIN_TRANSACTION_AMOUNT)}') IS NULL",

            "JSON_UNQUOTE(JSON_EXTRACT(gtwRestrictions, '$.{$fnGetConst(BasePaymentGateway::RESTRICTION_MIN_TRANSACTION_AMOUNT)}')) <= {$amount}"
          ])
          ->andWhere(['OR',
            "JSON_EXTRACT(gtwRestrictions, '$.{$fnGetConst(BasePaymentGateway::RESTRICTION_MAX_TRANSACTION_AMOUNT)}') IS NULL",

            "JSON_UNQUOTE(JSON_EXTRACT(gtwRestrictions, '$.{$fnGetConst(BasePaymentGateway::RESTRICTION_MAX_TRANSACTION_AMOUNT)}')) >= {$amount}"
          ])
          ->andWhere(['OR',
            "JSON_EXTRACT(gtwRestrictions, '$.{$fnGetConst(BasePaymentGateway::RESTRICTION_MAX_DAILY_TOTAL_AMOUNT)}') IS NULL",

            "JSON_EXTRACT(gtwUsages, '$.{$fnGetConst(BasePaymentGateway::USAGE_LAST_TRANSACTION_DATE)}') IS NULL",

            "JSON_UNQUOTE(JSON_EXTRACT(gtwUsages, '$.{$fnGetConst(BasePaymentGateway::USAGE_LAST_TRANSACTION_DATE)}')) < CURDATE()",

            "JSON_UNQUOTE(JSON_EXTRACT(gtwUsages, '$.{$fnGetConst(BasePaymentGateway::USAGE_TODAY_USED_AMOUNT)}')) <= JSON_UNQUOTE(JSON_EXTRACT(gtwRestrictions, '$.{$fnGetConst(BasePaymentGateway::RESTRICTION_MAX_DAILY_TOTAL_AMOUNT)}')) - {$amount}",
          ])
        ],
        "tmptbl_inner.gtwID = {$gatewayTableName}.gtwID"
      )
      ->andWhere(['gtwStatus' => enuGatewayStatus::Active])
      // ->andWhere(['IN', "{$gatewayTableName}gtwPluginName", $gatewayNames])
      // ->andWhere("LOWER({$gatewayTableName}.pgwAllowedDomainName) = 'dev.test'")
      ->orderBy([
        'tmptbl_inner.inner_pgwTransactionFeeAmount' => SORT_ASC,
        'tmptbl_inner.inner_pgwSumTodayPaidAmount' => SORT_ASC,
        'RAND()' => SORT_ASC,
      ])
      ->one();

    return $gatewayModel;
  }

  //redirect to payment page
  public function pay($paymentkey)
  {
    $onlinePaymentModel = OnlinePaymentModel::find()
      ->with('gateway')
      ->with('voucher')
      ->andWhere(['onpUUID' => $paymentkey])
      ->one();

    if ($onlinePaymentModel == null) {
      Yii::error('The requested online payment does not exist.', __METHOD__);
      throw new NotFoundHttpException('The requested online payment does not exist.');
    }

    if ($onlinePaymentModel->onpStatus != enuOnlinePaymentStatus::Pending)
      throw new UnprocessableEntityHttpException('This payment is not in pending state.');

    $gatewayClass = $onlinePaymentModel->gateway->getGatewayClass();

    $result = $gatewayClass->pay($onlinePaymentModel->gateway, $onlinePaymentModel);

    if ($result['type'] == 'form') {
      Yii::$app->controller->response->format = \yii\web\Response::FORMAT_HTML;
      Yii::$app->controller->layout = false;

      $params = [];
      foreach ($result['params'] as $k => $v) {
        $params[] = "<input type='hidden' name='{$k}' value='{$v}'>";
      }
      $params = implode("\n", $params);

      $html = <<<HTML
<html>
  <head>
  </head>
  <body onload="document.redirectform.submit()">
    <form method="{$result['method']}" action="{$result['url']}" name="redirectform">
      {$params}
    </form>
  </body>
</html>
HTML;

      return Yii::$app->controller->renderContent($html);

    } else if ($result['type'] == 'html') {
      Yii::$app->controller->response->format = \yii\web\Response::FORMAT_HTML;
      Yii::$app->controller->layout = false;

      $html = <<<HTML
<html>
  <head>
  </head>
  <body>
    {$result['html']}
  </body>
</html>
HTML;

      return Yii::$app->controller->renderContent($html);

    } else if ($result['type'] == 'link') {

      return Yii::$app->controller->redirect($result['url']);

      // //redirect in frontend
      // return [
      //   'url' => $result['url'],
      // ];
    }

    throw new UnprocessableEntityHttpException("Unknown payment page type ({$result['type']})");
  }

  /**
   * $pgwResponse: array|null response data back from payment gateway
   */
  public function approveOnlinePayment($paymentkey, $pgwResponse) : OnlinePaymentModel
  {
    $onlinePaymentModel = OnlinePaymentModel::find()
      ->with('gateway')
      ->with('voucher')
      ->andWhere(['onpUUID' => $paymentkey])
      ->one();

    if ($onlinePaymentModel == null) {
      Yii::error('The requested online payment does not exist.', __METHOD__);
      throw new NotFoundHttpException('The requested online payment does not exist.');
    }

    if ($onlinePaymentModel->onpStatus != enuOnlinePaymentStatus::Pending)
      throw new UnprocessableEntityHttpException('This payment is not in pending state.');

    //1: verify and settle via gateway
    try {
      $this->verifyOnlinePayment($onlinePaymentModel, $pgwResponse);

    } catch (\Throwable $th) {
      // if ($onlinePaymentModel->voucher->vchType != enuVoucherType::Basket) {
      if ($onlinePaymentModel->voucher->vchType == enuVoucherType::Credit) {
        $fnGetConstQouted = function($value) { return "'{$value}'"; };
        $voucherTableName = VoucherModel::tableName();

        $qry =<<<SQL
  UPDATE {$voucherTableName}
     SET vchStatus = {$fnGetConstQouted(enuVoucherStatus::Error)}
   WHERE vchID = {$onlinePaymentModel->onpVoucherID}
SQL;
        $rowsCount = Yii::$app->db->createCommand($qry)->execute();
      }

      return $onlinePaymentModel;
    }

    //start transaction
    $transaction = Yii::$app->db->beginTransaction();

    $walletTableName = WalletModel::tableName();
    $voucherTableName = VoucherModel::tableName();

    try {
      //2.1: create wallet transaction
      $walletTransactionModel = new WalletTransactionModel();
      $walletTransactionModel->wtrWalletID				= $onlinePaymentModel->onpWalletID;
      $walletTransactionModel->wtrVoucherID				= $onlinePaymentModel->onpVoucherID;
      $walletTransactionModel->wtrOnlinePaymentID	= $onlinePaymentModel->onpID;
      $walletTransactionModel->wtrAmount					= $onlinePaymentModel->onpAmount;
      $walletTransactionModel->save();

      //2.2: add to the wallet amount
      $qry =<<<SQL
  UPDATE {$walletTableName}
     SET walRemainedAmount = walRemainedAmount + {$onlinePaymentModel->onpAmount}
   WHERE walID = {$walletTransactionModel->wtrWalletID}
SQL;
      $rowsCount = Yii::$app->db->createCommand($qry)->execute();

      //save to the voucher
      if ($onlinePaymentModel->voucher->vchType == enuVoucherType::Basket) {
        //2.1: create decrease wallet transaction
        $walletTransactionModel = new WalletTransactionModel();
        $walletTransactionModel->wtrWalletID	= $onlinePaymentModel->onpWalletID;
        $walletTransactionModel->wtrVoucherID	= $onlinePaymentModel->onpVoucherID;
        $walletTransactionModel->wtrAmount		= (-1) * $onlinePaymentModel->onpAmount;
        $walletTransactionModel->save();

        //2.2: decrease wallet amount
        $qry =<<<SQL
  UPDATE {$walletTableName}
     SET walRemainedAmount = walRemainedAmount - {$onlinePaymentModel->onpAmount}
   WHERE walID = {$walletTransactionModel->wtrWalletID}
SQL;
        $rowsCount = Yii::$app->db->createCommand($qry)->execute();

        $field = 'vchPaidByWallet';
      } else {
        $field = 'vchOnlinePaid';
      }

      $qry =<<<SQL
  UPDATE {$voucherTableName}
     SET {$field} = IFNULL({$field}, 0) + {$onlinePaymentModel->onpAmount}
       , vchTotalPaid = IFNULL(vchTotalPaid, 0) + {$onlinePaymentModel->onpAmount}
   WHERE vchID = {$onlinePaymentModel->onpVoucherID}
SQL;
      $rowsCount = Yii::$app->db->createCommand($qry)->execute();
      $onlinePaymentModel->voucher->refresh();

      if (in_array($onlinePaymentModel->voucher->vchType, [
          enuVoucherType::Basket, enuVoucherType::Invoice,
        ])
      && ($onlinePaymentModel->voucher->vchTotalAmount == $onlinePaymentModel->voucher->vchTotalPaid ?? 0)
      ) {
        $onlinePaymentModel->voucher->vchType = enuVoucherType::Invoice;
        $onlinePaymentModel->voucher->vchStatus = enuVoucherStatus::Settled;
        $onlinePaymentModel->voucher->save();
      }

      //commit
      $transaction->commit();

      return $onlinePaymentModel;

    } catch (\Exception | \Throwable $e) {
      $transaction->rollBack();
      throw $e;
    }
  }

  //verify and settle online payment
  private function verifyOnlinePayment($onlinePaymentModel, $pgwResponse)
  {
    $gatewayClass = $onlinePaymentModel->gateway->getGatewayClass();

    try {
      list ($result, $trackNumber, $rrn) = $gatewayClass->verify($onlinePaymentModel->gateway, $onlinePaymentModel, $pgwResponse);

      $onlinePaymentModel->onpTrackNumber = $trackNumber;
      $onlinePaymentModel->onpRRN         = $rrn;
      $onlinePaymentModel->onpResult      = (array)$result;
      $onlinePaymentModel->onpStatus      = enuOnlinePaymentStatus::Paid;
      if ($onlinePaymentModel->save() == false) {
        //todo: ???
      }

    } catch (\Throwable $exp) {
      $onlinePaymentModel->onpResult = [
        'error' => $exp->getMessage(),
      ];
      $onlinePaymentModel->onpStatus = enuOnlinePaymentStatus::Error;
      if ($onlinePaymentModel->save() == false) {
        //todo: ???
      }

      //---------------
      //decrease USAGE_TODAY_USED_AMOUNT ($onlinePaymentModel->onpAmount)
      // if USAGE_LAST_TRANSACTION_DATE = CURDATE()

      $fnGetConst = function($value) { return $value; };

      $gatewayTableName = GatewayModel::tableName();

      $qry =<<<SQL
  UPDATE {$gatewayTableName}
     SET gtwUsages = JSON_MERGE_PATCH(
           COALESCE(JSON_REMOVE(gtwUsages, '$.{$fnGetConst(BasePaymentGateway::USAGE_TODAY_USED_AMOUNT)}'), '{}'),
           JSON_OBJECT(
             '{$fnGetConst(BasePaymentGateway::USAGE_TODAY_USED_AMOUNT)}', CAST(JSON_EXTRACT(gtwUsages, '$.{$fnGetConst(BasePaymentGateway::USAGE_TODAY_USED_AMOUNT)}') AS UNSIGNED) - {$onlinePaymentModel->onpAmount}
           )
         )
   WHERE gtwID = {$onlinePaymentModel->onpGatewayID}
     AND JSON_UNQUOTE(JSON_EXTRACT(gtwUsages, '$.{$fnGetConst(BasePaymentGateway::USAGE_LAST_TRANSACTION_DATE)}')) = CURDATE()
SQL;
      Yii::$app->db->createCommand($qry)->execute();

      //---------------
      throw $exp;
    }
  }

	public function approveOfflinePayment($offlinePaymentModel)
  {
    if ($offlinePaymentModel->ofpStatus != enuOfflinePaymentStatus::WaitForApprove)
      throw new UnprocessableEntityHttpException('This payment is not in pending state.');

    //start transaction
    $transaction = Yii::$app->db->beginTransaction();

    try {
      //1- create voucher
      $voucherModel = new VoucherModel;
      $voucherModel->vchOwnerUserID = $offlinePaymentModel->ofpOwnerUserID;
      $voucherModel->vchType        = enuVoucherType::Credit;
      $voucherModel->vchAmount      =
        $voucherModel->vchTotalAmount = $offlinePaymentModel->ofpAmount;
      $voucherModel->vchOfflinePaid = $offlinePaymentModel->ofpAmount;
      $voucherModel->vchItems       = [
        'inc-wallet-id' => $offlinePaymentModel->ofpWalletID,
      ];
      $voucherModel->vchStatus      = enuVoucherStatus::Finished;
      if ($voucherModel->save() == false)
        throw new ServerErrorHttpException('It is not possible to create a voucher');

      //2- create wallet transaction
      $walletTransactionModel = new WalletTransactionModel();
      $walletTransactionModel->wtrWalletID		= $offlinePaymentModel->ofpWalletID;
      $walletTransactionModel->wtrVoucherID		= $voucherModel->vchID;
      $walletTransactionModel->wtrOfflinePaymentID = $offlinePaymentModel->ofpID;
      $walletTransactionModel->wtrAmount			= $offlinePaymentModel->ofpAmount;
      if ($walletTransactionModel->save() == false)
        throw new ServerErrorHttpException('It is not possible to create wallet transaction');

      //3- update wallet
      $walletTableName = WalletModel::tableName();
      $qry =<<<SQL
  UPDATE {$walletTableName}
     SET walRemainedAmount = walRemainedAmount + {$offlinePaymentModel->ofpAmount}
   WHERE walID = {$offlinePaymentModel->ofpWalletID}
SQL;
			$rowsCount = Yii::$app->db->createCommand($qry)->execute();

      //4- update offline payment
      $offlinePaymentModel->ofpVoucherID = $voucherModel->vchID;
      $offlinePaymentModel->ofpStatus    = enuOfflinePaymentStatus::Approved;
      if ($offlinePaymentModel->save() == false)
        throw new ServerErrorHttpException('It is not possible to create an offline payment');

      //commit
      $transaction->commit();

      return $offlinePaymentModel;

    } catch (\Throwable $exp) {
      $transaction->rollBack();
      throw $exp;
    }
  }

  public function rejectOfflinePayment($offlinePaymentModel)
  {
    if ($offlinePaymentModel->ofpStatus != enuOfflinePaymentStatus::WaitForApprove)
      throw new UnprocessableEntityHttpException('This payment is not in pending state.');

    $offlinePaymentModel->ofpStatus = enuOfflinePaymentStatus::Rejected;
    if ($offlinePaymentModel->save() == false)
      throw new ServerErrorHttpException('It is not possible to create an offline payment');

    return $offlinePaymentModel;
  }

}
