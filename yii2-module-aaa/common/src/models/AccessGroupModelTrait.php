<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\models;

use shopack\base\common\rest\ModelColumnHelper;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\rest\enuColumnSearchType;
use shopack\base\common\validators\JsonValidator;
use shopack\aaa\common\enums\enuAccessGroupStatus;

/*
'agpID',
'agpUUID',
'agpName',
'agpPrivs',
'agpI18NData',
'agpStatus',
'agpCreatedAt',
'agpCreatedBy',
'agpUpdatedAt',
'agpUpdatedBy',
'agpRemovedAt',
'agpRemovedBy',
*/
trait AccessGroupModelTrait
{
  public static $primaryKey = ['agpID'];

	public function primaryKeyValue() {
		return $this->agpID;
	}

  public function columnsInfo()
  {
    return [
      'agpID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'agpUUID' => ModelColumnHelper::UUID(),
			'agpName' => [
        enuColumnInfo::type       => ['string', 'max' => 64],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
			'agpPrivs' => [
        enuColumnInfo::type       => JsonValidator::class,
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
			'agpI18NData' => ModelColumnHelper::I18NData(['agpName']),
      'agpStatus' => [
        enuColumnInfo::isStatus   => true,
        enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => enuAccessGroupStatus::Active,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
      ],

      'agpCreatedAt' => ModelColumnHelper::CreatedAt(),
      'agpCreatedBy' => ModelColumnHelper::CreatedBy(),
      'agpUpdatedAt' => ModelColumnHelper::UpdatedAt(),
      'agpUpdatedBy' => ModelColumnHelper::UpdatedBy(),
      'agpRemovedAt' => ModelColumnHelper::RemovedAt(),
      'agpRemovedBy' => ModelColumnHelper::RemovedBy(),
    ];
  }

  public function getCreatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'agpCreatedBy']);
	}

	public function getUpdatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'agpUpdatedBy']);
	}

	public function getRemovedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'agpRemovedBy']);
	}

}
