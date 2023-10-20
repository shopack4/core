<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\extensions\gateways\sms;

use Yii;
use Ramsey\Uuid\Uuid;
use shopack\aaa\backend\classes\BaseSmsGateway;
use shopack\aaa\backend\classes\SmsSendResult;
use shopack\aaa\backend\classes\ISmsGateway;

class DevTestSmsGateway
	extends BaseSmsGateway
	implements ISmsGateway
{
	public function getTitle()
	{
		return '*** درگاه پیامک تست برنامه نویس ***';
	}

	public function send(
		$message,
		$to,
		$from = null, //null => use default in gtwPluginParameters
		$templateName = null,
		$templateParams = null
	) : SmsSendResult {
		return new SmsSendResult(true, null, Uuid::uuid4()->toString());

	}

	public function receive()
	{
		return [];
	}

}
