<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuForgotPasswordRequestMessageType extends BaseEnum
{
  const RequestByEmail  = 'forgotPassByEmail';
  const RequestByMobile = 'forgotPassByMobile';
  const ChangedByEmail  = 'passChangedByEmail';
  const ChangedByMobile = 'passChangedByMobile';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::RequestByEmail  => 'forgotPassByEmail',
		self::RequestByMobile => 'forgotPassByMobile',
		self::ChangedByEmail  => 'passChangedByEmail',
		self::ChangedByMobile => 'passChangedByMobile',
	];

};
