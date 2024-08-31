<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnumWithADR;

abstract class enuUserStatus extends BaseEnumWithADR
{
	// B: used in base class
	// -----------------------------------------------------
	// |A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z|
	// |B| | |B| | | | | | | |x| | | | | |B| | | | | | | | |
	// -----------------------------------------------------

  const NewForLoginByMobile 	= 'L'; //will be delete after 24 hours if not changed to others

	public static $messageCategory = 'aaa';

	public static $list = [
		self::Active							 => 'Active',
		self::Inactive						 => 'Inactive',
		self::Removed 						 => 'Removed',
		self::NewForLoginByMobile	 => 'For Login By Mobile',
	];

};
