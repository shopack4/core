<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuMessageStatus extends BaseEnum
{
	// -----------------------------------------------------
	// |A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z|
	// |x|x| | |x| | | | | | | | |x| |x| |x|x| | | | | | | |
	// -----------------------------------------------------

  const New 				= 'N';
  const Processing	= 'P';
  const Sent 				= 'S';
  const FirstTry		= 'A';
  const SecondTry		= 'B';
  const Error 			= 'E';
  const Removed 		= 'R';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::New 				=> 'New',
		self::Processing	=> 'Processing',
		self::FirstTry		=> 'First Try',
		self::SecondTry		=> 'Second Try',
		self::Sent 				=> 'Sent',
		self::Error 			=> 'Error',
		self::Removed 		=> 'Removed',
	];

};
