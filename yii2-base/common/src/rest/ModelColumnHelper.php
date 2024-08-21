<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\rest;

use Yii;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\rest\enuColumnSearchType;
use shopack\base\common\validators\JsonValidator;

class ModelColumnHelper
{
	public static function adhoc()
	{
		return [
			enuColumnInfo::adhoc => true,
		];
	}

	public static function UUID()
	{
		return [
			enuColumnInfo::type       => 'safe', //['string', 'max' => 38],
			enuColumnInfo::validator  => null,
			enuColumnInfo::default    => 'uuid', //filled in applyDefaultValuesFromColumnsInfo
			enuColumnInfo::required   => false,  //true,
			enuColumnInfo::selectable => true,
			enuColumnInfo::search     => enuColumnSearchType::like,
		];
	}

	public static function I18NData($model, $i18nDataFieldName, array $fieldNames)
	{
		$className = get_class($model);
		$className::$i18nDataFields += [
			$i18nDataFieldName => $fieldNames
		];

		$fields = [
			$i18nDataFieldName => [
				enuColumnInfo::type       => JsonValidator::class,
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false,
				enuColumnInfo::selectable => true,
				enuColumnInfo::search     => enuColumnSearchType::like,
			]
		];

		// $isBackend = (str_contains($className, '\\backend\\'));

		// foreach ($fieldNames as $f)
		// {
		// 	$fields = array_merge($fields, [
		// 		$f . '_translated' => [
		// 			enuColumnInfo::virtual		=> true,
		// 			enuColumnInfo::type       => 'string',
		// 			enuColumnInfo::validator  => null,
		// 			enuColumnInfo::default    => null,
		// 			enuColumnInfo::required   => false,

		// 			// enuColumnInfo::selectable => !$isBackend,
		// 			enuColumnInfo::selectable => true,

		// 			enuColumnInfo::search     => enuColumnSearchType::like,
		// 		]
		// 	]);
		// }

		return $fields;
	}

	public static function CreatedAt()
	{
		return [
			enuColumnInfo::type       => 'safe',
			enuColumnInfo::validator  => null,
			enuColumnInfo::default    => null,
			enuColumnInfo::required   => false,
			enuColumnInfo::selectable => true,
		];
	}

	public static function CreatedBy($filter = null)
	{
		return [
			enuColumnInfo::type       => 'integer',
			enuColumnInfo::validator  => null,
			enuColumnInfo::default    => null,
			enuColumnInfo::required   => false,
			enuColumnInfo::selectable => true,
			enuColumnInfo::filter     => $filter ?? function($model, $fieldName, $isInRelation) {
				return (Yii::$app->user->isGuest || ($model->$fieldName != Yii::$app->user->id));
			},
		];
	}

	public static function UpdatedAt()
	{
		return [
			enuColumnInfo::type       => 'safe',
			enuColumnInfo::validator  => null,
			enuColumnInfo::default    => null,
			enuColumnInfo::required   => false,
			enuColumnInfo::selectable => true,
		];
	}

	public static function UpdatedBy($filter = null)
	{
		return [
			enuColumnInfo::type       => 'integer',
			enuColumnInfo::validator  => null,
			enuColumnInfo::default    => null,
			enuColumnInfo::required   => false,
			enuColumnInfo::selectable => true,
			enuColumnInfo::filter     => $filter ?? function($model, $fieldName, $isInRelation) {
				return (Yii::$app->user->isGuest || ($model->$fieldName != Yii::$app->user->id));
			},
		];
	}

	public static function RemovedAt()
	{
		return [
			enuColumnInfo::type       => 'safe', //int, now() 'integer',
			enuColumnInfo::validator  => null,
			enuColumnInfo::default    => 0,
			enuColumnInfo::required   => false,
			enuColumnInfo::selectable => true,
		];
	}

	public static function RemovedBy($filter = null)
	{
		return [
			enuColumnInfo::type       => 'integer',
			enuColumnInfo::validator  => null,
			enuColumnInfo::default    => null,
			enuColumnInfo::required   => false,
			enuColumnInfo::selectable => true,
			enuColumnInfo::filter     => $filter ?? function($model, $fieldName, $isInRelation) {
				return (Yii::$app->user->isGuest || ($model->$fieldName != Yii::$app->user->id));
			},
		];
	}

};
