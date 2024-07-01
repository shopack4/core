<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuVoucherType extends BaseEnum
{
	// -----------------------------------------------------
	// |A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z|
	// | |x|x| | |x| | |x| | | |x| | | | | | |x| | |x| | |x|
	// -----------------------------------------------------

	const Basket				= 'B'; //proforma. convert to Invoice after checkout
	const Invoice				= 'I';
  const Withdrawal		= 'W';
  const Income				= 'M';
  const Credit				= 'C';
  const TransferTo		= 'T'; //to another user (email / mobile)
  const TransferFrom	= 'F'; //from another user (email / mobile)
  const Prize					= 'Z';
	//Freeze
	//Unfreeze

	public static $messageCategory = 'aaa';

	public static $list = [
		self::Basket				=> 'Basket',
		self::Invoice				=> 'Invoice',
		self::Withdrawal		=> 'Withdrawal',
		self::Income				=> 'Income',
		self::Credit				=> 'Credit',
		self::TransferTo		=> 'Transfer To',
		self::TransferFrom	=> 'Transfer From',
		self::Prize					=> 'Prize',
	];

};
