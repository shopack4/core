<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\controllers;

use Yii;
use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\aaa\common\enums\enuVoucherType;
use shopack\aaa\frontend\common\auth\BaseController;
use shopack\aaa\frontend\common\models\VoucherSearchModel;

class BasketController extends BaseController
{
  public function actionIndex()
  {
    $searchModel = new VoucherSearchModel();
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
    $dataProvider->query
      ->andWhere(['vchType' => enuVoucherType::Basket])
      ->andWhere(['vchStatus' => enuVoucherStatus::New])
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

}
