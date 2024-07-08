<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuApprovalRequestStatus extends BaseEnum
{
	// -----------------------------------------------------
	// |A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z|
	// |x| | | | | | | | | | | | |x| | | | |x| | | | |x| | |
	// -----------------------------------------------------

  const New 				= 'N';
  const Sent 				= 'S';
  const Applied 		= 'A';
  const Expired 		= 'X'; //'E';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::New 				=> 'New',
		self::Sent 				=> 'Sent',
		self::Applied			=> 'Applied',
		self::Expired 		=> 'Expired',
	];

};
