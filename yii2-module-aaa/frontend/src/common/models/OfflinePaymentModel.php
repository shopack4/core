<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use yii\web\NotFoundHttpException;
use shopack\base\frontend\common\rest\RestClientActiveRecord;
use shopack\aaa\common\enums\enuOfflinePaymentStatus;
use shopack\base\common\helpers\HttpHelper;

class OfflinePaymentModel extends RestClientActiveRecord
{
	use \shopack\aaa\common\models\OfflinePaymentModelTrait;
	// use \shopack\base\common\models\UploadedFilesTrait;

	public static $resourceName = 'aaa/offline-payment';

	public function attributeLabels()
	{
		return [
			'ofpID'               => Yii::t('app', 'ID'),
			'ofpUUID'             => Yii::t('app', 'Key'),
			'ofpOwnerUserID'      => Yii::$app->getModule('aaa')->getGlobalOwnerUserLabel(),
			'ofpVoucherID'        => Yii::t('aaa', 'Voucher'),
			'ofpBankOrCart'       => Yii::t('aaa', 'Bank or Cart'),
			'ofpTrackNumber'      => Yii::t('aaa', 'Track Number'),
			'ofpReferenceNumber'	=> Yii::t('aaa', 'RRN'),
			'ofpAmount'           => Yii::t('aaa', 'Amount'),
			'ofpPayDate'        	=> Yii::t('aaa', 'Paid at'),
			'ofpPayer'        	  => Yii::t('aaa', 'Payer'),
			'ofpSourceCartNumber' => Yii::t('aaa', 'Source Cart Number'),
			'ofpImageFileID'      => Yii::t('aaa', 'Image'),
			'ofpWalletID'         => Yii::t('aaa', 'Wallet'),
			'ofpComment'          => Yii::t('aaa', 'Comment'),
			'ofpStatus'           => Yii::t('app', 'Status'),
			'ofpCreatedAt'        => Yii::t('app', 'Created At'),
			'ofpCreatedBy'        => Yii::t('app', 'Created By'),
			'ofpCreatedBy_User'   => Yii::t('app', 'Created By'),
			'ofpUpdatedAt'        => Yii::t('app', 'Updated At'),
			'ofpUpdatedBy'        => Yii::t('app', 'Updated By'),
			'ofpUpdatedBy_User'   => Yii::t('app', 'Updated By'),
			'ofpRemovedAt'        => Yii::t('app', 'Removed At'),
			'ofpRemovedBy'        => Yii::t('app', 'Removed By'),
			'ofpRemovedBy_User'   => Yii::t('app', 'Removed By'),
		];
	}

	public function isSoftDeleted()
  {
    return ($this->ofpStatus == enuOfflinePaymentStatus::Removed);
  }

	public static function canCreate() {
		return true;
	}

	public function canUpdate() {
		return ($this->ofpStatus != enuOfflinePaymentStatus::Removed);
	}

	public function canDelete() {
		return ($this->ofpStatus != enuOfflinePaymentStatus::Removed);
	}

	public function canUndelete() {
		return ($this->ofpStatus == enuOfflinePaymentStatus::Removed);
	}

	public function canAccept() {
		return ($this->ofpStatus == enuOfflinePaymentStatus::WaitForApprove);
	}
	public function canReject() {
		return ($this->ofpStatus == enuOfflinePaymentStatus::WaitForApprove);
	}

	public static function doAccept($id)
	{
		if (empty($id))
			throw new NotFoundHttpException('Invalid id');

    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/offline-payment/accept',
      HttpHelper::METHOD_POST,
      [
        'id' => $id,
      ]
    );

    if ($resultStatus < 200 || $resultStatus >= 300)
      throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));

    return true; //[$resultStatus, $resultData['result']];
	}

	public static function doReject($id) {
		if (empty($id))
			throw new NotFoundHttpException('Invalid id');

    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/offline-payment/reject',
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
