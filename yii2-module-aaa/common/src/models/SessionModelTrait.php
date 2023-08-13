<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\models;

use shopack\base\common\rest\ModelColumnHelper;
use shopack\base\common\rest\enuColumnInfo;
use shopack\aaa\common\enums\enuSessionStatus;

/*
'ssnID',
'ssnUUID',
'ssnUserID',
'ssnJWT',
'ssnJWTMD5',
'ssnStatus',
'ssnExpireAt',
'ssnCreatedAt',
'ssnCreatedBy',
'ssnUpdatedAt',
'ssnUpdatedBy',
'ssnRemovedAt',
'ssnRemovedBy',
*/
trait SessionModelTrait
{
  public function primaryKeyValue() {
		return $this->ssnID;
	}

  public static function columnsInfo()
  {
    return [
      'ssnID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'ssnUUID' => ModelColumnHelper::UUID(),
      'ssnUserID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'ssnJWT' => [
        enuColumnInfo::type       => ['string', 'max' => 2048],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'ssnStatus' => [
        enuColumnInfo::isStatus   => true,
        enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => enuSessionStatus::Pending,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => true,
      ],
      'ssnExpireAt' => [
        enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],

      'ssnCreatedAt' => ModelColumnHelper::CreatedAt(),
      'ssnCreatedBy' => ModelColumnHelper::CreatedBy(),
      'ssnUpdatedAt' => ModelColumnHelper::UpdatedAt(),
      'ssnUpdatedBy' => ModelColumnHelper::UpdatedBy(),
      'ssnRemovedAt' => ModelColumnHelper::RemovedAt(),
      'ssnRemovedBy' => ModelColumnHelper::RemovedBy(),

    ];
  }

  public function getCreatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'ssnCreatedBy']);
	}

	public function getUpdatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'ssnUpdatedBy']);
	}

	public function getRemovedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'ssnRemovedBy']);
	}

}
