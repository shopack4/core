<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\accounting\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuDiscountType extends BaseEnum
{
	const System = 'S';
	const Coupon = 'C';
	// const AsPromotion = 3;

	public static $messageCategory = 'aaa';

	public static $list = [
		self::System => 'System Discount',
		self::Coupon => 'As Coupon',
		// self::AsPromotion => 'As Promotion',
	];

};
