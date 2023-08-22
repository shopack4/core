<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\data\ActiveDataProvider;
use shopack\base\common\helpers\ExceptionHelper;
use shopack\base\backend\controller\BaseRestController;
use shopack\base\backend\helpers\PrivHelper;
use shopack\aaa\common\enums\enuPaymentGatewayType;
use shopack\aaa\backend\models\OfflinePaymentModel;
use shopack\aaa\backend\models\GatewayModel;
use shopack\aaa\common\enums\enuGatewayStatus;
use shopack\base\common\helpers\ArrayHelper;
use shopack\aaa\common\enums\enuVoucherType;

class OfflinePaymentController extends BaseRestController
{
	public function behaviors()
	{
		$behaviors = parent::behaviors();

		return $behaviors;
	}

	protected function findModel($id)
	{
		if (($model = OfflinePaymentModel::findOne($id)) !== null)
			return $model;

		throw new NotFoundHttpException('The requested item not exist.');
	}

	public function actionIndex()
	{
		$filter = $this->checkPrivAndGetFilter('aaa/offline-payment/crud', '0100', 'ofpOwnerUserID');

		$searchModel = new OfflinePaymentModel;
		$query = $searchModel::find()
			->select(OfflinePaymentModel::selectableColumns())
			->joinWith('owner')
			->joinWith('voucher')
			->joinWith('imageFile')
			->joinWith('wallet')
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
		$justForMe = false;
		if (PrivHelper::hasPriv('aaa/offline-payment/crud', '0100') == false) {
			$justForMe = true;
		}

		$model = OfflinePaymentModel::find()
			->select(OfflinePaymentModel::selectableColumns())
			->joinWith('owner')
			->joinWith('voucher')
			->joinWith('imageFile')
			->joinWith('wallet')
			->with('createdByUser')
			->with('updatedByUser')
			->with('removedByUser')
			->where(['ofpID' => $id])
			->asArray()
			->one()
		;

		if ($model !== null) {
			if ($justForMe && ($model['ofpOwnerUserID'] != Yii::$app->user->id))
				throw new ForbiddenHttpException('access denied');

			return $model;
		}

		throw new NotFoundHttpException('The requested item not exist.');

		// return $this->modelToResponse($model);
	}

	public function actionCreate()
	{
		$justForMe = false;
		if (PrivHelper::hasPriv('aaa/offline-payment/crud', '1000') == false) {
			$justForMe = true;
		}

		$model = new OfflinePaymentModel();
		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		if ($justForMe && ($model->ofpOwnerUserID != Yii::$app->user->id))
			throw new ForbiddenHttpException('access denied');

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
				'ofpID' => $model->ofpID,
				'ofpImageFileID' => $model->ofpImageFileID,
				'ofpStatus' => $model->ofpStatus,
				'ofpCreatedAt' => $model->ofpCreatedAt,
				'ofpCreatedBy' => $model->ofpCreatedBy,
			// ],
		];
	}

	public function actionUpdate($id)
	{
		$justForMe = false;
		if (PrivHelper::hasPriv('aaa/offline-payment/crud', '0010') == false) {
			$justForMe = true;
		}

		$model = $this->findModel($id);
		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		if ($justForMe && ($model->ofpOwnerUserID != Yii::$app->user->id))
			throw new ForbiddenHttpException('access denied');

		if ($model->save() == false)
			throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

		return [
			// 'result' => [
				// 'message' => 'updated',
				'ofpID' => $model->ofpID,
				'ofpStatus' => $model->ofpStatus,
				'ofpUpdatedAt' => $model->ofpUpdatedAt,
				'ofpUpdatedBy' => $model->ofpUpdatedBy,
			// ],
		];
	}

	public function actionDelete($id)
	{
		$justForMe = false;
		if (PrivHelper::hasPriv('aaa/offline-payment/crud', '0001') == false) {
			$justForMe = true;
		}

		$model = $this->findModel($id);

		if ($justForMe && ($model->ofpOwnerUserID != Yii::$app->user->id))
			throw new ForbiddenHttpException('access denied');

		if ($model->delete() === false)
			throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

		return [
			// 'result' => [
				// 'message' => 'deleted',
				'ofpID' => $model->ofpID,
				'ofpStatus' => $model->ofpStatus,
				'ofpRemovedAt' => $model->ofpRemovedAt,
				'ofpRemovedBy' => $model->ofpRemovedBy,
			// ],
		];
	}

	public function actionOptions()
	{
		return 'options';
	}

	public function actionAccept($id)
	{
		PrivHelper::checkPriv('aaa/offline-payment/accept');

		$model = $this->findModel($id);
		$model->doAccept();

		return [
			'result' => true,
		];
	}

	public function actionReject($id)
	{
		PrivHelper::checkPriv('aaa/offline-payment/reject');

		$model = $this->findModel($id);
		$model->doReject();

		return [
			'result' => true,
		];
	}

}
