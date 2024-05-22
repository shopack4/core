<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use shopack\aaa\backend\models\ChangeOrderDeliveryMethodForm;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\data\ActiveDataProvider;
use shopack\base\common\helpers\ExceptionHelper;
use shopack\base\backend\controller\BaseRestController;
use shopack\base\backend\helpers\PrivHelper;
use shopack\aaa\backend\models\VoucherModel;

class VoucherController extends BaseRestController
{
	public function behaviors()
	{
		$behaviors = parent::behaviors();

		$behaviors[BaseRestController::BEHAVIOR_AUTHENTICATOR]['except'] = [
			'process-voucher',
		];

		return $behaviors;
	}

	protected function findModel($id)
	{
		if (($model = VoucherModel::findOne($id)) !== null)
			return $model;

		throw new NotFoundHttpException('The requested item does not exist.');
	}

	public function actionOptions()
	{
		return 'options';
	}

	public function actionIndex()
	{
		$filter = $this->checkPrivAndGetFilter('aaa/voucher/crud', '0100', 'vchOwnerUserID');

		$searchModel = new VoucherModel;
		$query = $searchModel::find()
			->select(VoucherModel::selectableColumns())
			->joinWith('owner')
			->with('createdByUser')
			->with('updatedByUser')
			->with('removedByUser')
			->asArray()
		;

		$searchModel->fillQueryFromRequest($query);

		if (empty($filter) == false)
			$query->andWhere($filter);

		return $this->queryAllToResponse($query);
	}

	public function actionView($id)
	{
		$model = VoucherModel::find()
			->select(VoucherModel::selectableColumns())
			->joinWith('owner')
			->with('createdByUser')
			->with('updatedByUser')
			->with('removedByUser')
			->where(['vchID' => $id])
			->asArray()
			->one()
		;

		if ((PrivHelper::hasPriv('aaa/voucher/crud', '0100') == false)
			&& ($model != null)
			&& ($model['vchOwnerUserID'] != Yii::$app->user->id)
		) {
			throw new ForbiddenHttpException('access denied');
		}

		return $this->modelToResponse($model);
	}

	public function actionProcessVoucher($id)
	{
		$res = $this->findModel($id)->processVoucher();

		return [
			'result' => $res ? 'ok' : 'error',
		];
	}

	public function actionChangeOrderDeliveryMethod()
	{
		$model = new ChangeOrderDeliveryMethodForm();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		try {
			$result = $model->process();

			//convert errors to 422
			if ($result !== true)
				throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

			return $result;

		} catch(\Exception $exp) {
			$msg = ExceptionHelper::CheckDuplicate($exp, $model);
			throw new UnprocessableEntityHttpException($msg);
		}
	}

}
