<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuUploadFileStatus extends BaseEnum
{
  const New 				= 'N';
  const Removed 		= 'R';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::New 				=> 'New',
		self::Removed 		=> 'Removed',
	];

};
