<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\models;

use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\aaa\common\enums\enuVoucherType;
use Yii;
use yii\base\Model;
use shopack\base\common\helpers\HttpHelper;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\UnprocessableEntityHttpException;

class ChangeOrderDeliveryMethodForm extends Model
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

		//new dlv amount - current dlv amount
		$deltaAmount = ($deliveryMethodModel->dlvAmount ?? 0) - ($voucherModel->vchDeliveryAmount ?? 0);

		if ($deltaAmount == 0) { //no amount change
			//no method and amount change
			if ($voucherModel->vchDeliveryMethodID == $this->deliveryMethod)
				return true;

			$voucherModel->vchDeliveryMethodID = $this->deliveryMethod;
			// $voucherModel->vchDeliveryAmount = $deliveryMethodModel->dlvAmount;

			if ($voucherModel->save() == false)
				throw new UnprocessableEntityHttpException(implode("\n", $voucherModel->getFirstErrors()));

			return true;
		}

		$voucherModel->vchDeliveryMethodID = $this->deliveryMethod;
		$voucherModel->vchDeliveryAmount = $deliveryMethodModel->dlvAmount;
		$voucherModel->vchTotalAmount += $deltaAmount;

		if ($deltaAmount > 0) {
			if ($voucherModel->save() == false)
				throw new UnprocessableEntityHttpException(implode("\n", $voucherModel->getFirstErrors()));

			return true;
		}

		// $deltaAmount < 0 :
		$deltaAmount = (-1) * $deltaAmount;

		$voucherRemainAmount = $voucherModel->vchTotalAmount - ($voucherModel->vchTotalPaid ?? 0);

		if ($voucherRemainAmount >= 0) {
			if ($voucherModel->save() == false)
				throw new UnprocessableEntityHttpException(implode("\n", $voucherModel->getFirstErrors()));

			return true;
		}




		throw new UnprocessableEntityHttpException('not yet complete');

    return false;
	}

}
