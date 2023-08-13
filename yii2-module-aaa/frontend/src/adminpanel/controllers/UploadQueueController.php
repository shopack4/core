<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\controllers;

use Yii;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\frontend\helpers\Html;
use shopack\aaa\frontend\common\auth\BaseCrudController;
use shopack\aaa\frontend\common\models\UploadQueueModel;
use shopack\aaa\frontend\common\models\UploadQueueSearchModel;

class UploadQueueController extends BaseCrudController
{
	public $modelClass = UploadQueueModel::class;
	public $searchModelClass = UploadQueueSearchModel::class;
	public $modalDoneFragment = 'upload-queues';

	// public function actionCreate_afterCreateModel(&$model)
  // {
	// 	$model->uflOwnerUserID = $_GET['uflOwnerUserID'] ?? null;
  // }
/*
	public function actionApprove($id)
	{
    if (empty($_POST['confirmed']))
      throw new BadRequestHttpException('این عملیات باید تایید شده باشد');

		if (Yii::$app->request->isAjax == false)
			throw new BadRequestHttpException('It is not possible to execute this command in a mode other than Ajax');

		$done = UploadQueueModel::doAccept($id);

		return $this->renderJson([
			'status' => 'Ok',
			'message' => Yii::t('app', 'Success'),
			'modalDoneFragment' => $this->modalDoneFragment,
		]);
	}

	public function actionReject($id)
	{
    if (empty($_POST['confirmed']))
      throw new BadRequestHttpException('این عملیات باید تایید شده باشد');

		if (Yii::$app->request->isAjax == false)
			throw new BadRequestHttpException('It is not possible to execute this command in a mode other than Ajax');

		$done = UploadQueueModel::doReject($id);

		return $this->renderJson([
			'status' => 'Ok',
			'message' => Yii::t('app', 'Success'),
			'modalDoneFragment' => $this->modalDoneFragment,
		]);
	}
*/

}
