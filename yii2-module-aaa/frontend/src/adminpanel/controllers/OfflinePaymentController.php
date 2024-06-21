<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\controllers;

use Yii;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\frontend\common\helpers\Html;
use shopack\aaa\frontend\common\auth\BaseCrudController;
use shopack\aaa\frontend\common\models\OfflinePaymentModel;
use shopack\aaa\frontend\common\models\OfflinePaymentSearchModel;
use shopack\base\common\helpers\Url;
use shopack\base\frontend\common\models\GeneralAcceptForm;

class OfflinePaymentController extends BaseCrudController
{
	public $modelClass = OfflinePaymentModel::class;
	public $searchModelClass = OfflinePaymentSearchModel::class;
	public $modalDoneFragment = 'offline-payments';

	public function actionCreate_afterCreateModel(&$model)
  {
		$model->ofpOwnerUserID = $_GET['ofpOwnerUserID'] ?? null;
  }

	public function actionAccept($id)
	{
		$model = new GeneralAcceptForm();
		$model->message = Yii::t('aaa', 'Are you sure you want to APPROVE this item?');

		$formPosted = $model->load(Yii::$app->request->post());
		$done = false;
		if ($formPosted) {
			try {
				$done = OfflinePaymentModel::doAccept($id);
			} catch (\Throwable $th) {
				$model->addError(null, $th->getMessage());
			}
		}

    if (Yii::$app->request->isAjax) {
      if ($done) {
				$nextUrl = Yii::$app->getModule('aaa')->createOfflinePaymentAfterAcceptUrl($id);
				if (empty($nextUrl) == false) {
					// $result = Yii::$app->runAction($nextUrl['url'], $nextUrl['params']);
					// return $this->renderContent($result);

					return $this->renderJson([
						// 'message' => Yii::t('app', 'Success'),
						// 'id' => $id,
						// 'nextContent' => Yii::$app->runAction($nextUrl['url'], array_merge(['isPartial' => true],
						'next' => [
							'type' => 'modal',
							'modalPopupSize' => 'sm2',
							'url' => Url::to($nextUrl),
							'title' => 'تمدید عضویت بر اساس پرداخت آفلاین',
						],
						// 'modalDoneFragment' => $this->modalDoneFragment,
					]);
				}

        return $this->renderJson([
          'message' => Yii::t('app', 'Success'),
          // 'id' => $id,
          // 'redirect' => $this->doneLink ? call_user_func($this->doneLink, $model) : null,
          'modalDoneFragment' => $this->modalDoneFragment,
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

      return $this->renderAjaxModal('_accept_form', [
        'model' => $model,
      ]);
    }

    if ($done)
      return $this->redirect(['view', 'id' => $model->primaryKeyValue()]);

    return $this->render('accept', [
      'model' => $model
    ]);
	}

	public function old_actionApprove($id)
	{
    if (empty($_POST['confirmed']))
      throw new BadRequestHttpException('این عملیات باید تایید شده باشد');

		if (Yii::$app->request->isAjax == false)
			throw new BadRequestHttpException('It is not possible to execute this command in a mode other than Ajax');

		// $done = OfflinePaymentModel::doAccept($id);

		$nextUrl = Yii::$app->getModule('aaa')->createOfflinePaymentAfterAcceptUrl($id);
		if (empty($nextUrl) == false) {
			$result = Yii::$app->runAction($nextUrl['url'], $nextUrl['params']);
			return $this->renderContent($result);

			// '/mha/member-master-ins-doc/index', ArrayHelper::merge($_GET, [
			// 	'isPartial' => true,
			// 	'params' => [
			// 		'mbrminsdocMemberID' => $model->mbrUserID,
			// 	],
			// ]));
		}

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

		$done = OfflinePaymentModel::doReject($id);

		return $this->renderJson([
			'status' => 'Ok',
			'message' => Yii::t('app', 'Success'),
			'modalDoneFragment' => $this->modalDoneFragment,
		]);
	}

}
