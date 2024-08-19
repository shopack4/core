<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\controller;

use Yii;
use yii\base\InvalidConfigException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\web\ServerErrorHttpException;
use shopack\base\common\helpers\ArrayHelper;
use shopack\base\common\helpers\ExceptionHelper;
use shopack\base\backend\helpers\PrivHelper;

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
			throw new NotFoundHttpException('The requested item does not exist.');

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

		if (array_key_exists($this->action->id, $permissions) == false)
			return;

		$permissions = $permissions[$this->action->id];

		if ($permissions === false)
			throw new ForbiddenHttpException('access denied');

		if ($permissions === true)
			return;

		$behaviors = $this->behaviors();

		if ((empty($behaviors[static::BEHAVIOR_AUTHENTICATOR]['except']) == false)
			&& (in_array($this->action->id, $behaviors[static::BEHAVIOR_AUTHENTICATOR]['except']))
		) {
			return;
			// throw new ServerErrorHttpException("action ({$this->action->id}) is set as except for jwt checking, but exists in controller's permission checking");
		}

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
		if (empty($q) || ($q == '***'))
			return;

	}

	public function actionIndex($q = null, $i18ntranslate = true)
	{
		$modelClass = $this->modelClass;

		$query = $modelClass::find()
			->i18nTranslate($i18ntranslate)
		;

		if (empty($query->select))
			$query->select($modelClass::selectableColumns());

		$model = new $modelClass;
		$this->checkPermission($model, $query);

		$this->augmentQuery($query);

		$this->fillGlobalSearchFromRequest($query, $q);

		$model->fillQueryFromRequest($query);

		return $this->queryAllToResponse($query);
	}

	public function actionView($id, $i18ntranslate = false)
	{
		$modelClass = $this->modelClass;

		$query = $modelClass::find()
			->i18nTranslate($i18ntranslate)
		;

		if (empty($query->select))
			$query->select($modelClass::selectableColumns());

		$primaryKey = $modelClass::$primaryKey;
		$query->andWhere([$primaryKey[0] => $id]);

		$this->augmentQuery($query);

		return $this->queryOneToResponse($query, function($model) { $this->checkPermission($model); } );
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

	public function actionUndelete($id)
	{
		$model = $this->findModel($id);

		$this->checkPermission($model);

		if ($model->undelete() === false)
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
