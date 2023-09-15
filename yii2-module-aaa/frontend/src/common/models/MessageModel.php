<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use shopack\base\frontend\rest\RestClientActiveRecord;
// use shopack\aaa\common\enums\enuMessageStatus;

class MessageModel extends RestClientActiveRecord
{
	use \shopack\aaa\common\models\MessageModelTrait;

	public static $resourceName = 'aaa/message';

	public function attributeLabels()
	{
		return [
			'msgID'                      => Yii::t('app', 'ID'),
			'msgUserID'                  => Yii::t('aaa', 'User'),
			'msgApprovalRequestID'       => Yii::t('aaa', 'Approval Request'),
			'msgForgotPasswordRequestID' => Yii::t('aaa', 'Forgot Password Request'),
			'msgTypeKey'                 => Yii::t('aaa', 'Type Key'),
			'msgTarget'                  => Yii::t('aaa', 'Target'),
			'msgInfo'                    => Yii::t('aaa', 'Info'),
			'msgIssuer'                  => Yii::t('aaa', 'Issuer'),
			'msgLockedAt'                => Yii::t('aaa', 'Locked At'),
			'msgLockedBy'                => Yii::t('aaa', 'Locked By'),
			'msgLastTryAt'               => Yii::t('aaa', 'Last Try At'),
			'msgSentAt'                  => Yii::t('aaa', 'Sent At'),
			'msgResult'                  => Yii::t('aaa', 'Result'),
			'msgStatus'                  => Yii::t('app', 'Status'),
			'msgCreatedAt'               => Yii::t('app', 'Created At'),
			'msgCreatedBy'               => Yii::t('app', 'Created By'),
			'msgCreatedBy_User'          => Yii::t('app', 'Created By'),
			'msgUpdatedAt'               => Yii::t('app', 'Updated At'),
			'msgUpdatedBy'               => Yii::t('app', 'Updated By'),
			'msgUpdatedBy_User'          => Yii::t('app', 'Updated By'),
			'msgRemovedAt'               => Yii::t('app', 'Removed At'),
			'msgRemovedBy'               => Yii::t('app', 'Removed By'),
			'msgRemovedBy_User'          => Yii::t('app', 'Removed By'),
		];
	}

	public function isSoftDeleted()
  {
    return false; //($this->msgStatus == enuMessageStatus::Removed);
  }

	public static function canCreate() {
		return true;
	}

	public function canUpdate() {
		return true; //($this->msgStatus != enuMessageStatus::Removed);
	}

	public function canDelete() {
		return true; //($this->msgStatus != enuMessageStatus::Removed);
	}

	public function canUndelete() {
		return false; //($this->msgStatus == enuMessageStatus::Removed);
	}

}
