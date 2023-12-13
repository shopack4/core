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
		$service = $_POST['service'];
		if (empty($service))
			throw new UnprocessableEntityHttpException('NOT_PROVIDED:Service');

		$data = $_POST['data'];
		if (empty($data))
			throw new UnprocessableEntityHttpException('NOT_PROVIDED:Data');

		$module = Yii::$app->controller->module;
		$data = RsaPublic::model($module->servicesPublicKeys[$service])->decrypt($data);

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

		if ($model !== null)
			return $model;

		return []; //return empty basket instead of raising exception
		// throw new NotFoundHttpException('The requested item not exist.');
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
