<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\web\UnprocessableEntityHttpException;
use shopack\base\frontend\helpers\Html;
use shopack\aaa\common\enums\enuOnlinePaymentStatus;
use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\aaa\common\enums\enuVoucherType;
use shopack\aaa\frontend\common\auth\BaseController;
use shopack\aaa\frontend\common\models\WalletModel;
use shopack\aaa\frontend\common\models\WalletSearchModel;
use shopack\aaa\frontend\common\models\WalletIncreaseForm;
use shopack\aaa\frontend\common\models\OnlinePaymentModel;
use shopack\aaa\frontend\common\models\VoucherSearchModel;

class OrderController extends BaseController
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
    $searchModel = new VoucherSearchModel();
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
    $dataProvider->query
      ->andWhere(['vchType' => enuVoucherType::Basket])
      ->andWhere(['!=', 'vchStatus', enuVoucherStatus::New])
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
