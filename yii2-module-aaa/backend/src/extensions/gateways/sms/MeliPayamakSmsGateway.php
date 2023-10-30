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
					'enableField' => [
						'id' => 'enable',
						'label' => 'Enable',
					],
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

	public static function sendByPattern($params, $to, $bodyid)
	{
		$url = 'https://console.melipayamak.com/api/send/shared/70824b80a9d244de92d3689923565fd8';
		$data = [
			'bodyId' => (int)$bodyid,
			'to' => $to,
			'args' => $params,
		];
		$data_string = json_encode($data);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

		// Next line makes the request absolute insecure
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		// Use it when you have trouble installing local issuer certificate
		// See https://stackoverflow.com/a/31830614/1743997

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER,
			array('Content-Type: application/json',
						'Content-Length: ' . strlen($data_string))
			);
		$result = curl_exec($ch);
		curl_close($ch);
		// to debug
		// if(curl_errno($ch)){
		//     echo 'Curl error: ' . curl_error($ch);
		// }
		return $result;
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
			//1: try send by body id
			if (empty($bodyids) == false) {
				$enableFieldID = null;
				$paramsSchema = $this->getParametersSchema();
				foreach ($paramsSchema as $paramSchema) {
					if ($paramSchema['id'] == self::PARAM_BODY_IDS) {
						if (empty($paramSchema['typedef']['enableField']) == false) {
							$enableFieldID = $paramSchema['typedef']['enableField']['id'] ?? 'enable';
							break;
						}
					}
				}

				$found = false;
				foreach ($bodyids as $item) {
					if ($enableFieldID && (($item[$enableFieldID] ?? false) == false))
						continue;

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
					$params = [];
					$paramsSchema = $found['value']['params'];

					//no params for raw patterns (without header and footer)
					if (empty($paramsSchema)) {
						$params[] = $message;
					} else {
						if (is_array($paramsSchema) == false)
							$paramsSchema = explode(',', $paramsSchema);

						foreach ($paramsSchema as $ps) {
							$params[] = $templateParams[$ps];
						}
					}

					$bid = $found['key'];
					Yii::debug('befor sending sms with sendByBaseNumber: ('
						. print_r([
								'params' => $params,
								'to' => $to,
								'bodyid' => $bid,
							], true)
						. ')');
					// $response = $sms->sendByBaseNumber($params, $to, $bid);
					$response = self::sendByPattern($params, $to, $bid);
					Yii::debug('after sending sms with sendByBaseNumber: response('
						. implode('\n', (array)($response ?? []))
						. ')'
					);

					//{"recId":4707203589561557161,"status":"ارسال موفق بود"}

					//"{"Message":"An unexpected error occured"}"
				}
			}

			//2: try send direct
			if (empty($bodyids)) {
				if (empty($from) && (empty($lineNumber) == false)) {
					$from = $lineNumber;
				}

				//append header and footer
				$this->prepareMessageForSend($message);

				$api = new MelipayamakApi($username, $password);
				$sms = $api->sms();
				$response = $sms->send($to, $from, $message);

				//"{"Value":"0","RetStatus":35,"StrRetStatus":"InvalidData"}"
			}

			$response = Json::decode($response);
			// echo $response->Value; //RecId or Error Number

			$RetValue		= $response["recId"] ?? $response["Value"] ?? null;
			$RetStatus	= $response["RetStatus"] ?? 0;
			$RetMessage	= $response["StrRetStatus"] ?? $response["Message"] ?? '';

			if (($RetValue == null) && ($RetStatus != 1))
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
