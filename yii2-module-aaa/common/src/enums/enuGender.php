<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuGender extends BaseEnum
{
	const NotSet	= 'N';
	const Male		= 'M';
	const Female	= 'F';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::NotSet	=> 'None',
		self::Male		=> 'Male',
		self::Female	=> 'Female',
	];

	public static function getAbrLabel($value)
	{
		if ($value == self::Male)
			return 'آقای';

		if ($value == self::Female)
			return 'خانم';

		return '';
	}

};
