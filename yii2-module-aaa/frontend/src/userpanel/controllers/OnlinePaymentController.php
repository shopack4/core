<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\userpanel\controllers;

use Yii;
use shopack\aaa\frontend\common\auth\BaseController;
use shopack\aaa\frontend\common\models\OnlinePaymentSearchModel;

class OnlinePaymentController extends BaseController
{
  // public function init()
  // {
  //   parent::init();

  //   $viewPath = dirname(dirname(__FILE__))
  //     . DIRECTORY_SEPARATOR
  //     . 'views'
  //     . DIRECTORY_SEPARATOR
  //     . $this->id;

  //   $this->setViewPath($viewPath);
  // }

  public function actionIndex()
  {
    $searchModel = new OnlinePaymentSearchModel();
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);

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
