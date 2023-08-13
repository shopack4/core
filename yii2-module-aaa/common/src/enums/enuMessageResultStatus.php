<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuMessageResultStatus extends BaseEnum
{
  const New 				= 'N';
  const Sent 				= 'S';
  const Error 			= 'E';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::New 				=> 'New',
		self::Sent 				=> 'Sent',
		self::Error 			=> 'Error',
	];

};
