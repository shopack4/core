<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\accounting\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuUserAssetStatus extends BaseEnum
{
  const Active 		= 'A';
  const Pending 	= 'P';
  const Blocked 	= 'B';
  const Removed 	= 'R';

	public static $messageCategory = 'aaa';

	public static $list = [
		[
			self::Active		=> 'Active',
			self::Pending		=> 'Pending',
			self::Blocked		=> 'Blocked',
			self::Removed 	=> 'Removed',
		],
		'form' => [
			self::Active,
			self::Pending,
			self::Blocked,
		],
	];

};
