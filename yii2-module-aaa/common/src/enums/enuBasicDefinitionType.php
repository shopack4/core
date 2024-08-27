<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuBasicDefinitionType extends BaseEnum
{
	// -----------------------------------------------------
	// |A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z|
	// | |x| | | | | | | | | | | | |x| | | | | | | | | | | |
	// -----------------------------------------------------

  const Bank	= 'B';
  const OfflinePaymentRejectReason	= 'O';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::Bank	=> 'Bank',
		self::OfflinePaymentRejectReason	=> 'Offline Payment Reject Reason',
	];

};
