<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuUserMilitaryStatus extends BaseEnum
{
  const LackOfNeed					= 'L'; //عدم نیاز
  const SubjectToService		= 'S'; //مشمول
  const InTheArmy						= 'I'; //در حال سربازی
  const Done								= 'D'; //انجام شده
  const ExemptedFromService	= 'E'; //معاف

	public static $messageCategory = 'aaa';

	public static $list = [
		self::LackOfNeed					=> 'Lack Of Need', //عدم نیاز
		self::SubjectToService		=> 'Subject To Military Service', //مشمول
		self::InTheArmy						=> 'In The Army', //در حال سربازی
		self::Done								=> 'Done', //انجام شده
		self::ExemptedFromService	=> 'Exempted From Military Service', //معاف
	];

};
