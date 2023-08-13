<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuOfflinePaymentStatus extends BaseEnum
{
	const WaitForApprove = 'W';
	const Approved       = 'A';
	const Rejected       = 'J';
	const Removed        = 'R';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::WaitForApprove => 'Wait For Approve',
		self::Approved       => 'Approved',
		self::Rejected       => 'Rejected',
		self::Removed        => 'Removed',
	];

};
