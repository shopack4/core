<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\accounting\controllers;

use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\data\ActiveDataProvider;
use shopack\base\common\helpers\ExceptionHelper;
use shopack\base\backend\controller\BaseRestController;
use shopack\base\backend\helpers\PrivHelper;
use shopack\base\backend\models\BasketModel;
// use shopack\aaa\common\enums\enuVoucherType;
use shopack\base\common\enums\enuModelScenario;

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

	/**
	 * add an item into prevoucher
	 */
	public function actionAddToBasket()
	{
		// PrivHelper::checkPriv(['aaa/basket/crud' => '1000']);

		$model = new BasketModel();
		$model->scenario = enuModelScenario::CREATE;

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		try {
			if ($model->save() == false)
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
	}

	/**
	 * remove an item from prevoucher
	 */
	public function actionRemoveBasketItem($key)
	{
	}

	/******************************************************************\
	|** internals *****************************************************|
	\******************************************************************/


}
