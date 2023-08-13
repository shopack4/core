<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\frontend\rest\RestClientActiveRecord;
use shopack\aaa\common\enums\enuWalletTransactionStatus;

class WalletTransactionModel extends RestClientActiveRecord
{
	use \shopack\aaa\common\models\WalletTransactionModelTrait;

	public static $resourceName = 'aaa/wallet-transaction';
  public static $primaryKey = ['wtrID'];

	public function attributeLabels()
	{
		return [
			'wtrID'								=> Yii::t('app', 'ID'),
			'wtrWalletID'					=> Yii::t('aaa', 'Wallet'),
			'wtrVoucherID'				=> Yii::t('aaa', 'Voucher'),
			'wtrOnlinePaymentID'	=> Yii::t('aaa', 'Online Payment'),
			'wtrOfflinePaymentID'	=> Yii::t('aaa', 'Offline Payment'),
			'wtrAmount'						=> Yii::t('aaa', 'Amount'),
			'wtrStatus'						=> Yii::t('app', 'Status'),
			'wtrCreatedAt'        => Yii::t('app', 'Created At'),
			'wtrCreatedBy'        => Yii::t('app', 'Created By'),
			'wtrCreatedBy_User'   => Yii::t('app', 'Created By'),
			'wtrUpdatedAt'        => Yii::t('app', 'Updated At'),
			'wtrUpdatedBy'        => Yii::t('app', 'Updated By'),
			'wtrUpdatedBy_User'   => Yii::t('app', 'Updated By'),
			'wtrRemovedAt'        => Yii::t('app', 'Removed At'),
			'wtrRemovedBy'        => Yii::t('app', 'Removed By'),
			'wtrRemovedBy_User'   => Yii::t('app', 'Removed By'),
		];
	}

	public function isSoftDeleted()
  {
    return ($this->wtrStatus == enuWalletTransactionStatus::Removed);
  }

}
