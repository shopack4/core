<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuCheckoutStep extends BaseEnum
{
  const View 			= 'V';
  const Payment		= 'P';
  const Final			= 'F';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::View 			=> 'View',
		self::Payment		=> 'Payment',
		self::Final			=> 'Final',
	];

};
