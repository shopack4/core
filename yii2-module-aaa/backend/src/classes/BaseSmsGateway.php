<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\classes;

use Yii;
use shopack\base\common\base\BaseGateway;

class BaseSmsGateway extends BaseGateway
{
	const PARAM_HEADER	= 'header';
	const PARAM_FOOTER	= 'footer';

	const USAGE_LAST_SEND_DATE		= 'last_date';
	const USAGE_SENT_COUNT		= 'all_count';

	public function getParametersSchema()
	{
		return array_merge([
			[
				'id' => self::PARAM_HEADER,
				'label' => 'Header',
				'type' => 'string',
				'mandatory' => 0,
			],
			[
				'id' => self::PARAM_FOOTER,
				'label' => 'Footer',
				'type' => 'string',
				'mandatory' => 0,
			],
		], parent::getParametersSchema());
	}

	public function getUsagesSchema()
	{
		return array_merge([
			[
				'id' => self::USAGE_LAST_SEND_DATE,
				'type' => 'string',
				'label' => 'Last send date',
				'format' => 'jalaliWithTime',
			],
			[
				'id' => self::USAGE_SENT_COUNT,
				'type' => 'string',
				'label' => 'Sent count',
				'format' => 'decimal',
			],
		], parent::getUsagesSchema());
	}

	public function prepareMessageForSend(&$message)
	{
		$header	= $this->extensionModel->gtwPluginParameters[self::PARAM_HEADER];
		$footer	= $this->extensionModel->gtwPluginParameters[self::PARAM_FOOTER];

		if (empty($header) == false)
			$message = $header . "\n" . $message;

		if (empty($footer) == false)
			$message = $message . "\n" . $footer;

	}

}
