<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\helpers;

use Yii;

class LanguageHelper
{
	public static function getCurrentLanguage()
	{
		$request = Yii::$app->getRequest();

		if ($request->cookies->has('language'))
			return $request->cookies->get('language');

		if (empty(Yii::$app->language) == false)
			return Yii::$app->language;

		return null;
	}

	public static function setCurrentLanguage($language)
	{
		//todo: complete this
	}

}
