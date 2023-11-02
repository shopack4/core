<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use shopack\base\frontend\common\rest\RestClientActiveRecord;
// use shopack\aaa\common\enums\enuUserAccessGroupStatus;

class UserAccessGroupModel extends RestClientActiveRecord
{
	use \shopack\aaa\common\models\UserAccessGroupModelTrait;

	public static $resourceName = 'aaa/user-access-group';

	public function attributeLabels()
	{
		return [
			'usragpID'               => Yii::t('app', 'ID'),
			'usragpUserID'           => Yii::t('aaa', 'User'),
			'usragpAccessGroupID'    => Yii::t('aaa', 'Access Group'),
			'usragpStartAt'          => Yii::t('aaa', 'Start At'),
			'usragpEndAt'            => Yii::t('aaa', 'End At'),
			'usragpCreatedAt'        => Yii::t('app', 'Created At'),
			'usragpCreatedBy'        => Yii::t('app', 'Created By'),
			'usragpCreatedBy_User'   => Yii::t('app', 'Created By'),
			'usragpUpdatedAt'        => Yii::t('app', 'Updated At'),
			'usragpUpdatedBy'        => Yii::t('app', 'Updated By'),
			'usragpUpdatedBy_User'   => Yii::t('app', 'Updated By'),
			'usragpRemovedAt'        => Yii::t('app', 'Removed At'),
			'usragpRemovedBy'        => Yii::t('app', 'Removed By'),
			'usragpRemovedBy_User'   => Yii::t('app', 'Removed By'),
		];
	}

	public function isSoftDeleted()
  {
    return false; //($this->usragpStatus == enuUserAccessGroupStatus::Removed);
  }

	public static function canCreate() {
		return true;
	}

	public function canUpdate() {
		return true; //($this->usragpStatus != enuUserAccessGroupStatus::Removed);
	}

	public function canDelete() {
		return true; //($this->usragpStatus != enuUserAccessGroupStatus::Removed);
	}

	public function canUndelete() {
		return false; //($this->usragpStatus == enuUserAccessGroupStatus::Removed);
	}

}
