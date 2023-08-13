<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\rest;

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
			enuColumnInfo::search     => 'like',
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
			enuColumnInfo::type       => 'integer',
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
