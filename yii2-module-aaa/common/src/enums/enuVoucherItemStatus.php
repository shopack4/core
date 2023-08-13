<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuVoucherItemStatus extends BaseEnum
{
  const New				= 'N';
  const Error			= 'E';
  const Processed	= 'P';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::New				=> 'New',
		self::Error			=> 'Error',
		self::Processed	=> 'Processed',
	];

};
