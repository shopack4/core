<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use Yii;
use shopack\base\common\base\BaseEnum;

abstract class enuVoucherStatus extends BaseEnum
{
  const New				= 'N';
  const Error			= 'E';
  const Settled		= 'S';
  const Finished	= 'F';
  const Removed		= 'R';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::New				=> 'New',
		self::Error			=> 'Error',
		self::Settled		=> 'Settled',
		self::Finished	=> 'Finished',
		self::Removed		=> 'Removed',
	];

	public static function getForBasketList()
	{
		return [
			self::Error			=> Yii::t('aaa', 'Error'),
			self::Settled		=> Yii::t('aaa', 'Settled'),
			self::Finished	=> Yii::t('aaa', 'Finished'),
		];
	}

};
