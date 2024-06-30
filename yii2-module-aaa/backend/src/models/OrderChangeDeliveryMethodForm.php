<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\models;

use Yii;
use yii\base\Model;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\UnprocessableEntityHttpException;
use shopack\base\common\helpers\HttpHelper;
use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\aaa\common\enums\enuVoucherType;

class OrderChangeDeliveryMethodForm extends Model
{
	public $vchID;
	public $deliveryMethod;

	public function rules()
	{
		return [
			[[
				'vchID',
				'deliveryMethod',
			], 'required'],
		];
	}

	public function process()
	{
		if (Yii::$app->user->isGuest)
			throw new UnauthorizedHttpException("This process is not for guest.");

		if ($this->validate() == false)
			throw new UnauthorizedHttpException(implode("\n", $this->getFirstErrors()));

		$voucherModel = VoucherModel::find()
			->andWhere(['vchID' => $this->vchID])
			->andWhere(['vchType' => enuVoucherType::Invoice])
			->one();

		if ($voucherModel == null)
			throw new NotFoundHttpException('The requested item does not exist.');

		if ($voucherModel->vchStatus != enuVoucherStatus::WaitForPayment)
			throw new UnauthorizedHttpException('وضعیت سفارش باید منتظر پرداخت باشد.');

		$deliveryMethodModel = DeliveryMethodModel::findOne($this->deliveryMethod);
		if ($deliveryMethodModel === null)
			throw new NotFoundHttpException('Selected delivery method not found.');

		/*
			      | delivery |  delta |  total | total | remained |
			 case |   Amount | Amount | Amount |  Paid |   Amount | action
			------|----------|--------|--------|-------|----------|----------------
			1.old |       10 |        |    100 |    80 |          |
			1.new |       20 |    +10 |    110 |    80 |          |
			------|----------|--------|--------|-------|----------|----------------
			2.old |       10 |        |    100 |    80 |          |
			2.new |       10 |      0 |    100 |    80 |          |
			------|----------|--------|--------|-------|----------|----------------
			3.old |       10 |        |    100 |    80 |          |
			3.new |        0 |    -10 |     90 |    80 |       10 |
			------|----------|--------|--------|-------|----------|----------------
			4.old |       10 |        |    100 |    90 |          |
			4.new |        0 |    -10 |     90 |    90 |        0 | finalize
			------|----------|--------|--------|-------|----------|----------------
			5.old |       40 |        |    100 |     0 |          |
			5.new |        0 |    -40 |     60 |     0 |      100 |
			------|----------|--------|--------|-------|----------|----------------
			6.old |       40 |        |    100 |    10 |          |
			6.new |        0 |    -40 |     60 |    10 |       50 |
			------|----------|--------|--------|-------|----------|----------------
			7.old |       40 |        |    100 |    90 |          |   returnToTheWallet (30)
			7.new |        0 |    -40 |     60 |    60 |      -30 | & finalize
			------|----------|--------|--------|-------|----------|----------------
		*/

		//new dlv amount - current dlv amount
		$deltaAmount = ($deliveryMethodModel->dlvAmount ?? 0) - ($voucherModel->vchDeliveryAmount ?? 0);

		//c2
		if ($deltaAmount == 0) { //no amount change
			//no method and amount change
			if ($voucherModel->vchDeliveryMethodID == $this->deliveryMethod)
				return true;
		}

		$voucherModel->vchDeliveryMethodID = $this->deliveryMethod;
		$voucherModel->vchDeliveryAmount = $deliveryMethodModel->dlvAmount;
		$voucherModel->vchTotalAmount += $deltaAmount;

		//c1,2
		if ($deltaAmount >= 0) {
			if ($voucherModel->save() == false)
				throw new UnprocessableEntityHttpException(implode("\n", $voucherModel->getFirstErrors()));

			return true;
		}

		//c3,4,5,6,7
		// $deltaAmount < 0 :
		// $deltaAmount = (-1) * $deltaAmount;

		$vchTotalPaid = ($voucherModel->vchTotalPaid ?? 0);
		$voucherRemainedAmount = $voucherModel->vchTotalAmount - $vchTotalPaid;

		//c3,4,5,6
		if ($voucherRemainedAmount >= 0) {

			//c4 : change status for finalize
			if ($voucherRemainedAmount == 0)
				$voucherModel->vchStatus = enuVoucherStatus::Settled;

			if ($voucherModel->save() == false)
				throw new UnprocessableEntityHttpException(implode("\n", $voucherModel->getFirstErrors()));

			//c4 : finalize
			if ($voucherRemainedAmount == 0)
				$voucherModel->processVoucher();

			return true;
		}

		//c7
		// $voucherRemainedAmount < 0 :
		$voucherRemainedAmount = abs($voucherRemainedAmount);

		$voucherModel->vchTotalPaid = $vchTotalPaid - $voucherRemainedAmount;

		$transaction = Yii::$app->db->beginTransaction();

		try {
			//return to the wallet
			WalletModel::returnToTheWallet(
				$voucherRemainedAmount,
				$voucherModel,
				// $walletModel->walID
			);

			//vchReturnToWallet
			$voucherModel->vchReturnToWallet = ($voucherModel->vchReturnToWallet ?? 0) + $voucherRemainedAmount;

			//settle
			$voucherModel->vchStatus = enuVoucherStatus::Settled;

			if ($voucherModel->save() == false)
				throw new UnprocessableEntityHttpException(implode("\n", $voucherModel->getFirstErrors()));

			$transaction->commit();
    } catch (\Throwable $exp) {
      $transaction->rollBack();
      throw $exp;
    }

		//finalize
		$voucherModel->processVoucher();

    return true;
	}

}
