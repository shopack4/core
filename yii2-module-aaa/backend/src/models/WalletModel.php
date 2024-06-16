<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\models;

use Yii;
use yii\db\Expression;
use shopack\aaa\backend\classes\AAAActiveRecord;
use shopack\aaa\common\enums\enuVoucherType;
use shopack\aaa\common\enums\enuWalletStatus;
use yii\web\ServerErrorHttpException;

class WalletModel extends AAAActiveRecord
{
	use \shopack\aaa\common\models\WalletModelTrait;

  use \shopack\base\common\db\SoftDeleteActiveRecordTrait;
  public function initSoftDelete()
  {
    $this->softdelete_RemovedStatus  = enuWalletStatus::Removed;
    // $this->softdelete_StatusField    = 'walStatus';
    $this->softdelete_RemovedAtField = 'walRemovedAt';
    $this->softdelete_RemovedByField = 'walRemovedBy';
	}

	public static function tableName()
	{
		return '{{%AAA_Wallet}}';
	}

	public function behaviors()
	{
		return [
			[
				'class' => \shopack\base\common\behaviors\RowDatesAttributesBehavior::class,
				'createdAtAttribute' => 'walCreatedAt',
				'createdByAttribute' => 'walCreatedBy',
				'updatedAtAttribute' => 'walUpdatedAt',
				'updatedByAttribute' => 'walUpdatedBy',
			],
		];
	}

	public static function ensureIHaveDefaultWallet($userid = null)
	{
		if (empty($userid)) {
			$userid = Yii::$app->user->id;

			if (Yii::$app->user->isGuest) // || empty($_GET['justForMe']))
				return false;
		}

		//todo: replace logic with `INSERT IGNORE`

		$model = WalletModel::find()
			->andWhere(['walOwnerUserID' => $userid])
			->andWhere(['walIsDefault' => true])
			->andWhere(['!=', 'walStatus', enuWalletStatus::Removed])
			->one();

		if ($model == null) {
			$model = new WalletModel();

			$model->walOwnerUserID		= $userid;
			$model->walName						= 'Default';
			$model->walIsDefault			= true;
			$model->walRemainedAmount	= 0;
			$model->walStatus					= enuWalletStatus::Active;

			$model->save();
		}

		return $model;
	}

	public static function returnToTheWallet(
		$returnAmount,
		VoucherModel $originVoucherModel,
		$walID = null
	) {
		if (empty($walID)) {
			$walletModel = WalletModel::ensureIHaveDefaultWallet();
			$walID = $walletModel->walID;
		}

		if (Yii::$app->db->getTransaction() === null) {
			$transaction = Yii::$app->db->beginTransaction();
		}

		try {
			//create return voucher
			$voucherModel = new VoucherModel();
			$voucherModel->vchOriginVoucherID	= $originVoucherModel->vchID;
			$voucherModel->vchOwnerUserID			= $originVoucherModel->vchOwnerUserID;
			$voucherModel->vchType						= enuVoucherType::Credit;
			$voucherModel->vchAmount					=
				$voucherModel->vchTotalAmount		= $returnAmount;
      $voucherModel->vchItems       = [
        'inc-wallet-id' => $walID,
      ];
      if ($voucherModel->save() == false)
        throw new ServerErrorHttpException('It is not possible to create a return voucher');

			//create wallet transaction
			$walletTransactionModel = new WalletTransactionModel();
			$walletTransactionModel->wtrWalletID	= $walID;
			$walletTransactionModel->wtrVoucherID	= $voucherModel->vchID;
			$walletTransactionModel->wtrAmount		= $returnAmount;
			if ($walletTransactionModel->save() == false)
				throw new ServerErrorHttpException('It is not possible to create wallet transaction');

			//increase wallet amount
			$walletTableName = WalletModel::tableName();
			$qry =<<<SQL
  UPDATE {$walletTableName}
     SET walRemainedAmount = walRemainedAmount + {$returnAmount}
   WHERE walID = {$walID}
SQL;
			$rowsCount = Yii::$app->db->createCommand($qry)->execute();

			if ($rowsCount == 0)
				throw new ServerErrorHttpException('It is not possible to update wallet amount');

			if (isset($transaction))
				$transaction->commit();

    } catch (\Throwable $exp) {
			if (isset($transaction))
	      $transaction->rollBack();

      throw $exp;
    }
	}

}
