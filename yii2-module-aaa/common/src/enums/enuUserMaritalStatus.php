<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuUserMaritalStatus extends BaseEnum
{
  const NotMarried	= 'N';
  const Married			= 'M';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::NotMarried	=> 'Not Married',
		self::Married			=> 'Married',
	];

};
