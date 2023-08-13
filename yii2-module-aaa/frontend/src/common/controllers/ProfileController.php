<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\frontend\helpers\Html;
use shopack\aaa\frontend\common\auth\BaseController;
use shopack\aaa\frontend\common\models\UserModel;
use shopack\aaa\frontend\common\models\ImageChangeForm;
use shopack\aaa\frontend\common\models\EmailChangeForm;
use shopack\aaa\frontend\common\models\MobileChangeForm;

class ProfileController extends BaseController
{
	// public $modelClass = UserModel::class;
	// public $searchModelClass = UserSearchModel::class;

  public function init()
  {
    parent::init();

    $viewPath = dirname(dirname(__FILE__))
      . DIRECTORY_SEPARATOR
      . 'views'
      . DIRECTORY_SEPARATOR
      . $this->id;

    $this->setViewPath($viewPath);
  }

	protected function findUserModel()
	{
		if (($model = UserModel::findOne(Yii::$app->user->id)) === null)
      throw new NotFoundHttpException('The requested item not exist.');

    return $model;
	}

	public function actionIndex()
	{
		if (Yii::$app->user->isGuest)
			return $this->goHome();

    return $this->render('profile', [
      'model' => $this->findUserModel(),
    ]);
	}

	public function actionUpdateUser()
  {
		if (Yii::$app->user->isGuest)
			return $this->goHome();

		$model = $this->findUserModel();

    if ($model->isSoftDeleted())
      throw new BadRequestHttpException('این آیتم حذف شده است و قابل ویرایش نمی‌باشد.');

		$formPosted = $model->load(Yii::$app->request->post());
		$done = false;
		if ($formPosted)
			$done = $model->save();

    if (Yii::$app->request->isAjax) {
      if ($done) {
        return $this->renderJson([
          'message' => Yii::t('app', 'Success'),
          // 'id' => $id,
          // 'redirect' => $this->doneLink ? call_user_func($this->doneLink, $model) : null,
          'modalDoneFragment' => 'details',
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

      return $this->renderAjaxModal('_form_user', [
        'model' => $model,
      ]);
    }

    if ($done)
      return $this->redirect(['index']);

    return $this->render('updateUser', [
      'model' => $model
    ]);
  }

	public function actionUpdateImage()
  {
		if (Yii::$app->user->isGuest)
			return $this->goHome();

		$model = $this->findUserModel();

    if ($model->isSoftDeleted())
      throw new BadRequestHttpException('این آیتم حذف شده است و قابل ویرایش نمی‌باشد.');

    $model = new ImageChangeForm();

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
          'modalDoneFragment' => 'details',
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

      return $this->renderAjaxModal('_form_image', [
        'model' => $model,
      ]);
    }

    if ($done)
      return $this->redirect(['index']);

    return $this->render('updateImage', [
      'model' => $model,
    ]);
  }

  // email-change
  // resend-email-approval
  // mobile-change
  // mobile-approve
  // password-set
  // password-change

  public function actionEmailChange()
  {
		if (Yii::$app->user->isGuest)
			return $this->goHome();

		$model = $this->findUserModel();

    if ($model->isSoftDeleted())
      throw new BadRequestHttpException('این آیتم حذف شده است و قابل ویرایش نمی‌باشد.');

    $model = new EmailChangeForm();

    $formPosted = $model->load(Yii::$app->request->post());
    $done = false;
    if ($formPosted)
      $done = $model->process();

    if (Yii::$app->request->isAjax) {
      if ($done) {
        return $this->renderJson([
          'message' => Yii::t('app', 'Success'),
          'redirect' => Url::to(['request-email-approval-sent', 'email' => $model->email]),
          // 'modalDoneFragment' => 'login',
        ]);
      }

      if ($formPosted) {
        return $this->renderJson([
          'status' => 'Error',
          'message' => Yii::t('app', 'Error'),
          'error' => Html::errorSummary($model),
        ]);
      }

      return $this->renderAjaxModal('_form_email', [
        'model' => $model,
      ]);
    }

    if ($done)
      return $this->redirect(['index']);

    return $this->render('changeEmail', [
      'model' => $model,
    ]);
  }

  public function actionRequestEmailApprovalSent($email)
  {
    return $this->render('requestEmailApprovalSent', [
      'email' => $email,
    ]);
  }

  public function actionResendEmailApproval()
  {
    if (empty($_POST['confirmed']))
      throw new BadRequestHttpException('این عملیات باید تایید شده باشد');

    if (Yii::$app->request->isAjax == false)
			throw new BadRequestHttpException('It is not possible to execute this command in a mode other than Ajax');

		if (Yii::$app->user->isGuest)
			return $this->goHome();

		$userModel = $this->findUserModel();

    if ($userModel->isSoftDeleted())
      throw new BadRequestHttpException('این آیتم حذف شده است و قابل ویرایش نمی‌باشد.');

    if (empty($userModel->usrEmail))
      throw new BadRequestHttpException('ایمیل خالی است.');

    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/auth/request-approval-code',
      HttpHelper::METHOD_POST,
      [],
      [
        'input' => $userModel->usrEmail,
      ]
    );
    if ($resultStatus < 200 || $resultStatus >= 300)
      throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));

