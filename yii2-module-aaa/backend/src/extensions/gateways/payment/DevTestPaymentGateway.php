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

	//list ($response, $trackID, $paymentUrl)
	public function prepare(&$gatewayModel, $onlinePaymentModel, $callbackUrl)
	{
		return [
			/* $response   */ 'ok',
			/* $trackID    */ 'track-' . $onlinePaymentModel->onpUUID,
			/* $paymentUrl */ //[
				// 'post',
				Url::to([
					'/aaa/online-payment/devtestpaymentpage',
					'paymentkey' => $onlinePaymentModel->onpUUID, //-> in url
					'callback' => $callbackUrl, //-> in url
				], true),
				// 'aaa' => 'bbb', //-> in hidden field
			// ],
		];
	}

	public function verify(&$gatewayModel, $onlinePaymentModel, $pgwResponse)
	{
		$result = $pgwResponse['result'] ?? null;

		if ($result == 'ok') {
			return [
				$pgwResponse, //'ok',
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
