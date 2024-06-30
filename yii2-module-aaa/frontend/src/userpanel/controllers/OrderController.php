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
use shopack\aaa\frontend\userpanel\models\OrderChangeDeliveryMethodForm;
use shopack\aaa\frontend\userpanel\models\OrderPaymentForm;
use shopack\base\common\helpers\HttpHelper;
use yii\web\BadRequestHttpException;

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
    $model = new OrderChangeDeliveryMethodForm();
    $model->vchID = $id;

		$formPosted = $model->load(Yii::$app->request->post());
		$done = false;
		if ($formPosted)
			$done = $model->process();

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

  public function actionPay($id)
  {
    $model = new OrderPaymentForm();
    $model->vchID = $id;

		$formPosted = $model->load(Yii::$app->request->post());
		$done = false;
		if ($formPosted)
			$done = $model->process();

    if (Yii::$app->request->isAjax) {
      if ($done != false) {
        if ($done === true) {
          return $this->renderJson([
            'message' => Yii::t('app', 'Success'),
          ]);
        }

        return $this->renderJson([
          'message' => Yii::t('app', 'Success'),
          'redirect' => $done['paymentUrl'],
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

      return $this->renderAjaxModal('_payment_form', [
        'model' => $model,
      ]);
    }

    if ($done != false)
      return $this->redirect($done['paymentUrl']);

    return $this->render('payment', [
      'model' => $model,
    ]);
  }

	public function actionCancel($id)
	{
    if (empty($_POST['confirmed']))
      throw new BadRequestHttpException('این عملیات باید تایید شده باشد');

		if (Yii::$app->request->isAjax == false)
			throw new BadRequestHttpException('It is not possible to execute this command in a mode other than Ajax');

		$done = VoucherModel::doCancel($id);

		return $this->renderJson([
			'status' => 'Ok',
			'message' => Yii::t('app', 'Success'),
			'modalDoneFragment' => $this->modalDoneFragment,
		]);
	}

}
