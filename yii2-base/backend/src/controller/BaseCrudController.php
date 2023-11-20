<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\controller;

use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use shopack\base\common\helpers\ExceptionHelper;
use shopack\base\backend\helpers\PrivHelper;
use shopack\base\common\helpers\ArrayHelper;

abstract class BaseCrudController extends BaseRestController
{
	public $modelClass;

	public function init()
	{
		parent::init();

		if ($this->modelClass === null)
			throw new InvalidConfigException('The "modelClass" property must be set.');
	}

	protected function findModel($id)
	{
		$modelClass = $this->modelClass;
		if (($model = $modelClass::findOne($id)) === null)
			throw new NotFoundHttpException('The requested item not exist.');

		return $model;
	}

	public function actionOptions()
	{
		return 'options';
	}

	abstract public function permissions();

	public function checkPermission($model = null, $query = null)
	{
		$permissions = $this->permissions();

		if (empty($permissions[$this->action->id]))
			return;

		$permissions = $permissions[$this->action->id];
		$filter = ArrayHelper::remove($permissions, 'filter', null);
		$checker = ArrayHelper::remove($permissions, 'checker', null);

		if (empty($checker)) {
			if (empty($filter) || ($query == null))
				PrivHelper::checkPriv($permissions);
			else {
				$justForMe = $_GET['justForMe'] ?? false;

				if ($justForMe || (PrivHelper::hasPriv($permissions) == false)) {
					$filter($query);
				}
			}
		} else {
			if ((PrivHelper::hasPriv($permissions) == false)
				&& ($checker($model) == false)
			) {
				throw new ForbiddenHttpException('access denied');
			}
		}
	}

	public function queryAugmentaters()
	{
		return [];
	}

	public function augmentQuery($query)
	{
		$augmentaters = $this->queryAugmentaters();
		if (isset($augmentaters[$this->action->id])) {
			$augmentaters[$this->action->id]($query);
		}
	}

	public function fillGlobalSearchFromRequest(\yii\db\ActiveQuery $query, $q)
	{
	}

	public function actionIndex($q = null)
	{
		$modelClass = $this->modelClass;
		$model = new $modelClass;
		$query = $model::find()
			->select($modelClass::selectableColumns())
			->asArray()
		;

		$this->checkPermission($model, $query);

		$this->augmentQuery($query);

		$this->fillGlobalSearchFromRequest($query, $q);

		$model->fillQueryFromRequest($query);

		return $this->queryAllToResponse($query);
	}

	public function actionView($id)
	{
		$modelClass = $this->modelClass;
		$primaryKey = $modelClass::$primaryKey;
		$query = $modelClass::find()
			->select($modelClass::selectableColumns())
			// ->where(['docID' => $id])
			->andWhere([$primaryKey[0] => $id])
			->asArray()
		;

		$this->augmentQuery($query);

		$model = $query->one();

		$this->checkPermission($model);

		if ($model !== null)
			return $model;

		throw new NotFoundHttpException('The requested item not exist.');

		// return RESTfulHelper::modelToResponse($this->findModel($id));
	}

	public function actionCreate()
	{
		$modelClass = $this->modelClass;
		$model = new $modelClass();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		$this->checkPermission($model);

		try {
			if ($model->save() == false)
				throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

		} catch(\Exception $exp) {
			$msg = ExceptionHelper::CheckDuplicate($exp, $model);
			throw new UnprocessableEntityHttpException($msg);
		}

		return [
			implode(',', (Array)($modelClass::primaryKey())) => $model->primaryKeyValue(),
			// // 'result' => [
			// 	// 'message' => 'created',
			// 	'docID' => $model->docID,
			// 	'docStatus' => $model->docStatus,
			// 	'docCreatedAt' => $model->docCreatedAt,
			// 	'docCreatedBy' => $model->docCreatedBy,
			// // ],
		];
	}

	public function actionUpdate($id)
	{
		$model = $this->findModel($id);

		$this->checkPermission($model);

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		if ($model->save() == false)
			throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

		return [
			// // 'result' => [
			// 	// 'message' => 'updated',
			// 	'docID' => $model->docID,
			// 	'docStatus' => $model->docStatus,
			// 	'docUpdatedAt' => $model->docUpdatedAt,
			// 	'docUpdatedBy' => $model->docUpdatedBy,
			// // ],
		];
	}

	public function actionDelete($id)
	{
		$model = $this->findModel($id);

		$this->checkPermission($model);

		if ($model->delete() === false)
			throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

		return [
			// // 'result' => [
			// 	// 'message' => 'deleted',
			// 	'docID' => $model->docID,
			// 	'docStatus' => $model->docStatus,
			// 	'docRemovedAt' => $model->docRemovedAt,
			// 	'docRemovedBy' => $model->docRemovedBy,
			// // ],
		];
	}

}
