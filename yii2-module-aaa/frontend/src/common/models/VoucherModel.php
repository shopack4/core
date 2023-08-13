<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use shopack\base\frontend\rest\RestClientActiveRecord;
use shopack\aaa\common\enums\enuVoucherStatus;

class VoucherModel extends RestClientActiveRecord
{
	use \shopack\aaa\common\models\VoucherModelTrait;

	public static $resourceName = 'aaa/voucher';
  public static $primaryKey = ['vchID'];

	public function attributeLabels()
	{
		return [
			'vchID'               => Yii::t('app', 'ID'),
			'vchOwnerUserID'      => Yii::t('aaa', 'Owner'),
			'vchType'      				=> Yii::t('aaa', 'Type'),
			'vchAmount'           => Yii::t('aaa', 'Amount'),
			'vchPaidByWallet'     => Yii::t('aaa', 'Paid By Wallet'),
			'vchOnlinePaid'       => Yii::t('aaa', 'Online Paid'),
			'vchOfflinePaid'      => Yii::t('aaa', 'Offline Paid'),
			'vchTotalPaid'        => Yii::t('aaa', 'Total Paid'),
			'vchStatus'           => Yii::t('app', 'Status'),
			'vchCreatedAt'        => Yii::t('app', 'Created At'),
			'vchCreatedBy'        => Yii::t('app', 'Created By'),
			'vchCreatedBy_User'   => Yii::t('app', 'Created By'),
			'vchUpdatedAt'        => Yii::t('app', 'Updated At'),
			'vchUpdatedBy'        => Yii::t('app', 'Updated By'),
			'vchUpdatedBy_User'   => Yii::t('app', 'Updated By'),
			'vchRemovedAt'        => Yii::t('app', 'Removed At'),
			'vchRemovedBy'        => Yii::t('app', 'Removed By'),
			'vchRemovedBy_User'   => Yii::t('app', 'Removed By'),
		];
	}

	public function isSoftDeleted()
  {
    return ($this->vchStatus == enuVoucherStatus::Removed);
  }

	public static function canCreate() {
		return true;
	}

	public function canUpdate() {
		return ($this->vchStatus != enuVoucherStatus::Removed);
	}

	public function canDelete() {
		return ($this->vchStatus != enuVoucherStatus::Removed);
	}

	public function canUndelete() {
		return ($this->vchStatus == enuVoucherStatus::Removed);
	}

}
