<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\cmn\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuLanguageStatus extends BaseEnum
{
  const Active 		= 'A';
  const Inactive 	= 'D';
  const Removed 	= 'R';

	public static $messageCategory = 'cmn';

	public static $list = [
		[
			self::Active		=> 'Active',
			self::Inactive	=> 'Inactive',
			self::Removed 	=> 'Removed',
		],
		'form' => [
			self::Active,
			self::Inactive,
		],
	];

};
