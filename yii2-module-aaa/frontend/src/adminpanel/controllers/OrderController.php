<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use shopack\aaa\common\enums\enuVoucherType;
use shopack\aaa\frontend\common\auth\BaseController;
use shopack\aaa\frontend\common\models\VoucherModel;
use shopack\aaa\frontend\common\models\VoucherSearchModel;

class OrderController extends BaseController
{
	protected function findModel($id)
	{
		if (($model = VoucherModel::findOne($id)) === null)
      throw new NotFoundHttpException('The requested item does not exist.');

    return $model;
	}

  public function actionIndex()
  {
    $searchModel = new VoucherSearchModel();
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
    $dataProvider->query
      ->andWhere(['vchType' => enuVoucherType::Invoice]) //enuVoucherType::Basket])
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

    if ($model->vchType != enuVoucherType::Invoice)
      throw new BadRequestHttpException('Item is not invoice');

    return $this->render('view', [
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
			// 'modalDoneFragment' => $this->modalDoneFragment,
		]);
	}

  //todo:actionReprocess

}
