<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\extensions\gateways\sms;

use Yii;
use Melipayamak\MelipayamakApi;
use shopack\aaa\backend\classes\BaseSmsGateway;
use shopack\aaa\backend\classes\SmsSendResult;
use shopack\aaa\backend\classes\ISmsGateway;

//https://www.melipayamak.com/api/
//https://github.com/Melipayamak/melipayamak-php
//https://github.com/Melipayamak/melipayamak-yii2

class MeliPayamakSmsGateway
	extends BaseSmsGateway
	implements ISmsGateway
{
	const PARAM_USERNAME		= 'username';
	const PARAM_PASSWORD		= 'password';
	const PARAM_LINENUMBER	= 'number';
	const PARAM_BODY_ID			= 'bodyid';

	public function getTitle()
	{
		return 'ملی پیامک';
	}

	public function getParametersSchema()
	{
		return array_merge([
			[
				'id' => self::PARAM_USERNAME,
				'type' => 'string',
				'mandatory' => 1,
				'label' => 'User Name',
				'style' => 'direction:ltr',
			],
			[
				'id' => self::PARAM_PASSWORD,
				'type' => 'password',
				'mandatory' => 1,
				'label' => 'Password',
				'style' => 'direction:ltr',
			],
			[
				'id' => self::PARAM_BODY_ID,
				'type' => 'string',
				'mandatory' => 1,
				'label' => 'Body ID',
				'style' => 'direction:ltr',
			],
			[
				'id' => self::PARAM_LINENUMBER,
				'type' => 'string',
				'mandatory' => 1,
				'label' => 'Line Number',
				'style' => 'direction:ltr',
			],
		], parent::getParametersSchema());
	}

	public function send(
		$message,
		$to,
		$from = null //null => use default in gtwPluginParameters
	) : SmsSendResult {

		$this->prepareMessageForSend($message);

		$username		= $this->extensionModel->gtwPluginParameters[self::PARAM_USERNAME];
		$password		= $this->extensionModel->gtwPluginParameters[self::PARAM_PASSWORD];
		$lineNumber	= $this->extensionModel->gtwPluginParameters[self::PARAM_LINENUMBER] ?? null;
		$bodyid			= $this->extensionModel->gtwPluginParameters[self::PARAM_BODY_ID] ?? null;

		try {
			$api = new MelipayamakApi($username, $password);
			$sms = $api->sms();

			if (empty($bodyid)) {
				if (empty($from) && (empty($lineNumber) == false))
					$from = $lineNumber;

				$response = $sms->send($to, $from, $message);

				//"{"Value":"0","RetStatus":35,"StrRetStatus":"InvalidData"}"
			} else {
				$response = $sms->sendByBaseNumber($message, $to, $bodyid);

				//"{"Message":"An unexpected error occured"}"
			}

			$response = json_decode($response, true);
			// echo $response->Value; //RecId or Error Number

			$RetStatus	= $response["RetStatus"] ?? 0;
			$RetMessage	= $response["StrRetStatus"] ?? $response["Message"] ?? '';
			$RetValue		= $response["Value"] ?? null;

			if ($RetStatus != 1)
				return new SmsSendResult(false, $RetMessage, $RetStatus);

			return new SmsSendResult(true, null, $RetValue);

		} catch(\Exception $exp) {
			Yii::error($exp, __METHOD__);
			return new SmsSendResult(false, $exp->getMessage());
		}

	}

	public function receive()
	{
		return [];
	}

}
