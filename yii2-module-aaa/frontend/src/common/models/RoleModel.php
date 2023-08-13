<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use shopack\base\frontend\rest\RestClientActiveRecord;
// use shopack\aaa\common\enums\enuRoleStatus;

class RoleModel extends RestClientActiveRecord
{
	use \shopack\aaa\common\models\RoleModelTrait;

	public static $resourceName = 'aaa/role';
  public static $primaryKey = ['rolID'];

	public function attributeLabels()
	{
		return [
			'rolID'               => Yii::t('app', 'ID'),
			'rolName'             => Yii::t('app', 'Name'),
			'rolParentID'         => Yii::t('app', 'Parent'),
			'rolPrivs'            => Yii::t('app', 'Privs'),
			'rolCreatedAt'        => Yii::t('app', 'Created At'),
			'rolCreatedBy'        => Yii::t('app', 'Created By'),
			'rolCreatedBy_User'   => Yii::t('app', 'Created By'),
			'rolUpdatedAt'        => Yii::t('app', 'Updated At'),
			'rolUpdatedBy'        => Yii::t('app', 'Updated By'),
			'rolUpdatedBy_User'   => Yii::t('app', 'Updated By'),
			'rolRemovedAt'        => Yii::t('app', 'Removed At'),
			'rolRemovedBy'        => Yii::t('app', 'Removed By'),
			'rolRemovedBy_User'   => Yii::t('app', 'Removed By'),
		];
	}

	public function isSoftDeleted()
  {
    return false; //($this->rolStatus == enuRoleStatus::Removed);
  }

	public static function canCreate() {
		return true;
	}

	public function canUpdate() {
		return true; //($this->rolStatus != enuRoleStatus::Removed);
	}

	public function canDelete() {
		return true; //($this->rolStatus != enuRoleStatus::Removed);
	}

	public function canUndelete() {
		return false; //($this->rolStatus == enuRoleStatus::Removed);
	}

}
