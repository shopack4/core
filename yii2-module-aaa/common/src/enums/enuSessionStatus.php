<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuSessionStatus extends BaseEnum
{
  const Pending 					= 'P';
  const Active 						= 'A';
  const ForLoginByMobile	= 'L';
  const Removed 					= 'R';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::Pending 					=> 'Pending',
		self::Active						=> 'Active',
		self::ForLoginByMobile	=> 'For Login By Mobile',
		self::Removed 					=> 'Removed',
	];

};
