<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\models;

use Yii;
use yii\db\Expression;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use shopack\aaa\backend\classes\AAAActiveRecord;
use shopack\aaa\common\enums\enuOfflinePaymentStatus;
use shopack\aaa\backend\models\WalletModel;

class OfflinePaymentModel extends AAAActiveRecord
{
	use \shopack\aaa\common\models\OfflinePaymentModelTrait;

  use \shopack\base\common\db\SoftDeleteActiveRecordTrait;
  public function initSoftDelete()
  {
    $this->softdelete_RemovedStatus  = enuOfflinePaymentStatus::Removed;
    // $this->softdelete_StatusField    = 'ofpStatus';
    $this->softdelete_RemovedAtField = 'ofpRemovedAt';
    $this->softdelete_RemovedByField = 'ofpRemovedBy';
	}

	public static function tableName()
	{
		return '{{%AAA_OfflinePayment}}';
	}

	public function extraRules()
  {
    return [
      ['ofpWalletID', 'required'],
    ];
  }

	public function behaviors()
	{
		return [
			[
				'class' => \shopack\base\common\behaviors\RowDatesAttributesBehavior::class,
				'createdAtAttribute' => 'ofpCreatedAt',
				'createdByAttribute' => 'ofpCreatedBy',
				'updatedAtAttribute' => 'ofpUpdatedAt',
				'updatedByAttribute' => 'ofpUpdatedBy',
			],
		];
	}

	public function save($runValidation = true, $attributeNames = null)
	{
		if (empty($_FILES) == false) {
			$uploadResult = Yii::$app->fileManager->saveUploadedFiles($this->ofpOwnerUserID, 'offline-payment');

			if (empty($uploadResult))
				return false;

			foreach ($uploadResult as $k => $v) {
				$this->$k = $v['fileID'];
			}
		}

		if (empty($this->ofpWalletID)) {
			$walletModel = WalletModel::ensureIHaveDefaultWallet($this->ofpOwnerUserID);
      $this->ofpWalletID = $walletModel->walID;
		}

		// if ($this->validate() == false)
		// throw new UnprocessableEntityHttpException(implode("\n", $this->getFirstErrors()));

		$result = parent::save($runValidation, $attributeNames);

		return $result;
	}

	public function doAccept()
	{
		Yii::$app->paymentManager->approveOfflinePayment($this);
	}

	public function doReject()
	{
		Yii::$app->paymentManager->rejectOfflinePayment($this);
	}

}
