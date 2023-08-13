<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuVoucherType extends BaseEnum
{
  const Basket				= 'B';
  const Withdrawal		= 'W';
  const Income				= 'I';
  const Credit				= 'C';
  const TransferTo		= 'T';
  const TransferFrom	= 'F';
  const Prize					= 'Z';
	//Freeze
	//Unfreeze

	public static $messageCategory = 'aaa';

	public static $list = [
		self::Basket				=> 'Basket',
		self::Withdrawal		=> 'Withdrawal',
		self::Income				=> 'Income',
		self::Credit				=> 'Credit',
		self::TransferTo		=> 'Transfer To',
		self::TransferFrom	=> 'Transfer From',
		self::Prize					=> 'Prize',
	];

};
