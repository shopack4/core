<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\frontend\rest\RestClientActiveRecord;
use shopack\aaa\common\enums\enuWalletStatus;

class WalletModel extends RestClientActiveRecord
{
	use \shopack\aaa\common\models\WalletModelTrait;

	public static $resourceName = 'aaa/wallet';
  public static $primaryKey = ['walID'];

	public function attributeLabels()
	{
		return [
			'walID'               => Yii::t('app', 'ID'),
			'walOwnerUserID'      => Yii::t('aaa', 'Owner'),
			'walName'      				=> Yii::t('aaa', 'Name'),
			'walIsDefault' 				=> Yii::t('app', 'Default'),
			'walRemainedAmount'   => Yii::t('aaa', 'Remained Amount'),
			'walStatus'           => Yii::t('app', 'Status'),
			'walCreatedAt'        => Yii::t('app', 'Created At'),
			'walCreatedBy'        => Yii::t('app', 'Created By'),
			'walCreatedBy_User'   => Yii::t('app', 'Created By'),
			'walUpdatedAt'        => Yii::t('app', 'Updated At'),
			'walUpdatedBy'        => Yii::t('app', 'Updated By'),
			'walUpdatedBy_User'   => Yii::t('app', 'Updated By'),
			'walRemovedAt'        => Yii::t('app', 'Removed At'),
			'walRemovedBy'        => Yii::t('app', 'Removed By'),
			'walRemovedBy_User'   => Yii::t('app', 'Removed By'),
		];
	}

	public function isSoftDeleted()
  {
    return ($this->walStatus == enuWalletStatus::Removed);
  }

	public static function canCreate() {
		return true;
	}

	public function canUpdate() {
		return ($this->walStatus != enuWalletStatus::Removed);
	}

	public function canDelete() {
		return ($this->walStatus != enuWalletStatus::Removed);
	}

	public function canUndelete() {
		return ($this->walStatus == enuWalletStatus::Removed);
	}

	public static function ensureIHaveDefaultWallet()
	{
		if (Yii::$app->user->isGuest || empty($_GET['justForMe']))
			return false;

		try {
			list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/wallet/ensure-i-have-default-wallet', HttpHelper::METHOD_POST);

			return ($resultStatus >= 200 && $resultStatus < 300);

		} catch (\Throwable $th) {}

		return false;
	}

}
