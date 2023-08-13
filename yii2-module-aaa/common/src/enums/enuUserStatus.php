<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuUserStatus extends BaseEnum
{
  const Active 								= 'A';
  const Inactive 							= 'D';
  const NewForLoginByMobile 	= 'L'; //will be delete after 24 hours if not changed to others
  const Removed 							= 'R';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::Active							 => 'Active',
		self::Inactive						 => 'Inactive',
		self::NewForLoginByMobile	 => 'For Login By Mobile',
		self::Removed 						 => 'Removed',
	];

};
