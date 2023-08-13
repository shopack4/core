<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuMessageTemplateMedia extends BaseEnum
{
  const Email 	= 'E';
  const Sms			= 'S';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::Email 	=> 'Email',
		self::Sms			=> 'Sms',
	];

};
