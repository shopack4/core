<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\userpanel\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\web\UnprocessableEntityHttpException;
use shopack\base\common\helpers\Url;
use shopack\base\frontend\helpers\Html;
use shopack\aaa\frontend\common\auth\BaseController;
use shopack\aaa\frontend\common\models\VoucherModel;
use shopack\aaa\common\enums\enuVoucherType;
use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\aaa\frontend\common\enums\enuCheckoutStep;
use shopack\aaa\frontend\userpanel\models\BasketCheckoutForm;
use shopack\aaa\frontend\userpanel\models\BasketItemForm;
use shopack\aaa\frontend\common\models\OnlinePaymentModel;

class BasketController extends BaseController
{
	// public function init()
  // {
  //   parent::init();

  //   $viewPath = dirname(dirname(__FILE__))
  //     . DIRECTORY_SEPARATOR
  //     . 'views'
  //     . DIRECTORY_SEPARATOR
  //     . $this->id;

  //   $this->setViewPath($viewPath);
  // }

	public function getCurrentBasket()
	{
    $voucherModel = VoucherModel::find()
      ->andWhere(['vchOwnerUserID' => Yii::$app->user->id])
      ->andWhere(['vchType' => enuVoucherType::Basket])
      ->andWhere(['vchStatus' => enuVoucherStatus::New])
      ->andWhere(['vchRemovedAt' => 0])
      ->all();

		return $voucherModel[0] ?? null;
	}

  public function actionIndex()
	{
		$model = new BasketCheckoutForm;
		$model->setVoucher($this->getCurrentBasket());

		if (empty($model->voucher) || empty($model->voucher->vchItems)) {
			return $this->render('empty', [
				'model' => $model,
			]);
		}

		return $this->render('view', [
			'model' => $model,
		]);
	}

	// public function actionAddItem()
	// {
	// }

	// public function actionUpdateItem()
	// {
	// }

  public function actionRemoveItem($key)
  {
    if (empty($_POST['confirmed']))
      throw new BadRequestHttpException('دستور حذف باید تایید شده باشد');

		$model = new BasketItemForm;
		$model->itemkey = $key;
		$model->setIsNewRecord(false);

		$done = $model->delete();

    return $this->redirect('index');
  }

	public function actionCheckout()
	{
		$model = new BasketCheckoutForm;
		$model->setVoucher($this->getCurrentBasket());

		if (empty($model->voucher) || empty($model->voucher->vchItems)) {
			return $this->render('empty', [
				'model' => $model,
			]);
		}

		//free basket
		if ($model->total == 0) {
			try {
				$result = $model->checkout();

				if (isset($result['paymentUrl']))
					return $this->redirect($result['paymentUrl']);

				return $this->redirect(Url::to([
					'checkout-done',
					'voucher' => $model->voucher->vchID,
				]));

			} catch (\Throwable $th) {
				throw $th;
			}
		}

		//non-free basket
		$formPosted = $model->load(Yii::$app->request->post());

		if (empty($model->currentStep)) {
			return $this->render('payment', [
				'model' => $model,
			]);
		}

		if ($model->currentStep == BasketCheckoutForm::STEP_PAYMENT) {
			try {
				$result = $model->checkout();

				if (isset($result['paymentUrl']))
					return $this->redirect($result['paymentUrl']);

				return $this->redirect(Url::to([
					'checkout-done',
					'voucher' => $model->voucher->vchID,
				]));

			} catch (\Throwable $th) {
				$model->addError(null, $th->getMessage());
			}

			return $this->render('payment', [
				'model' => $model,
			]);
		}

		//if ($model->currentStep == BasketCheckoutForm::) {}


		/*
		if ($step == enuCheckoutStep::Final) {
			// $done = false;
			// if ($formPosted)
			// 	$done = $model->checkout();

			//if ok:
				//redirect to payment page
				//or show done for free basket

			//if error:
			return $this->render('checkout-final', [
				'model' => $model,
			]);

		} else {
			switch ($step) {
				case enuCheckoutStep::View:
					$viewName = 'checkout-view';
					break;

				case enuCheckoutStep::Payment:
					$viewName = 'checkout-payment';
					break;

				default:
					throw new BadRequestHttpException('invalid step');
			}

			return $this->render($viewName, [
				'model' => $model,
			]);
		}

		*/

	}

  public function actionCheckoutDone(
		$voucher = null,
		$paymentkey = null,
		$errors = null
	) {
		if (empty($voucher) == false) {
			$voucher = VoucherModel::findOne($voucher);
		} else if (empty($paymentkey) == false) {
			$onlinePaymentModels = OnlinePaymentModel::find()
				->andWhere(['onpUUID' => $paymentkey])
				->all();

			if (empty($onlinePaymentModels))
				throw new NotFoundHttpException('Payment not found');

			$onlinePaymentModel = $onlinePaymentModels[0];

			$voucher = $onlinePaymentModel->voucher;
		}

		if (empty($voucher))
			throw new NotFoundHttpException('Voucher not found');

    if ($voucher->vchType != enuVoucherType::Basket)
      throw new UnprocessableEntityHttpException('Incorrect Voucher type (not basket)');

    return $this->render('checkout-done', [
      'voucher' => $voucher,
			'errors' => $errors ? urldecode($errors) : null,
    ]);
  }

}
