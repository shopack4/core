<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\cmn\common\models;

use shopack\base\common\rest\ModelColumnHelper;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\rest\enuColumnSearchType;
use shopack\base\common\validators\JsonValidator;
use shopack\cmn\common\enums\enuLanguageStatus;

/*
'lngID',
'lngUUID',
'lngLanguageCode',
'lngCountryCode',
'lngName',
'lngIsPreferred',
'lngStatus',
'lngCreatedAt',
'lngCreatedBy',
'lngUpdatedAt',
'lngUpdatedBy',
'lngRemovedAt',
'lngRemovedBy',
*/
trait LanguageModelTrait
{
  public static $primaryKey = ['lngID'];

	public function primaryKeyValue() {
		return $this->lngID;
	}

  public function columnsInfo()
  {
    return [
      'lngID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'lngUUID' => ModelColumnHelper::UUID(),
      'lngLanguageCode' => [
        enuColumnInfo::type       => ['string', 'max' => 5],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
				enuColumnInfo::search     => enuColumnSearchType::like,
      ],
      'lngCountryCode' => [
        enuColumnInfo::type       => ['string', 'max' => 5],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
				enuColumnInfo::search     => enuColumnSearchType::like,
      ],
			'lngName' => [
        enuColumnInfo::type       => ['string', 'max' => 64],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
				enuColumnInfo::search     => enuColumnSearchType::like,
      ],
      'lngIsPreferred' => [
				enuColumnInfo::type       => 'boolean',
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => false,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
				enuColumnInfo::search     => enuColumnSearchType::exact,
      ],
      'lngStatus' => [
        enuColumnInfo::isStatus   => true,
        enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => enuLanguageStatus::Active,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
      ],

      'lngCreatedAt' => ModelColumnHelper::CreatedAt(),
      'lngCreatedBy' => ModelColumnHelper::CreatedBy(),
      'lngUpdatedAt' => ModelColumnHelper::UpdatedAt(),
      'lngUpdatedBy' => ModelColumnHelper::UpdatedBy(),
      'lngRemovedAt' => ModelColumnHelper::RemovedAt(),
      'lngRemovedBy' => ModelColumnHelper::RemovedBy(),
    ];
  }

  public function getCreatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'lngCreatedBy']);
	}

	public function getUpdatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'lngUpdatedBy']);
	}

	public function getRemovedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'lngRemovedBy']);
	}

}
