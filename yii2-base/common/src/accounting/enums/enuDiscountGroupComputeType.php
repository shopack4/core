<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\accounting\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuDiscountGroupComputeType extends BaseEnum
{
	const Sum = 'S';
	const Min = 'N';
	const Max = 'S';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::Sum => 'Sum',
		self::Min => 'Min',
		self::Max => 'Max',
	];

};