		return $this->renderJson([
			'status' => 'Ok',
			'message' => Yii::t('app', 'Success'),
			'modalDoneFragment' => 'login',
		]);
  }

  public function actionMobileChange()
  {
		if (Yii::$app->user->isGuest)
			return $this->goHome();

		$model = $this->findUserModel();

    if ($model->isSoftDeleted())
      throw new BadRequestHttpException('این آیتم حذف شده است و قابل ویرایش نمی‌باشد.');

    $model = new MobileChangeForm();

    $formPosted = $model->load(Yii::$app->request->post());
    $done = false;
    if ($formPosted)
      $done = $model->process();

    if (Yii::$app->request->isAjax) {
      if ($done) {
        return $this->renderJson([
          // 'message' => Yii::t('app', 'Success'),
          // 'redirect' => Url::to(['request-mobile-approval-sent', 'mobile' => $model->mobile]),
          // 'modalDoneFragment' => 'login',
        ]);
      }

      if ($formPosted) {
        return $this->renderJson([
          'status' => 'Error',
          'message' => Yii::t('app', 'Error'),
          'error' => Html::errorSummary($model),
        ]);
      }

      return $this->renderAjaxModal('_form_mobile', [
        'model' => $model,
      ]);
    }

    if ($done)
      return $this->redirect(['index']);

    return $this->render('changeMobile', [
      'model' => $model,
    ]);
  }

  public function actionResendMobileApproval()
  {
    if (empty($_POST['confirmed']))
      throw new BadRequestHttpException('این عملیات باید تایید شده باشد');

    if (Yii::$app->request->isAjax == false)
			throw new BadRequestHttpException('It is not possible to execute this command in a mode other than Ajax');

		if (Yii::$app->user->isGuest)
			return $this->goHome();

		$userModel = $this->findUserModel();

    if ($userModel->isSoftDeleted())
      throw new BadRequestHttpException('این آیتم حذف شده است و قابل ویرایش نمی‌باشد.');

    if (empty($userModel->usrMobile))
      throw new BadRequestHttpException('موبایل خالی است.');

    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/auth/request-approval-code',
      HttpHelper::METHOD_POST,
      [],
      [
        'input' => $userModel->usrMobile,
      ]
    );
    if ($resultStatus < 200 || $resultStatus >= 300)
      throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));

		return $this->renderJson([
			'status' => 'Ok',
			'message' => Yii::t('app', 'Success'),
			'modalDoneFragment' => 'login',
		]);
  }

  public function actionMobileApprove()
  {
		if (Yii::$app->user->isGuest)
			return $this->goHome();

		$model = $this->findUserModel();

    if ($model->isSoftDeleted())
      throw new BadRequestHttpException('این آیتم حذف شده است و قابل ویرایش نمی‌باشد.');
  }

}
