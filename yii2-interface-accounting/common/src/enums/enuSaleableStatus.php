<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\interface\accounting\common\enums;

use shopack\base\common\base\BaseEnumWithADR;

abstract class enuSaleableStatus extends BaseEnumWithADR
{
	// B: used in base class
	// -----------------------------------------------------
	// |A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z|
	// |B| | |B| | | | | | | | | | | | | |B| | | | | | | | |
	// -----------------------------------------------------

	public static $messageCategory = 'aaa';

	public static $list = [
		[
			self::Active		=> 'Active',
			self::Inactive	=> 'Inactive',
			self::Removed 	=> 'Removed',
		],
		'form' => [
			self::Active,
			self::Inactive,
		],
	];

};
