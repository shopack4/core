<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\rest;

use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\rest\enuColumnSearchType;
use shopack\base\common\validators\JsonValidator;

class ModelColumnHelper
{
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

	public static function I18NData(Array $fieldNames)
	{
		return [
			enuColumnInfo::type       => JsonValidator::class,
			enuColumnInfo::validator  => null,
			enuColumnInfo::default    => null,
			enuColumnInfo::required   => false,
			enuColumnInfo::selectable => true,
			enuColumnInfo::search     => enuColumnSearchType::like,
		];
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

	public static function CreatedBy()
	{
		return [
			enuColumnInfo::type       => 'integer',
			enuColumnInfo::validator  => null,
			enuColumnInfo::default    => null,
			enuColumnInfo::required   => false,
			enuColumnInfo::selectable => true,
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

	public static function UpdatedBy()
	{
		return [
			enuColumnInfo::type       => 'integer',
			enuColumnInfo::validator  => null,
			enuColumnInfo::default    => null,
			enuColumnInfo::required   => false,
			enuColumnInfo::selectable => true,
		];
	}

	public static function RemovedAt()
	{
		return [
			enuColumnInfo::type       => 'safe', //int, now() 'integer',
			enuColumnInfo::validator  => null,
			enuColumnInfo::default    => null,
			enuColumnInfo::required   => false,
			enuColumnInfo::selectable => true,
		];
	}

	public static function RemovedBy()
	{
		return [
			enuColumnInfo::type       => 'integer',
			enuColumnInfo::validator  => null,
			enuColumnInfo::default    => null,
			enuColumnInfo::required   => false,
			enuColumnInfo::selectable => true,
		];
	}

};
