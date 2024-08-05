<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\controllers;

use Yii;
use yii\db\Expression;
use shopack\aaa\frontend\common\auth\BaseController;
use shopack\aaa\frontend\common\models\SessionSearchModel;

class SessionController extends BaseController
{
	public function actionOnlineList()
	{
		if (Yii::$app->user->isGuest)
			return $this->goHome();

    $searchModel = new SessionSearchModel();
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);

		$dataProvider->query
			->andWhere(['>=', 'ssnTokenExpireAt', new Expression('NOW()')])
		;

    $viewParams = [
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
		];

		if (Yii::$app->request->isAjax)
			return $this->renderJson($this->renderAjax('_online-list', $viewParams));

    return $this->render('onlineList', $viewParams);
	}

}
