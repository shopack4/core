<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\models;

use shopack\base\common\rest\ModelColumnHelper;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\rest\enuColumnSearchType;
// use shopack\base\common\validators\JsonValidator;
use shopack\aaa\common\enums\enuMessageTemplateStatus;
use shopack\aaa\common\enums\enuMessageTemplateMedia;

/*
'mstID',
'mstUUID',
'mstKey',
'mstMedia',
'mstLanguage',
'mstTitle',
'mstBody',
'mstParamsPrefix',
'mstParamsSuffix',
'mstIsSystem',
'mstStatus',
'mstCreatedAt',
'mstCreatedBy',
'mstUpdatedAt',
'mstUpdatedBy',
'mstRemovedAt',
'mstRemovedBy',
*/
trait MessageTemplateModelTrait
{
  public static $primaryKey = ['mstID'];

	public function primaryKeyValue() {
		return $this->mstID;
	}

	public function columnsInfo()
  {
    return [
			'mstID' => [
				enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
			],
      'mstUUID' => ModelColumnHelper::UUID(),
			'mstKey' => [
				enuColumnInfo::type       => ['string', 'max' => 64],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
			],
			'mstMedia' => [
				enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null, //enuMessageTemplateMedia
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
			],
			'mstLanguage' => [ //fa, fa_IR
				enuColumnInfo::type       => ['string', 'max' => 5],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
			],
			'mstTitle' => [
				enuColumnInfo::type       => ['string', 'max' => 512],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
			],
			'mstBody' => [
				enuColumnInfo::type       => 'string', //mediumtext
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
			],
			'mstParamsPrefix' => [
				enuColumnInfo::type       => ['string', 'max' => 10],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => '{{',
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
			],
			'mstParamsSuffix' => [
				enuColumnInfo::type       => ['string', 'max' => 10],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => '}}',
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
			],
      'mstIsSystem' => [
				enuColumnInfo::type       => 'boolean',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => false,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
			],
			'mstStatus' => [
        enuColumnInfo::isStatus   => true,
				enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => enuMessageTemplateStatus::Active,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
			],

      'mstCreatedAt' => ModelColumnHelper::CreatedAt(),
      'mstCreatedBy' => ModelColumnHelper::CreatedBy(),
      'mstUpdatedAt' => ModelColumnHelper::UpdatedAt(),
      'mstUpdatedBy' => ModelColumnHelper::UpdatedBy(),
      'mstRemovedAt' => ModelColumnHelper::RemovedAt(),
      'mstRemovedBy' => ModelColumnHelper::RemovedBy(),
		];
  }

  public function getCreatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'mstCreatedBy']);
	}

  public function getUpdatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'mstUpdatedBy']);
	}

	public function getRemovedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'mstRemovedBy']);
	}

}
