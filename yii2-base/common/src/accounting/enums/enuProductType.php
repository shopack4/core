<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\accounting\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuProductType extends BaseEnum
{
  const Physical 	= 'P';
  const Digital 	= 'D';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::Physical		=> 'Physical',
		self::Digital	=> 'Digital',
	];

};
