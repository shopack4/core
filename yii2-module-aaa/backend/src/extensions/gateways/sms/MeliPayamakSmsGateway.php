<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\extensions\gateways\sms;

use Yii;
use Melipayamak\MelipayamakApi;
use shopack\base\common\helpers\Json;
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
	// const PARAM_BODY_ID			= 'bodyid';
	const PARAM_BODY_IDS		= 'bodyids';

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
			// [
			// 	'id' => self::PARAM_BODY_ID,
			// 	'type' => 'string',
			// 	'mandatory' => 1,
			// 	'label' => 'Body ID',
			// 	'style' => 'direction:ltr',
			// ],
			[
				'id' => self::PARAM_BODY_IDS,
				'mandatory' => 1,
				'label' => 'Body IDs',
				'style' => 'direction:ltr',
				'type' => 'kvp-multi',
				'typedef' => [
					'key' => [
						'label' => 'Body Id',
					],
					'value' => [
						[
							'id' => 'params',
							'label' => 'Body Params',
						],
						[
							'id' => 'templates',
							'label' => 'Templates',
						],
					],
				],
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
		$from = null, //null => use default in gtwPluginParameters
		$templateName = null,
		$templateParams = null
	) : SmsSendResult {
		$username		= $this->extensionModel->gtwPluginParameters[self::PARAM_USERNAME];
		$password		= $this->extensionModel->gtwPluginParameters[self::PARAM_PASSWORD];
		$lineNumber	= $this->extensionModel->gtwPluginParameters[self::PARAM_LINENUMBER] ?? null;
		// $bodyid			= $this->extensionModel->gtwPluginParameters[self::PARAM_BODY_ID] ?? null;

		if (empty($templateParams))
			$bodyids = null;
		else
			$bodyids = $this->extensionModel->gtwPluginParameters[self::PARAM_BODY_IDS] ?? null;

		try {
			$api = new MelipayamakApi($username, $password);
			$sms = $api->sms();

			//1: try send by body id
			if (empty($bodyids) == false) {
				$found = false;
				foreach ($bodyids as $item) {
					if (empty($templateName)) {
						if ($item['value']['templates'] == '*') {
							$found = $item;
							break;
						} else
							continue;
					} else if (strpos(",{$item['value']['templates']},", ",{$templateName},") !== false) {
						$found = $item;
						break;
					}
				}

				if (($found === false) || empty($found['value']['params'])) {
					$bodyids = null;
				} else {
					$paramsSchema = $found['value']['params'];
					if (is_array($paramsSchema) == false)
						$paramsSchema = explode(',', $paramsSchema);

					$params = [];
					foreach ($paramsSchema as $ps) {
						$params[] = $templateParams[$ps];
					}

					$response = $sms->sendByBaseNumber($params, $to, $found['key']);

					//"{"Message":"An unexpected error occured"}"
				}
			}

			//2: try send direct
			if (empty($bodyids)) {
				if (empty($from) && (empty($lineNumber) == false)) {
					$from = $lineNumber;
				}

				$this->prepareMessageForSend($message);
				$response = $sms->send($to, $from, $message);

				//"{"Value":"0","RetStatus":35,"StrRetStatus":"InvalidData"}"
			}

			$response = Json::decode($response);
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
