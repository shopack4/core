<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuMessageStatus extends BaseEnum
{
  const New 				= 'N';
  const Processing	= 'P';
  const Sent 				= 'S';
  const Error 			= 'E';
  const Removed 		= 'R';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::New 				=> 'New',
		self::Processing	=> 'Processing',
		self::Sent 				=> 'Sent',
		self::Error 			=> 'Error',
		self::Removed 		=> 'Removed',
	];

};
