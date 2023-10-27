<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\accounting\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuAmountType extends BaseEnum
{
  const Percent	= '%';
  const Price 	= '$';
  const Free 		= 'Z';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::Percent	=> 'Percent',
		self::Price		=> 'Price',
		self::Free		=> 'Free',
	];

};
