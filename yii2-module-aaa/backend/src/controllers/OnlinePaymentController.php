<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use Yii;
use yii\web\UnprocessableEntityHttpException;
use shopack\base\backend\controller\BaseCrudController;
use shopack\aaa\common\enums\enuPaymentGatewayType;
use shopack\aaa\common\enums\enuGatewayStatus;
use shopack\aaa\common\enums\enuOnlinePaymentStatus;
use shopack\aaa\backend\models\OnlinePaymentModel;
use shopack\aaa\backend\models\GatewayModel;

class OnlinePaymentController extends BaseCrudController
{
	public function behaviors()
	{
		$behaviors = parent::behaviors();

		$behaviors[static::BEHAVIOR_AUTHENTICATOR]['except'] = [
			'callback',
			'pay',
			// 'devtestpaymentpage',
		];

		return $behaviors;
	}

	public $modelClass = OnlinePaymentModel::class;

	public function permissions()
	{
		$checkOwner = function($model) : bool {
			return (($model != null) && ($model['voucher']['vchOwnerUserID'] == Yii::$app->user->id));
		};

		return [
			'index'  => [
										'aaa/online-payment/crud' => '0100',
										'filter' => function($query) {
											Yii::$app->user->assertIsNotGuest();
											$query->andWhere(['vchOwnerUserID' => Yii::$app->user->id]);
										},
									],
			'view'   => ['aaa/online-payment/crud' => '0100', 'checker' => $checkOwner],
			// 'create' => ['aaa/online-payment/crud' => '1000', 'checker' => $checkOwner],
			// 'update' => ['aaa/online-payment/crud' => '0010', 'checker' => $checkOwner],
			// 'delete' => ['aaa/online-payment/crud' => '0001', 'checker' => $checkOwner],
			// 'undelete' => ['aaa/online-payment/undelete'],
		];
	}

	public function queryAugmentaters()
	{
		return [
			'index' => function($query) {
				$query
					->joinWith('gateway')
					->joinWith('voucher')
					->joinWith('voucher.owner')
					->joinWith('wallet')
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
			'view' => function($query) {
				$query
					->joinWith('gateway')
					->joinWith('voucher')
					->joinWith('voucher.owner')
					->joinWith('wallet')
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
		];
	}

	public function actionGetAllowedTypes()
	{
		$types = [];

		$models = GatewayModel::find()
			->select('gtwPluginName')
			->andWhere(['gtwPluginType' => 'payment'])
			->andWhere(['gtwStatus' => enuGatewayStatus::Active])
			->groupBy('gtwPluginName')
			->asArray()
			->all();
		;

		if (empty($models) == false) {
			foreach ($models as $model) {
				$gtwclass = Yii::$app->controller->module->GatewayClass($model['gtwPluginName']);

				$type = $gtwclass->getPaymentGatewayType();

				if (YII_ENV_PROD && ($type == enuPaymentGatewayType::DevTest))
					continue;

				$types[] = $type;
			}
		}

		return $types;
	}
/*
	public function actionDevtestpaymentpage($paymentkey, $callback)
	{
		if (!YII_ENV_DEV)
			throw new ServerErrorHttpException('only dev mode allowed');

		$onlinePaymentModel = OnlinePaymentModel::find()
      ->andWhere(['onpUUID' => $paymentkey])
      ->one();

		$this->response->format = \yii\web\Response::FORMAT_HTML;

		return <<<HTML
<p>this is test payment page</p>
<p>paymentkey: {$paymentkey}</p>
<p>amount: {$onlinePaymentModel->onpAmount}</p>
<p>callback: {$callback}</p>
<p>frontend callback: {$onlinePaymentModel->onpCallbackUrl}</p>
<p><a href='{$callback}?result=ok'>[OK]</a></p>
<p><a href='{$callback}?result=error'>[ERROR]</a></p>
<p><a href='{$callback}?result=cancel'>[CANCEL]</a></p>
HTML;
	}
*/
	//middleware for redirect to pay page
	public function actionPay($paymentkey)
  {
		return Yii::$app->paymentManager->pay($paymentkey);
	}

	//accepts all http methods
	public function actionCallback(
		$paymentkey,
		$action = 'verify'
	) {
		if ($action != 'verify')
			throw new UnprocessableEntityHttpException("invalid action ({$action})");

		$pgwResponse = array_merge(
			Yii::$app->request->getQueryParams(),
			Yii::$app->request->getBodyParams(),
		);

		$onlinePaymentModel = null;

		try {
			$onlinePaymentModel = Yii::$app->paymentManager->approveOnlinePayment($paymentkey, $pgwResponse);

      if ($onlinePaymentModel->onpStatus == enuOnlinePaymentStatus::Error) {
				if (empty($onlinePaymentModel->onpResult['error']) == false) {
					// if (YII_DEBUG) {
						$onlinePaymentModel->addError('', $onlinePaymentModel->onpResult['error']);
						// $errors = $onlinePaymentModel->onpResult['error'];
					// } else {
					// 	$errors = 'Payment Failed';
					// }
				}
			} else {
				$done = $onlinePaymentModel->voucher->processVoucher();
			}

		} catch (\Throwable $th) {
			if ($onlinePaymentModel == null)
				throw $th;

			$onlinePaymentModel->addError('', $th->getMessage());
		}

		// throw new UnprocessableEntityHttpException('aaaaaaaaa (3)');

		//---
		$url = $onlinePaymentModel->onpCallbackUrl;
		if (strpos($url, '?') === false)
			$url .= '?';
		else
			$url .= '&';
		$url .= 'paymentkey=' . $paymentkey;

		// if (YII_DEBUG)
			$errors = $onlinePaymentModel->getErrorSummary(true);

		if (empty($errors) == false)
			$url .= '&errors=' . urlencode(implode('\n', $errors));

		$this->redirect($url);

		// $onlinePaymentClass = $onlinePaymentModel->getOnlinePaymentClass();

		// if (!($onlinePaymentClass instanceof \shopack\base\common\classes\IWebhook)) {
		// 	Yii::error('Webhook not supported by this online-payment.', __METHOD__);
		// 	throw new UnprocessableEntityHttpException('Webhook not supported by this online-payment.');
		// }

		// //check caller
		// if (!YII_ENV_DEV) {
		// 	if (method_exists($onlinePaymentClass, 'validateCaller')) {
		// 		$ret = $onlinePaymentClass->validateCaller();
		// 		if ($ret !== true) {
		// 			Yii::error($ret[1], __METHOD__);
		// 			throw new UnprocessableEntityHttpException($ret[1]);
		// 		}
		// 	}
		// }

		// $ret = $onlinePaymentClass->callWebhook($command);

		// $params = [
		// 	'get' => $_GET,
		// 	'post' => Yii::$app->request->getBodyParams(),
		// ];

		// $onlinePaymentClass->log(
		// 	/* onplogMethodName */ 'webhook',
		// 	/* onplogRequest    */ $params,
		// 	/* onplogResponse   */ $ret
		// );

		// return $ret;
  }

}
