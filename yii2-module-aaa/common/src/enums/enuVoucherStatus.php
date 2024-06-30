<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use Yii;
use shopack\base\common\base\BaseEnum;

abstract class enuVoucherStatus extends BaseEnum
{
	// -----------------------------------------------------
	// |A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z|
	// | | |x| |x|x| | | | | | | |x| | | |x|x| | | |x| | | |
	// -----------------------------------------------------

  const New							= 'N';
	const WaitForPayment	= 'W';
	const Canceled				= 'C';
	//const Accepted				= 'A'; //for proforma
  const Settled					= 'S';
  const Finished				= 'F';
  const Error						= 'E';
  const Removed					= 'R';


	public static $messageCategory = 'aaa';

	public static $list = [
		self::New							=> 'New',
		self::WaitForPayment	=> 'Wait For Payment',
		self::Canceled				=> 'Canceled',
		self::Settled					=> 'Settled',
		self::Finished				=> 'Finished',
		self::Error						=> 'Error',
		self::Removed					=> 'Removed',
	];

	// public static function getForBasketList()
	// {
	// 	return [
	// 		self::New				=> Yii::t('aaa', 'New'),
	// 		// self::Settled		=> Yii::t('aaa', 'Settled'),
	// 		self::Error			=> Yii::t('aaa', 'Error'),
	// 		// self::Finished	=> Yii::t('aaa', 'Finished'),
	// 	];
	// }

};
