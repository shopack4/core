<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use shopack\base\frontend\common\rest\RestClientActiveRecord;

class SessionModel extends RestClientActiveRecord
{
	use \shopack\aaa\common\models\SessionModelTrait;

	public static $resourceName = 'aaa/session';

	public function attributeLabels()
	{
		return [
			'ssnID'							=> Yii::t('app', 'ID'),
			'ssnUserID'					=> Yii::t('app', 'User'),
			'ssnJWT'						=> Yii::t('app', 'JWT'),
			'ssnJWTMD5'					=> Yii::t('app', 'JWT MD5'),
			'ssnStatus'					=> Yii::t('app', 'Status'),
			'ssnExpireAt'				=> Yii::t('aaa', 'Expire At'),
			'ssnCreatedAt'			=> Yii::t('app', 'Created At'),
			'ssnCreatedBy'			=> Yii::t('app', 'Created By'),
			'ssnCreatedBy_User'	=> Yii::t('app', 'Created By'),
			'ssnUpdatedAt'			=> Yii::t('app', 'Updated At'),
			'ssnUpdatedBy'			=> Yii::t('app', 'Updated By'),
			'ssnUpdatedBy_User'	=> Yii::t('app', 'Updated By'),
			'ssnRemovedAt'			=> Yii::t('app', 'Removed At'),
			'ssnRemovedBy'			=> Yii::t('app', 'Removed By'),
			'ssnRemovedBy_User'	=> Yii::t('app', 'Removed By'),
		];
	}

	public function isSoftDeleted()
  {
    return false;
  }

	public static function canCreate() {
		return false;
	}

	public function canUpdate() {
		return false;
	}

	public function canDelete() {
		return false;
	}

	public function canUndelete() {
		return false;
	}

}
