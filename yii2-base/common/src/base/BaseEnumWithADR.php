<?php

namespace shopack\base\common\base;

use Yii;
use shopack\base\common\base\BaseEnum;

abstract class BaseEnumWithADR extends BaseEnum
{
	// -----------------------------------------------------
	// |A|B|C|D|E|F|G|H|I|J|K|L|M|N|O|P|Q|R|S|T|U|V|W|X|Y|Z|
	// |x| | |x| | | | | | | | | | | | | |x| | | | | | | | |
	// -----------------------------------------------------

  const Active 		= 'A';
  const Inactive 	= 'D';
  const Removed 	= 'R';

	public static function getIcon($value)
	{
		switch ($value) {
			case self::Active:
				return Yii::$app->formatter->asBoolean(true);

			case self::Inactive:
				return Yii::$app->formatter->asBoolean(false);

			// case self::Removed:
			// 	return "<i class='fa fa-percentage text-danger'></i>";
		}

		return null;
	}

}
