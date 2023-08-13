<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuForgotPasswordRequestStatus extends BaseEnum
{
  const New 				= 'N';
  const Sent 				= 'S';
  const Applied 		= 'A';
  const Expired 		= 'X'; //'E';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::New 				=> 'New',
		self::Sent 				=> 'Sent',
		self::Applied			=> 'Applied',
		self::Expired 		=> 'Expired',
	];

};
