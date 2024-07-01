<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use shopack\base\frontend\common\rest\RestClientActiveRecord;
use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\base\common\helpers\HttpHelper;
use yii\web\NotFoundHttpException;

class VoucherModel extends RestClientActiveRecord
{
	use \shopack\aaa\common\models\VoucherModelTrait;

	public static $resourceName = 'aaa/voucher';

	public function attributeLabels()
	{
		return [
			'vchID'               => Yii::t('app', 'ID'),
			'vchOriginVoucherID'  => Yii::t('aaa', 'Origin Voucher'),
			'vchOwnerUserID'      => Yii::$app->getModule('aaa')->getOwnerUserLabel(),
			'vchType'      				=> Yii::t('aaa', 'Type'),
			'vchAmount'           => Yii::t('aaa', 'Amount'),
			'vchItemsDiscounts'   => Yii::t('aaa', 'Discount Amount'),
			'vchItemsVATs'   			=> Yii::t('aaa', 'VAT Amount'),
			'vchDeliveryAmount'   => Yii::t('aaa', 'Delivery Amount'),
			'vchTotalAmount'      => Yii::t('aaa', 'Total Price'),
			'vchPaidByWallet'     => Yii::t('aaa', 'Paid By Wallet'),
			'vchOnlinePaid'       => Yii::t('aaa', 'Online Paid'),
			'vchOfflinePaid'      => Yii::t('aaa', 'Offline Paid'),
			'vchTotalPaid'        => Yii::t('aaa', 'Total Paid'),
			'vchReturnToWallet'   => Yii::t('aaa', 'Returned Amount'),
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

	public function canPay() {
		return in_array($this->vchStatus, [
			// enuVoucherStatus::New,
			enuVoucherStatus::WaitForPayment,
			// enuVoucherStatus::Settled,
			// enuVoucherStatus::Finished,
			// enuVoucherStatus::Error,
			// enuVoucherStatus::Removed,
		]);
	}

	public function canCancel() {
		return in_array($this->vchStatus, [
			enuVoucherStatus::New,
			enuVoucherStatus::WaitForPayment,
			// enuVoucherStatus::Settled,
			// enuVoucherStatus::Finished,
			// enuVoucherStatus::Error,
			// enuVoucherStatus::Removed,
		]);
	}

	public function canReprocess() {
	  return in_array($this->vchStatus, [
	    // enuVoucherStatus::New,
	    // enuVoucherStatus::WaitForPayment,
	    enuVoucherStatus::Settled,
	    // enuVoucherStatus::Finished,
	    enuVoucherStatus::Error,
	    // enuVoucherStatus::Removed,
	  ]);
	}

	public static function doCancel($id)
	{
		if (empty($id))
			throw new NotFoundHttpException('Invalid id');

    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/voucher/cancel',
      HttpHelper::METHOD_POST,
      [
        'id' => $id,
      ]
    );

    if ($resultStatus < 200 || $resultStatus >= 300)
      throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));

    return true; //[$resultStatus, $resultData['result']];
	}

	public static function doReprocess($id)
	{
		if (empty($id))
			throw new NotFoundHttpException('Invalid id');

    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/voucher/reprocess',
      HttpHelper::METHOD_POST,
      [
        'id' => $id,
      ]
    );

    if ($resultStatus < 200 || $resultStatus >= 300)
      throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));

    return true; //[$resultStatus, $resultData['result']];
	}

}
