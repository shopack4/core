<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\userpanel\controllers;

use Yii;
use shopack\aaa\frontend\common\auth\BaseCrudController;
use shopack\aaa\frontend\common\models\OfflinePaymentModel;
use shopack\aaa\frontend\common\models\OfflinePaymentSearchModel;

class OfflinePaymentController extends BaseCrudController
{
  public $modelClass = OfflinePaymentModel::class;
	public $searchModelClass = OfflinePaymentSearchModel::class;

	public function actionCreate_afterCreateModel(&$model)
  {
		$model->ofpOwnerUserID = Yii::$app->user->identity->usrID;
  }

}
