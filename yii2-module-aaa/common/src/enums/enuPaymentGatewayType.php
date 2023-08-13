<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuPaymentGatewayType extends BaseEnum
{
	const IranBank					= 'I';
	const IranIntermediate	= 'N';
	const DevTest						= '-';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::IranBank					=> 'Iran Bank',
		self::IranIntermediate	=> 'Iran Intermediate Gateway',
		self::DevTest						=> 'Dev Test',
	];

	// public static function getHRLabel($value)
	// {
	// 	if ($value == self::Prepay)
	// 		return 'پرداخت آنلاین';

	// 	if ($value == self::Postpay)
	// 		return 'پرداخت در محل';

	// 	if ($value == self::Both)
	// 		return 'پرداخت آنلاین + پرداخت در محل';

	// 	if ($value == self::ByCredit)
	// 		return 'پرداخت با اعتبار';

	// 	return '';
	// }

}
