<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuUploadQueueStatus extends BaseEnum
{
  const New 		= 'N';
	const Stored  = 'S';
	const Error   = 'E';
  const Removed = 'R';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::New 		=> 'New',
		self::Stored	=> 'Stored',
		self::Error 	=> 'Error',
		self::Removed => 'Removed',
	];

};
