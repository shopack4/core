<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\data\ActiveDataProvider;
use shopack\base\backend\controller\BaseRestController;
use shopack\base\backend\helpers\PrivHelper;
use shopack\aaa\backend\models\ApprovalRequestModel;

class ApprovalRequestController extends BaseRestController
{
	public function behaviors()
	{
		$behaviors = parent::behaviors();
		return $behaviors;
	}

	protected function findModel($id)
	{
		if (($model = ApprovalRequestModel::findOne($id)) !== null)
			return $model;

		throw new NotFoundHttpException('The requested item does not exist.');
	}

	public function actionIndex()
	{
		$filter = $this->checkPrivAndGetFilter('aaa/approval-request/crud', '0100', 'aprUserID');

		$searchModel = new ApprovalRequestModel;
		$query = ApprovalRequestModel::find()
			// ->select(ApprovalRequestModel::selectableColumns())
			->with('createdByUser')
			->with('updatedByUser')
			->with('removedByUser')
		;

		$searchModel->fillQueryFromRequest($query);

		if (empty($filter) == false)
			$query->andWhere($filter);

		return $this->queryAllToResponse($query);
	}

	public function actionView($id)
	{
		if (PrivHelper::hasPriv('aaa/approval-request/crud', '0100') == false) {
			if (Yii::$app->user->id != $id)
				throw new ForbiddenHttpException('access denied');
		}

		$query = ApprovalRequestModel::find()
			// ->select(ApprovalRequestModel::selectableColumns())
			->with('createdByUser')
			->with('updatedByUser')
			->with('removedByUser')
			->where(['aprUserID' => $id])
		;

		return $this->queryOneToResponse($query);
	}

	public function actionOptions()
	{
		return 'options';
	}

}
