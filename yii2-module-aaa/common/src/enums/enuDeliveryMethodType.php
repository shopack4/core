<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuDeliveryMethodType extends BaseEnum
{
	const ReceiveByCustomer	= 'R';
	const SendToCustomer		= 'S';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::ReceiveByCustomer	=> 'Receive by customer',
		self::SendToCustomer		=> 'Send to customer',
	];

};
