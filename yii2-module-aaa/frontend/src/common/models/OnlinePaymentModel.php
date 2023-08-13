<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\frontend\rest\RestClientActiveRecord;
use shopack\aaa\common\enums\enuOnlinePaymentStatus;
use shopack\aaa\common\enums\enuPaymentGatewayType;

class OnlinePaymentModel extends RestClientActiveRecord
{
	use \shopack\aaa\common\models\OnlinePaymentModelTrait;

	public static $resourceName = 'aaa/online-payment';
  public static $primaryKey = ['onpID'];

	public function attributeLabels()
	{
		return [
			'onpID'               => Yii::t('app', 'ID'),
			'onpUUID'             => Yii::t('app', 'Key'),
			'onpGatewayID'        => Yii::t('aaa', 'Gateway'),
			'onpVoucherID'        => Yii::t('aaa', 'Voucher'),
			'onpAmount'           => Yii::t('aaa', 'Amount'),
			'onpCallbackUrl'      => Yii::t('aaa', 'Callback Url'),
			'onpWalletID'         => Yii::t('aaa', 'Wallet'),
			'onpTrackNumber'      => Yii::t('aaa', 'Track Number'),
			'onpRRN'      				=> Yii::t('aaa', 'RRN'),
			'onpStatus'           => Yii::t('app', 'Status'),
			'onpCreatedAt'        => Yii::t('app', 'Created At'),
			'onpCreatedBy'        => Yii::t('app', 'Created By'),
			'onpCreatedBy_User'   => Yii::t('app', 'Created By'),
			'onpUpdatedAt'        => Yii::t('app', 'Updated At'),
			'onpUpdatedBy'        => Yii::t('app', 'Updated By'),
			'onpUpdatedBy_User'   => Yii::t('app', 'Updated By'),
			'onpRemovedAt'        => Yii::t('app', 'Removed At'),
			'onpRemovedBy'        => Yii::t('app', 'Removed By'),
			'onpRemovedBy_User'   => Yii::t('app', 'Removed By'),
		];
	}

	public function isSoftDeleted()
  {
    return ($this->onpStatus == enuOnlinePaymentStatus::Removed);
  }

	public static function canCreate() {
		return true;
	}

	public function canUpdate() {
		return ($this->onpStatus != enuOnlinePaymentStatus::Removed);
	}

	public function canDelete() {
		return ($this->onpStatus != enuOnlinePaymentStatus::Removed);
	}

	public function canUndelete() {
		return ($this->onpStatus == enuOnlinePaymentStatus::Removed);
	}

	public static function getAllowedTypes()
	{
		list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/online-payment/get-allowed-types',
			HttpHelper::METHOD_GET,
			[
				// 'service' => $service,
				// 'domain' =>
			]
		);

		if ($resultStatus < 200 || $resultStatus >= 300)
			throw new \yii\web\HttpException($resultStatus, Yii::t('mha', $resultData['message'], $resultData));

		$types = [];

		foreach ($resultData as $t) {
			$types[$t] = enuPaymentGatewayType::getLabel($t);
		}

		return $types;
	}

}
