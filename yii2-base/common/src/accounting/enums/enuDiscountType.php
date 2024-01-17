<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\accounting\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuDiscountType extends BaseEnum
{
	const System					= 'S';
	const SystemIncrease	= 'I';
	const Coupon					= 'C';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::System					=> 'Fix System Discount',
		self::SystemIncrease	=> 'Increase System Discount',
		self::Coupon					=> 'As Coupon',
	];

	public static function getIcon($value)
	{
		switch ($value) {
			case self::System:
				return "<i class='fa fa-percentage text-danger'></i>";

			case self::SystemIncrease:
				return "<i class='fa fa-plus-circle text-danger'></i>";

			case self::Coupon:
				return "<i class='fa fa-percentage text-success'></i>";
		}

		return null;
	}

};
