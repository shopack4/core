<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\auth;

use Yii;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use shopack\base\common\helpers\Url;
use shopack\base\frontend\common\helpers\Html;

abstract class BaseCrudController extends BaseController
{
	public $modelClass;
	public $searchModelClass;
  public $doneLink;
	public $modalDoneFragment;

	public function init()
	{
		parent::init();

    if ($this->doneLink === null) {
      $this->doneLink = function ($model, $deleted = false) {
        if (($deleted == false) || $model->isSoftDeleted())
          return Url::to(['view', 'id' => $model->primaryKeyValue()]);

        return Url::to(['index', 'id' => $model->primaryKeyValue()]);
      };
    }

		if ($this->modelClass === null)
			throw new InvalidConfigException('The "modelClass" property must be set.');

		if ($this->searchModelClass === null)
			throw new InvalidConfigException('The "searchModelClass" property must be set.');
	}

	protected function findModel($id)
	{
		$modelClass = $this->modelClass;
		if (($model = $modelClass::findOne($id)) === null)
      throw new NotFoundHttpException('The requested item not exist.');

    return $model;
	}

  public function getSearchParams()
  {
    return Yii::$app->request->queryParams;
  }

  public function actionIndex(
    $isPartial = false,
    array $params = []
  ) {
		$searchModelClass = $this->searchModelClass;

    $searchModel = new $searchModelClass();
		$dataProvider = $searchModel->search(array_merge($this->getSearchParams(), $params));

    $viewParams = [
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
		];
    if (isset($params))
      $viewParams = array_merge($viewParams, $params);

		if (Yii::$app->request->isAjax)
			return $this->renderJson($this->renderAjax('_index', $viewParams));

    // $isPartial = isset(Yii::$app->request->queryParams['isPartial']);
    if ($isPartial) {
      // $this->layout = false;
      return $this->renderPartial('_index', $viewParams);
      // return $this->renderAjax('_index', $viewParams);
    }

    return $this->render('index', $viewParams);
  }

  public function actionView_afterFindModel($model)
  {
  }

  public function actionView($id)
  {
		$model = $this->findModel($id);

    list ($viewName, $formName) = $this->actionView_afterFindModel($model);
    if (empty($viewName))
      $viewName = 'view';
    if (empty($formName))
      $formName = '_view';

    $params = [
      'model' => $model,
		];

		if (Yii::$app->request->isAjax)
			return $this->renderJson($this->renderAjax($formName, $params));

    return $this->render($viewName, $params);
  }

  public function actionCreate_afterCreateModel(&$model)
  {
  }

  public function actionCreate()
  {
    $model = new $this->modelClass;
    $model->applyDefaultValuesFromColumnsInfo();

    list ($viewName, $formName) = $this->actionCreate_afterCreateModel($model);
    if (empty($viewName))
      $viewName = 'create';
    if (empty($formName))
      $formName = '_form';

		$formPosted = $model->load(Yii::$app->request->post());
		$done = false;
		if ($formPosted)
			$done = $model->save();

    if (Yii::$app->request->isAjax) {
      if ($done) {
        return $this->renderJson([
          'message' => Yii::t('app', 'Success'),
          // 'id' => $id,
          // 'redirect' => $this->doneLink ? call_user_func($this->doneLink, $model) : null,
          'modalDoneFragment' => $this->modalDoneFragment,
        ]);
      }

      if ($formPosted) {
        return $this->renderJson([
          'status' => 'Error',
          'message' => Yii::t('app', 'Error'),
          // 'id' => $id,
          'error' => Html::errorSummary($model),
        ]);
      }

      return $this->renderAjaxModal($formName, [
        'model' => $model,
      ]);
    }

    if ($done)
      return $this->redirect(['view', 'id' => $model->primaryKeyValue()]);

    return $this->render($viewName, [
      'model' => $model,
    ]);
  }

  public function actionUpdate_afterFindModel(&$model)
  {
  }

  public function actionUpdate($id)
  {
		$model = $this->findModel($id);

    list ($viewName, $formName) = $this->actionUpdate_afterFindModel($model);
    if (empty($viewName))
      $viewName = 'update';
    if (empty($formName))
      $formName = '_form';

    if ($model->isSoftDeleted())
      throw new BadRequestHttpException('این آیتم حذف شده است و قابل ویرایش نمی‌باشد.');

		$formPosted = $model->load(Yii::$app->request->post());
		$done = false;
		if ($formPosted)
			$done = $model->save();

    if (Yii::$app->request->isAjax) {
      if ($done) {
        return $this->renderJson([
          'message' => Yii::t('app', 'Success'),
          // 'id' => $id,
          // 'redirect' => $this->doneLink ? call_user_func($this->doneLink, $model) : null,
          'modalDoneFragment' => $this->modalDoneFragment,
        ]);
      }

      if ($formPosted) {
        return $this->renderJson([
          'status' => 'Error',
          'message' => Yii::t('app', 'Error'),
          // 'id' => $id,
          'error' => Html::errorSummary($model),
        ]);
      }

      return $this->renderAjaxModal($formName, [
        'model' => $model,
      ]);
    }

    if ($done)
      return $this->redirect(['view', 'id' => $model->primaryKeyValue()]);

    return $this->render($viewName, [
      'model' => $model
    ]);
  }

  public function actionDelete($id)
  {
    if (empty($_POST['confirmed']))
      throw new BadRequestHttpException('دستور حذف باید تایید شده باشد');

		$model = $this->findModel($id);
    $done = $model->delete();

    if ($done)
      return $this->redirect($this->doneLink ? call_user_func($this->doneLink, $model, true) : 'index');

    return $this->redirect(
      $this->doneLink ? call_user_func($this->doneLink, $model, false)
        : ['view', 'id' => $model->primaryKeyValue()]);
  }

  public function actionUndelete($id)
  {
    if (empty($_POST['confirmed']))
      throw new BadRequestHttpException('دستور بازگردانی باید تایید شده باشد');

		$model = $this->findModel($id);
    $done = $model->undelete();

    if ($done)
      return $this->redirect($this->doneLink ? call_user_func($this->doneLink, $model, false) : 'index');

    return $this->redirect(
      $this->doneLink ? call_user_func($this->doneLink, $model, false)
        : ['view', 'id' => $model->primaryKeyValue()]);
  }

}
