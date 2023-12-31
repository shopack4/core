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
use shopack\aaa\backend\models\BasketItemForm;
use shopack\aaa\backend\models\BasketCheckoutForm;
use shopack\aaa\backend\models\VoucherModel;
use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\aaa\common\enums\enuVoucherType;
use shopack\base\common\helpers\Json;
use shopack\base\common\security\RsaPublic;

// use shopack\base\backend\models\BasketModel;

class BasketController extends BaseRestController
{
	public function getSecureData()
	{
		// $allData = array_merge($_GET, $_POST);
		$allData = $_POST;

		$service = $allData['service'];
		if (empty($service))
			throw new UnprocessableEntityHttpException('NOT_PROVIDED:Service');

		$data = $allData['data'];
		if (empty($data))
			throw new UnprocessableEntityHttpException('NOT_PROVIDED:Data');

		$module = Yii::$app->controller->module;

		$key = $module->servicesPublicKeys[$service];
		$rsaModel = RsaPublic::model($key);
		$data = $rsaModel->decrypt($data);

		$data = Json::decode($data);

		if ($service != $data['service']) //todo: change to sanity check
			throw new ForbiddenHttpException('INVALID:Service');

		return $data;
	}

	public function actionGetCurrent()
	{
		$data = $this->getSecureData();

		$userid = $data['userid'];
		if ($userid != Yii::$app->user->id)
			throw new ForbiddenHttpException('Access denied');

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
		}

		return $model;
	}

	public function actionSetCurrent()
	{










	}

	//just called from other services with encryption
	public function actionAddItem()
	{
		return BasketItemForm::addItem();
	}

	public function actionRemoveItem($key)
	{
		return BasketItemForm::removeItem($key);
	}

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
