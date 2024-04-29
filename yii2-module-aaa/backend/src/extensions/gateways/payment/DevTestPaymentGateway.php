<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\extensions\gateways\payment;

use Yii;
use yii\web\ServerErrorHttpException;
use yii\web\UnprocessableEntityHttpException;
use shopack\base\common\helpers\Url;
use shopack\aaa\backend\classes\BasePaymentGateway;
use shopack\aaa\backend\classes\IPaymentGateway;
use shopack\aaa\common\enums\enuPaymentGatewayType;

class DevTestPaymentGateway
	extends BasePaymentGateway
	implements IPaymentGateway
{
	//must be 127.0.0.1 for running
	const PARAM_KEY = 'key';

	public function getTitle()
	{
		return '*** درگاه پرداخت تست برنامه نویس ***';
	}

	public function getPaymentGatewayType()
	{
		return enuPaymentGatewayType::DevTest;
	}

	public function getParametersSchema()
	{
		return array_merge(parent::getParametersSchema(), [
			[
				'id' => self::PARAM_KEY,
				'type' => 'string',
				'mandatory' => 1,
				'label' => 'Key',
			],
		]);
	}

	//list ($response, $paymentToken, $paymentUrl)
	public function prepare(&$gatewayModel, $onlinePaymentModel, $callbackUrl)
	{
		return [
			/* $response     */ 'ok',
			/* $paymentToken */ 'token-' . $onlinePaymentModel->onpUUID,
			/* $paymentUrl   */ //[
				'aaa',
				// Url::to([
				// 	'/aaa/online-payment/devtestpaymentpage',
				// 	'paymentkey' => $onlinePaymentModel->onpUUID, //-> in url
				// 	'callback' => $callbackUrl, //-> in url
				// ], true),
			// ],
		];
	}

	public function pay(&$gatewayModel, $onlinePaymentModel)
	{
    $backendCallback = Url::to([
      '/aaa/online-payment/callback',
      'paymentkey' => $onlinePaymentModel->onpUUID,
    ], true);

    if (empty(Yii::$app->paymentManager->topmostPayCallback) == false) {
      // if (str_ends_with($this->topmostPayCallback, '/') == false)
      //   $this->topmostPayCallback .= '/';

      $ch = (strpos(Yii::$app->paymentManager->topmostPayCallback, '?') === false ? '?' : '&');
      $backendCallback = Yii::$app->paymentManager->topmostPayCallback
				. $ch
				. 'done='
				. urlencode($backendCallback);
    }

		$html =<<<HTML
<p>this is test payment page</p>
<p>paymentkey: {$onlinePaymentModel->onpUUID}</p>
<p>amount: {$onlinePaymentModel->onpAmount}</p>
<p>callback: {$backendCallback}</p>
<p>frontend callback: {$onlinePaymentModel->onpCallbackUrl}</p>
<p><a href='{$backendCallback}?result=ok'>[OK]</a></p>
<p><a href='{$backendCallback}?result=error'>[ERROR]</a></p>
<p><a href='{$backendCallback}?result=cancel'>[CANCEL]</a></p>
HTML;

		return [
			'type' => 'html',
			'html' => $html,
		];
	}

	public function verify(&$gatewayModel, $onlinePaymentModel, $pgwResponse)
	{
		$result = $pgwResponse['result'] ?? null;

		if ($result == 'ok') {
			return [
				$pgwResponse, //'ok',
				'trace-' . $onlinePaymentModel->onpUUID,
				'rrn-' . $onlinePaymentModel->onpUUID,
			];
		}

		if ($result == 'error')
			throw new UnprocessableEntityHttpException('Payment Failed');

		if ($result == 'cancel')
			throw new UnprocessableEntityHttpException('Payment Cancelled');

		throw new ServerErrorHttpException('unknown payment result');
	}

}
