<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\cmn\frontend\common\helpers;

use shopack\cmn\frontend\common\models\LanguageModel;

class I18NHelper
{
	private static $_languagesMap;
	public static function getLanguagesMap()
	{
		if (empty(self::$_languagesMap)) {
			self::$_languagesMap = [];
			$languages = LanguageModel::find()->asArray()->all();
			foreach ($languages as $lng)
			{
				$lngCode = implode('_', array_filter([$lng['lngLanguageCode'], $lng['lngCountryCode']]) );
				self::$_languagesMap[$lngCode] = $lng;
			}
		}

		return self::$_languagesMap;
	}

	public static function getMultiLanguageAttributs($model, $fieldName, $I18NDataFieldName)
	{
		$languagesMap = self::getLanguagesMap();

		$attributes = [];
		foreach ($model->$I18NDataFieldName as $lngCode => $fields)
		{
			foreach ($fields as $field => $value)
			{
				if ($field == $fieldName) {
					$attributes[] = [
						'attribute' => "{$I18NDataFieldName}[{$lngCode}][{$field}]",
						'label' => $model->getAttributeLabel($field) . " ({$languagesMap[$lngCode]['lngName']})",
					];
				}
			}
		}
		return $attributes;
	}

}
