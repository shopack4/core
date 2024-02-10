<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\accounting\controllers;

use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use shopack\base\common\helpers\ExceptionHelper;
use shopack\base\backend\controller\BaseRestController;
use shopack\base\common\helpers\Json;
use shopack\base\common\security\RsaPrivate;

//basket = Voucher[Type=Basket & Status=New]
abstract class BaseAccountingController extends BaseRestController
{
	public function behaviors()
	{
		$behaviors = parent::behaviors();

		// $behaviors[BaseRestController::BEHAVIOR_AUTHENTICATOR]['except'] = [
		// 	'callback',
		// ];

		return $behaviors;
	}

	public function actionOptions()
	{
		return 'options';
	}

	private static $_accountingModule = null;
	public static function getAccountingModule()
	{
		if (self::$_accountingModule == null) {
			self::$_accountingModule = Yii::$app->controller->module;
			if (self::$_accountingModule->id != 'accounting')
				self::$_accountingModule = self::$_accountingModule->accounting;
		}

		return self::$_accountingModule;
	}

	/**
	 * add an item into prevoucher
	 */
	public function actionAddToBasket()
	{
		$accountingModule = self::getAccountingModule();
		$modelClass = $accountingModule->basketModelClass;
		$model = new $modelClass;
		// $model = new BasketModel();
		// $model->scenario = enuModelScenario::CREATE;

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		try {
			if ($model->addToBasket() == false)
				throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));
		} catch(\Exception $exp) {
			$msg = ExceptionHelper::CheckDuplicate($exp, $model);
			throw new UnprocessableEntityHttpException($msg);
		}

		return [
			// 'result' => [
				// 'message' => 'created',
				'prevoucher' => $model->getPrevoucher(),
			// ],
		];
	}

	/**
	 * update a prevoucher item
	 */
	public function actionUpdateBasketItem($key)
	{
		$accountingModule = self::getAccountingModule();
		$modelClass = $accountingModule->basketModelClass;
		$model = new $modelClass;

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		try {
			if ($model->updateBasketItem() == false)
				throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));
		} catch(\Exception $exp) {
			$msg = ExceptionHelper::CheckDuplicate($exp, $model);
			throw new UnprocessableEntityHttpException($msg);
		}

		return [
			// 'result' => [
				// 'message' => 'created',
				'prevoucher' => $model->getPrevoucher(),
			// ],
		];
	}

	/**
	 * remove an item from prevoucher
	 */
	public function actionRemoveBasketItem($key)
	{
		$accountingModule = self::getAccountingModule();
		$modelClass = $accountingModule->basketModelClass;
		$model = new $modelClass;

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		try {
			if ($model->removeBasketItem() == false)
				throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));
		} catch(\Exception $exp) {
			$msg = ExceptionHelper::CheckDuplicate($exp, $model);
			throw new UnprocessableEntityHttpException($msg);
		}

		return [
			// 'result' => [
				// 'message' => 'created',
				'prevoucher' => $model->getPrevoucher(),
			// ],
		];
	}

	public function getSecureData()
	{
		$allData = $_POST;

		$service = $allData['service'];
		if (empty($service))
			throw new UnprocessableEntityHttpException('NOT_PROVIDED:Service');

		$parentModule = Yii::$app->topModule;

		if ($service != $parentModule->id)
			throw new ForbiddenHttpException('INVALID:Service.id');

		$data = $allData['data'];
		if (empty($data))
			throw new UnprocessableEntityHttpException('NOT_PROVIDED:Data');

		$data = RsaPrivate::model($parentModule->servicePrivateKey)->decrypt($data);
		$data = Json::decode($data);

		if ($service != $data['service']) //todo: change to sanity check
			throw new ForbiddenHttpException('INVALID:Service');

		return $data;
	}

	/**
	 * recheck basket item(s) before check out
	 * called by /aaa/basket/get-current($recheckItems = true)
	 * @note: MUST BE CALL IN SECURE CHANNEL
	 */
	public function actionRecheckBasketItems()
	{
		$data = $this->getSecureData();
		$lastPrevoucher = $data['prevoucher'];
		$voucherItems = $data['items'];

		$accountingModule = self::getAccountingModule();
		$modelClass = $accountingModule->basketModelClass;

		return $modelClass::recheckBasketItems($lastPrevoucher, $voucherItems);
	}

}
