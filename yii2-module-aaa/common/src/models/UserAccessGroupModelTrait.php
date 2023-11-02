<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\models;

use shopack\base\common\rest\ModelColumnHelper;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\rest\enuColumnSearchType;
use shopack\base\common\validators\JsonValidator;

/*
'usragpID',
'usragpUUID',
'usragpUserID',
'usragpAccessGroupID',
'usragpStartAt',
'usragpEndAt',
'usragpCreatedAt',
'usragpCreatedBy',
'usragpUpdatedAt',
'usragpUpdatedBy',
'usragpRemovedAt',
'usragpRemovedBy',
*/
trait UserAccessGroupModelTrait
{
  public static $primaryKey = ['usragpID'];

	public function primaryKeyValue() {
		return $this->usragpID;
	}

  public static function columnsInfo()
  {
    return [
      'usragpID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'usragpUUID' => ModelColumnHelper::UUID(),
      'usragpUserID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
      ],
      'usragpAccessGroupID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
      ],
      'usragpStartAt' => [
        enuColumnInfo::type       => 'safe', //datetime
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'usragpEndAt' => [
        enuColumnInfo::type       => 'safe', //datetime
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],

      'usragpCreatedAt' => ModelColumnHelper::CreatedAt(),
      'usragpCreatedBy' => ModelColumnHelper::CreatedBy(),
      'usragpUpdatedAt' => ModelColumnHelper::UpdatedAt(),
      'usragpUpdatedBy' => ModelColumnHelper::UpdatedBy(),
      'usragpRemovedAt' => ModelColumnHelper::RemovedAt(),
      'usragpRemovedBy' => ModelColumnHelper::RemovedBy(),
    ];
  }

  public function getCreatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'usragpCreatedBy']);
	}

	public function getUpdatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'usragpUpdatedBy']);
	}

	public function getRemovedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'usragpRemovedBy']);
	}

  public function getUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'usragpUserID']);
	}
  public function getAccessGroup() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\AccessGroupModel';
		else
			$className = '\shopack\aaa\frontend\common\models\AccessGroupModel';

		return $this->hasOne($className, ['ugpID' => 'usragpAccessGroupID']);
	}

}
