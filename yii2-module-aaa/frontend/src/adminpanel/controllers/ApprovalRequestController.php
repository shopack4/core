<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\controllers;

use Yii;
use shopack\base\frontend\rest\RestClientDataProvider;
use shopack\aaa\frontend\common\auth\BaseController;
use shopack\aaa\frontend\common\models\ApprovalRequestModel;
use shopack\aaa\frontend\common\models\ApprovalRequestSearchModel;

class ApprovalRequestController extends BaseController
{
	public function actionIndex()
	{
		if (Yii::$app->user->isGuest)
			return $this->goHome();

    $searchModel = new ApprovalRequestSearchModel();
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
		$dataProvider->query->andWhere(['aprUserID' => Yii::$app->user->id]);

    $viewParams = [
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
		];

		if (Yii::$app->request->isAjax)
			return $this->renderJson($this->renderAjax('_index', $viewParams));

    return $this->render('index', $viewParams);
	}

}
