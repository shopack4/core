<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use shopack\base\frontend\rest\RestClientActiveRecord;
// use shopack\aaa\common\enums\enuApprovalRequestStatus;

class ApprovalRequestModel extends RestClientActiveRecord
{
	use \shopack\aaa\common\models\ApprovalRequestModelTrait;

	public static $resourceName = 'aaa/approval-request';
  public static $primaryKey = ['aprID'];

	public function attributeLabels()
	{
		return [
			'aprID'               => Yii::t('app', 'ID'),
			'aprUserID'           => Yii::t('app', 'User'),
			'aprKeyType'          => Yii::t('app', 'Key Type'),
			'aprKey'              => Yii::t('app', 'Key'),
			'aprCode'             => Yii::t('app', 'Code'),
			'aprLastRequestAt'    => Yii::t('app', 'Last Request At'),
			'aprExpireAt'         => Yii::t('app', 'Expire At'),
			'aprSentAt'           => Yii::t('app', 'Sent At'),
			'aprApplyAt'          => Yii::t('app', 'Apply At'),
			'aprStatus'           => Yii::t('app', 'Status'),
			'aprCreatedAt'        => Yii::t('app', 'Created At'),
			'aprCreatedBy'        => Yii::t('app', 'Created By'),
			'aprCreatedBy_User'   => Yii::t('app', 'Created By'),
			'aprUpdatedAt'        => Yii::t('app', 'Updated At'),
			'aprUpdatedBy'        => Yii::t('app', 'Updated By'),
			'aprUpdatedBy_User'   => Yii::t('app', 'Updated By'),
			'aprRemovedAt'        => Yii::t('app', 'Removed At'),
			'aprRemovedBy'        => Yii::t('app', 'Removed By'),
			'aprRemovedBy_User'   => Yii::t('app', 'Removed By'),
		];
	}

	public function isSoftDeleted()
  {
    return false; //($this->aprStatus == enuApprovalRequestStatus::Removed);
  }

}
