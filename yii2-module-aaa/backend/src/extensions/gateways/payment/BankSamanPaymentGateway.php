<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\extensions\gateways\payment;

use Yii;
use yii\web\UnprocessableEntityHttpException;
use GuzzleHttp\Client;
use shopack\base\common\helpers\Json;
use shopack\aaa\common\enums\enuPaymentGatewayType;
use shopack\aaa\backend\classes\BasePaymentGateway;
use shopack\aaa\backend\classes\IPaymentGateway;

class BankSamanPaymentGateway
	extends BasePaymentGateway
	implements IPaymentGateway
{
	const URL_Request			= "https://sep.shaparak.ir/onlinepg/onlinepg";
	const URL_PayStart		= "https://sep.shaparak.ir/OnlinePG/SendToken";
	const URL_Verify			= "https://sep.shaparak.ir/verifyTxnRandomSessionkey/ipg/VerifyTransaction";
	const URL_PayReverse	= "https://sep.shaparak.ir/verifyTxnRandomSessionkey/ipg/ReverseTransaction";

	const ActionToken			= 'Token';

	const PARAM_TERMINAL_ID = 'terminalID';

	public function getTitle()
	{
		return 'بانک سامان';
	}

	public function getPaymentGatewayType()
	{
		return enuPaymentGatewayType::IranBank;
	}

	public function getParametersSchema()
	{
		return array_merge(parent::getParametersSchema(), [
			// [
			// 	'id' => self::PARAM_USERNAME,
			// 	'type' => 'string',
			// 	'mandatory' => 1,
			// 	'label' => 'User Name',
			// ],
			// [
			// 	'id' => self::PARAM_PASSWORD,
			// 	'type' => 'password',
			// 	'mandatory' => 1,
			// 	'label' => 'Password',
			// ],
			[
				'id' => self::PARAM_TERMINAL_ID,
				'type' => 'string',
				'mandatory' => 1,
				'label' => 'Terminal ID',
			],
		]);
	}

	protected function callApi($method, $url, $urlparams = [], $data = []): array
	{
		$client = new Client([
			'curl' => [CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1'],
		]);

		if (empty($urlparams) == false) {
			$idx = 0;
			foreach ($urlparams as $k => $v) {
				$url .= ($idx == 0 ? '?' : '&');
				++$idx;

				$url .= $k;
				$url .= '=';
				$url .= $v;
			}
		}

		$response = $client->request($method, $url, [
			'json' => $data,
			'headers' => [
				'Content-Type' => 'application/json',
			],
			'http_errors' => false,
		]);

		$responseStatus = $response->getStatusCode();
		if ($responseStatus != 200) {
			throw new UnprocessableEntityHttpException("Error ($responseStatus) in call api");
		}

		return Json::decode($response->getBody()->getContents());
	}

	public function prepare(&$gatewayModel, $onlinePaymentModel, $callbackUrl)
	{
		$terminal_id = $this->extensionModel->gtwPluginParameters[self::PARAM_TERMINAL_ID];

		$price = $onlinePaymentModel->onpAmount * 10; //toman -> rial
		// $callbackUrl = urlencode($callbackUrl);

		// $serverTime = $this->callApi('GET', self::URLTime);

		try {
			//--token
			$result = $this->callApi('POST', self::URL_Request, [], [
				'Action'						=> self::ActionToken,
				'Amount'						=> $price,
				// 'Wage'							=> ,
				'TerminalId'				=> $terminal_id,
				'ResNum'						=> $onlinePaymentModel->onpID,
				'RedirectURL'				=> $callbackUrl,
				// 'CellNumber'				=> ,
				// 'TokenExpiryInMin'	=> ,
				// 'HashedCardNumber'	=> ,
			]);
		} catch (\Exception $exp) {
			// echo "<div class=\"error\">{$E}</div>";
			throw new UnprocessableEntityHttpException('Error in prepare payment (' . $exp->getMessage() . ')');
		}

		// $this->throwIfFailed($result);
		if (!isset($result['status']) || $result['status'] != 1) {
			$errorCode = $result['errorCode'];
			$errorDesc = $result['errorDesc'] ?? 'Unknown Error';

			throw new UnprocessableEntityHttpException("Error ($errorCode:$errorDesc) in payment transaction");
		}

		$token = $result['token'];

		return [
			/* $response     */ 'ok',
			/* $paymentToken */ $token,
			/* $paymentUrl   */ [
				'post',
				self::URL_PayStart,
				'Token' => $token,
				'GetMethod' => false,
			],
		];
	}

	public function pay(&$gatewayModel, $onlinePaymentModel)
	{
		// return [
		// 	'type' => 'form',
		// 	'method' => 'post',
		// 	'url' => self::URL_PayStart,
		// 	'params' => [
		// 		'Token' => $onlinePaymentModel->onpPaymentToken,
		// 		'GetMethod' => 0,
		// 	],
		// ];
		return [
			'type' => 'link',
			'url' => self::URL_PayStart
				. '?' . 'Token=' . $onlinePaymentModel->onpPaymentToken,
		];
	}

	public function verify(&$gatewayModel, $onlinePaymentModel, $pgwResponse)
	{
		$terminal_id = $this->extensionModel->gtwPluginParameters[self::PARAM_TERMINAL_ID];

		$MID								= $pgwResponse['MID'];							//شماره ترمینال
		$State							= $pgwResponse['State'];						//وضعیت تراکنش: حروف انگلیسی
		$Status							= $pgwResponse['Status'];						//وضعیت تراکنش: مقدار عددی
		$RRN								= $pgwResponse['Rrn'] ?? $pgwResponse['RRN']; //شماره مرجع
		$RefNum							= $pgwResponse['RefNum'];						//رسید دیجیتالی خرید
		$ResNum							= $pgwResponse['ResNum'];						//شماره خرید
		$TerminalId					= $pgwResponse['TerminalId'];				//شماره ترمینال
		$TraceNo						= $pgwResponse['TraceNo'];					//شماره رهگیری
		$Amount							= $pgwResponse['Amount'];						//
		$Wage								= $pgwResponse['Wage'];							//
		$SecurePan					= $pgwResponse['SecurePan'];				//شماره کارتی که تراکنش با آن انجام شده است
		$HashedCardNumber		= $pgwResponse['HashedCardNumber'];	//شماره کارت هش شده SHA256

		// switch ($Status) {
		// 	case 1:  //CanceledByUser: کاربر انصراف داده است
		// 	case 2:  //OK: پرداخت با موفقیت انجام شد
		// 	case 3:  //Failed: پرداخت انجام نشد.
		// 	case 4:  //SessionIsNull: کاربر در بازه زمانی تعیین شده پاسخی ارسال نکرده است.
		// 	case 5:  //InvalidParameters: پارامترهای ارسالی نامعتبر است.
		// 	case 8:  //MerchantIpAddressIsInvalid: آدرس سرور پذیرنده نامعتبر است )در پرداخت های بر پایه توکن(
		// 	case 10: //TokenNotFound: توکن ارسال شده یافت نشد.
		// 	case 11: //TokenRequired: با این شماره ترمینال فقط تراکنش های توکنی قابل پرداخت هستند.
		// 	case 12: //TerminalNotFound: شماره ترمینال ارسال شده یافت نشد.
		// }

		if ($Status != 2) { //Ok
			if ($Status == 1)
				$errorDesc = 'Canceled By User';
			else if ($Status == 3)
				$errorDesc = 'Failed';
			else
				$errorDesc = 'Unknown Error';

			throw new UnprocessableEntityHttpException("Error ($Status:$errorDesc) in payment transaction");
		}

		//1: validate data
		if ($terminal_id != $TerminalId) {
			throw new UnprocessableEntityHttpException("Error: mismatched Terminal Id");
		}

		//2: check double spending
		//todo: check double spending
		//$RefNum

		//3: verify
		$verify_result = $this->callApi('POST', self::URL_Verify, [], [
			'RefNum' => $RefNum,
			'TerminalNumber' => $terminal_id, // $MID,
		]);

		// $this->throwIfFailed($verify_result);
		if (!isset($verify_result['ResultCode']) || $verify_result['ResultCode'] != 0) {
			$errorCode = $verify_result['ResultCode'];
			$errorDesc = $verify_result['errorDesc'] ?? 'Unknown Error';

			throw new UnprocessableEntityHttpException("Error ($errorCode:$errorDesc) in payment verification");
		}

		$transactionDetail = $verify_result['TransactionDetail'];

		//4: settlement

		//
		return [
			$transactionDetail, //'ok',
			$transactionDetail['StraceNo'],
			$transactionDetail['RRN'],
			// 'traceNo'				=> $payGateTransactionId,
			// 'referenceNo'		=> $result['rrn'],
			// 'transactionId'	=> $result['refID'],
			// 'cardNo'				=> $result['cardNumber'],
		];
	}

	// protected function throwIfFailed($result)
	// {
	// 	if (!isset($result['status']) || $result['status'] != 1) {
	// 		$errorCode = $result['errorCode'];
	// 		$errorDesc = $result['errorDesc'] ?? 'Unknown';

	// 		throw new UnprocessableEntityHttpException("Error ($errorCode:$errorDesc) in payment transaction");
	// 	}
	// }

}
