<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuSessionStatus extends BaseEnum
{
  const Active 						= 'A';
	// Inactive 'D'
  const Removed 					= 'R';
  const Pending 					= 'P';
  const ForLoginByMobile	= 'L';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::Active						=> 'Active',
		self::Removed 					=> 'Removed',
		self::Pending 					=> 'Pending',
		self::ForLoginByMobile	=> 'For Login By Mobile',
	];

};
