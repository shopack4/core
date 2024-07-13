<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuBasicDefinitionType extends BaseEnum
{
  const OfflinePaymentRejectReason	= 'O';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::OfflinePaymentRejectReason	=> 'Offline Payment Reject Reason',
	];

};
