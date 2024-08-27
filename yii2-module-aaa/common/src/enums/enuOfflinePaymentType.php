<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuOfflinePaymentType extends BaseEnum
{
	// -----------------------------------------------------
	// |A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z|
	// |x|x|x| | | | | | | |x| | | | |x|x| | | | | | | | | |
	// -----------------------------------------------------

	const Cash						= 'C';
	const Pos							= 'P';
	const ToCart					= 'K';
	const ToAccountNumber	= 'A'; //International Bank Account Number : IB`A`N
	const ToISBN					= 'B'; //International Standard Book Number: IS`B`N
	const Cheque					= 'Q';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::Cash						=> 'Cash',
		self::Pos							=> 'Pos',
		self::ToCart					=> 'To Cart',
		self::ToAccountNumber	=> 'To Account Number',
		self::ToISBN					=> 'To ISBN',
		self::Cheque					=> 'Cheque',
	];

};
