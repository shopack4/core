<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use yii\web\NotFoundHttpException;
use shopack\base\backend\controller\BaseRestController;
use shopack\aaa\backend\models\SessionModel;
use shopack\base\backend\helpers\PrivHelper;

class SessionController extends BaseRestController
{
	public function behaviors()
	{
		$behaviors = parent::behaviors();
		return $behaviors;
	}

	protected function findModel($id)
	{
		if (($model = SessionModel::findOne($id)) !== null)
			return $model;

		throw new NotFoundHttpException('The requested item does not exist.');
	}

	public function actionIndex()
	{
		PrivHelper::checkPriv(['aaa/session/crud' => '0100']);

		$searchModel = new SessionModel;
		$query = $searchModel::find(true)
			->select(SessionModel::selectableColumns())
			->joinWith('user')
			->with('createdByUser')
			->with('updatedByUser')
			->with('removedByUser')
			->asArray()
		;

		$searchModel->fillQueryFromRequest($query);

		return $this->queryAllToResponse($query);
	}

}
