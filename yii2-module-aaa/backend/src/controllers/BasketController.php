<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use shopack\base\common\helpers\ExceptionHelper;
use shopack\base\backend\controller\BaseRestController;
use shopack\aaa\backend\models\BasketForm;
// use shopack\aaa\backend\models\BasketItemForm;
use shopack\aaa\backend\models\BasketCheckoutForm;
use shopack\aaa\backend\models\VoucherModel;
use shopack\aaa\backend\models\WalletModel;
use shopack\aaa\backend\models\WalletTransactionModel;
use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\aaa\common\enums\enuVoucherType;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\common\helpers\Json;
use shopack\base\common\security\RsaPrivate;
use shopack\base\common\security\RsaPublic;

// use shopack\base\backend\models\BasketModel;

class BasketController extends BaseRestController
{
	public function actionGetCurrent($recheckItems = false)
	{
		if (Yii::$app->user->isGuest)
			throw new ForbiddenHttpException('guest not allowed');

		// $recheckItems = $_POST['recheckItems'] ?? false;

		// $data = $this->getSecureData();

		// $userid = $data['userid'];
		// if ($userid != Yii::$app->user->id)
		// 	throw new ForbiddenHttpException('Access denied');

		$model = VoucherModel::find()
			->select(VoucherModel::selectableColumns())
			->andWhere(['vchOwnerUserID' => Yii::$app->user->id])
			->andWhere(['vchType' => enuVoucherType::Basket])
			->andWhere(['vchStatus' => enuVoucherStatus::New])
			->andWhere(['vchRemovedAt' => 0])
			->asArray()
			->one();

		if ($model == null) {
			$model = new VoucherModel();

			$model->vchOwnerUserID = Yii::$app->user->id;
			$model->vchType        = enuVoucherType::Basket;
			$model->vchAmount      = 0;
			$model->vchTotalAmount = 0;

			if ($model->save() == false) {
				throw new UnprocessableEntityHttpException('could not create new basket');
			}

			return $model;
		}

		if ($recheckItems) {
			$parentModule = Yii::$app->topModule;

			if (empty($model['vchItems']) == false) {
				if (is_string($model['vchItems'])) {
					$model['vchItems'] = Json::decode($model['vchItems']);
				}

				$services = [];

				//1- get services
				foreach ($model['vchItems'] as $item) {
					if (empty($services[$item['service']])) {
						$services[$item['service']] = [];
					}

					$services[$item['service']][] = $item;
				}

				//todo: TEMP:
				$_old_vchItems = $model['vchItems'];

				$model['vchItems'] = null;
				$newItems = [];

				//2: call recheck for every service
				foreach ($services as $service => $items) {
					$data = Json::encode([
						'service' => $service,
						'prevoucher' => $model,
						'items' => $items,
					]);

					$data = RsaPublic::model($parentModule->servicesPublicKeys[$service])->encrypt($data);

					list ($resultStatus, $resultData) = HttpHelper::callApi(
						"{$service}/accounting/recheck-basket-items",
						HttpHelper::METHOD_POST,
						[],
						[
							'service'	=> $service,
							'data' => $data,
						]
					);

					//todo: complete by return values from recheck-basket-items
					if ($resultStatus < 200 || $resultStatus >= 300) {
						// throw new \yii\web\HttpException($resultStatus, Yii::t('mha', $resultData['message'], $resultData));
					} else {

						//add to $newItems

					}
				}

				//3: add new items
				$model['vchItems'] = $newItems;

				//todo: TEMP:
				$model['vchItems'] = $_old_vchItems;

			}
		}

		return $model;
	}

	public function actionSetCurrent()
	{
		$data = $this->getSecureData();
		return VoucherModel::updateBasketOrOpenInvoice($data['service'], $data['voucher']);
	}

	//just called from other services with encryption
	// public function actionAddItem()
	// {
	// 	return BasketItemForm::addItem();
	// }

	// public function actionRemoveItem($key)
	// {
	// 	return BasketItemForm::removeItem($key);
	// }

	public function actionCheckout()
	{
		$model = new BasketCheckoutForm();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		try {
			$result = $model->checkout();
			if ($result == false)
				throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

			return $result;

		} catch(\Exception $exp) {
			$msg = ExceptionHelper::CheckDuplicate($exp, $model);
			throw new UnprocessableEntityHttpException($msg);
		}
	}

}
