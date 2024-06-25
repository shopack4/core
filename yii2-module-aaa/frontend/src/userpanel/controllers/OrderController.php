<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\userpanel\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use shopack\base\frontend\common\helpers\Html;
use shopack\aaa\common\enums\enuVoucherType;
use shopack\aaa\frontend\common\auth\BaseController;
use shopack\aaa\frontend\common\models\VoucherModel;
use shopack\aaa\frontend\common\models\VoucherSearchModel;
use shopack\aaa\frontend\userpanel\models\ChangeOrderDeliveryMethodForm;

class OrderController extends BaseController
{
	protected function findModel($id)
	{
		$modelClass = new VoucherModel();

    $models = $modelClass::find()
      ->where(['vchID' => $id])
      ->andWhere(['vchType' => enuVoucherType::Invoice])
      ->all(); //one($id);

		if (empty($models))
      throw new NotFoundHttpException('The requested item does not exist.');

    return $models[0];
	}

  public function actionIndex()
  {
    $searchModel = new VoucherSearchModel();
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
    $dataProvider->query
      ->andWhere(['vchType' => enuVoucherType::Invoice])
      // ->andWhere(['!=', 'vchStatus', enuVoucherStatus::New])
    ;

    $viewParams = [
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
		];
    if (isset($params))
      $viewParams = array_merge($viewParams, $params);

		if (Yii::$app->request->isAjax)
			return $this->renderJson($this->renderAjax('_index', $viewParams));

    return $this->render('index', $viewParams);
  }

  public function actionView($id)
  {
		$model = $this->findModel($id);

    return $this->render('view', [
      'model' => $model,
		]);
  }

  public function actionChangeDeliveryMethod($id)
  {
    $model = new ChangeOrderDeliveryMethodForm();
    $model->vchID = $id;

		$formPosted = $model->load(Yii::$app->request->post());
		$done = false;

		if ($formPosted) {
			$done = $model->process();
    } else {
      $voucherModel = $this->findModel($id);
      $model->deliveryMethod = $voucherModel->vchDeliveryMethodID;
    }

    if (Yii::$app->request->isAjax) {
      if ($done) {
        return $this->renderJson([
          'message' => Yii::t('app', 'Success'),
          // 'id' => $id,
          // 'redirect' => $this->doneLink ? call_user_func($this->doneLink, $model) : null,
          // 'modalDoneFragment' => $this->modalDoneFragment,
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

      return $this->renderAjaxModal('_deliveryMethod_form', [
        'model' => $model,
      ]);
    }

    if ($done)
      return $this->redirect(['view', 'id' => $id]);

    return $this->render('deliveryMethod', [
      'model' => $model
    ]);
  }

}
