<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\accounting\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuUserAssetStatus extends BaseEnum
{
  const Draft 		= 'D';
  const Pending 	= 'P';
  const Active 		= 'A';
  const Blocked 	= 'B';
  const Removed 	= 'R';

	public static $messageCategory = 'aaa';

	public static $list = [
		[
			self::Draft			=> 'Draft',
			self::Pending		=> 'Pending',
			self::Active		=> 'Active',
			self::Blocked		=> 'Blocked',
			self::Removed 	=> 'Removed',
		],
		'form' => [
			self::Draft,
			self::Pending,
			self::Active,
			self::Blocked,
		],
	];

};
