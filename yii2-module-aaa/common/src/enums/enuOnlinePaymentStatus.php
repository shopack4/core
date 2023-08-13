<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuOnlinePaymentStatus extends BaseEnum
{
  const New			= 'N';
  const Pending	= 'P';
  const Paid		= 'I';
  const Error		= 'E';
  const Removed	= 'R';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::New			=> 'New',
		self::Pending	=> 'Pending',
		self::Paid		=> 'Paid',
		self::Error		=> 'Error',
		self::Removed	=> 'Removed',
	];

};
