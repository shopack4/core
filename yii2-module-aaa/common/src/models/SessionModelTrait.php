<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\models;

use shopack\base\common\rest\ModelColumnHelper;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\rest\enuColumnSearchType;
use shopack\aaa\common\enums\enuSessionStatus;
use shopack\base\common\validators\JsonValidator;

/*
'ssnID',
'ssnUUID',
'ssnUserID',
'ssnIPv4',
'ssnInfo',
'ssnJWT',
'ssnJWTMD5',
'ssnTokenExpireAt',
'ssnSessionExpireAt',
'ssnOldJwt',
'ssnRefreshedAt',
'ssnRefreshCount',
'ssnLockedAt',
'ssnLockedBy',
'ssnStatus',
'ssnCreatedAt',
'ssnCreatedBy',
'ssnUpdatedAt',
'ssnUpdatedBy',
'ssnRemovedAt',
'ssnRemovedBy',
*/
trait SessionModelTrait
{
  public static $primaryKey = ['ssnID'];

	public function primaryKeyValue() {
		return $this->ssnID;
	}

  public function columnsInfo()
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
      'ssnIPv4' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'ssnInfo' => [
        enuColumnInfo::type       => JsonValidator::class,
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
        enuColumnInfo::selectable => false, //true,
      ],
      'ssnTokenExpireAt' => [
        enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'ssnSessionExpireAt' => [
        enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'ssnOldJwt' => [
        enuColumnInfo::type       => ['string', 'max' => 2048],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => false, //true,
      ],
      'ssnRefreshedAt' => [
        enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'ssnRefreshCount' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'ssnLockedAt' => [
        enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'ssnLockedBy' => [
        enuColumnInfo::type       => ['string', 'max' => 64],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => false, //true,
      ],
      'ssnStatus' => [
        enuColumnInfo::isStatus   => true,
        enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => enuSessionStatus::Pending,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
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

  public function getUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'ssnUserID']);
	}

}
