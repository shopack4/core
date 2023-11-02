<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use shopack\base\frontend\common\rest\RestClientActiveRecord;
// use shopack\aaa\common\enums\enuAccessGroupStatus;

class AccessGroupModel extends RestClientActiveRecord
{
	use \shopack\aaa\common\models\AccessGroupModelTrait;

	public static $resourceName = 'aaa/access-group';

	public function attributeLabels()
	{
		return [
			'agpID'               => Yii::t('app', 'ID'),
			'agpName'             => Yii::t('app', 'Name'),
			'agpPrivs'            => Yii::t('app', 'Privs'),
			'agpStatus'           => Yii::t('app', 'Status'),
			'agpCreatedAt'        => Yii::t('app', 'Created At'),
			'agpCreatedBy'        => Yii::t('app', 'Created By'),
			'agpCreatedBy_User'   => Yii::t('app', 'Created By'),
			'agpUpdatedAt'        => Yii::t('app', 'Updated At'),
			'agpUpdatedBy'        => Yii::t('app', 'Updated By'),
			'agpUpdatedBy_User'   => Yii::t('app', 'Updated By'),
			'agpRemovedAt'        => Yii::t('app', 'Removed At'),
			'agpRemovedBy'        => Yii::t('app', 'Removed By'),
			'agpRemovedBy_User'   => Yii::t('app', 'Removed By'),
		];
	}

	public function isSoftDeleted()
  {
    return false; //($this->agpStatus == enuAccessGroupStatus::Removed);
  }

	public static function canCreate() {
		return true;
	}

	public function canUpdate() {
		return true; //($this->agpStatus != enuAccessGroupStatus::Removed);
	}

	public function canDelete() {
		return true; //($this->agpStatus != enuAccessGroupStatus::Removed);
	}

	public function canUndelete() {
		return false; //($this->agpStatus == enuAccessGroupStatus::Removed);
	}

}
