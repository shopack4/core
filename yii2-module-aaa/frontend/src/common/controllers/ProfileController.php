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
use shopack\base\common\helpers\GeneralHelper;
use shopack\base\frontend\common\helpers\Html;
use shopack\aaa\frontend\common\auth\BaseController;
use shopack\aaa\frontend\common\models\UserModel;
use shopack\aaa\frontend\common\models\ImageChangeForm;
use shopack\aaa\frontend\common\models\EmailChangeForm;
use shopack\aaa\frontend\common\models\MobileChangeForm;
use shopack\aaa\frontend\common\models\ApproveCodeForm;

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

		$userModel = $this->findUserModel();
    if ($userModel->isSoftDeleted())
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

		$userModel = $this->findUserModel();

    if ($userModel->isSoftDeleted())
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

		$userModel = $this->findUserModel();
    if ($userModel->isSoftDeleted())
      throw new BadRequestHttpException('این آیتم حذف شده است و قابل ویرایش نمی‌باشد.');

    $model = new MobileChangeForm();

    $formPosted = $model->load(Yii::$app->request->post());
    $done = false;
    if ($formPosted)
      $done = $model->process();

    if (Yii::$app->request->isAjax) {
      if ($done) {
        //goto phase 2
        $approveCodeModel = new ApproveCodeForm();
        $approveCodeModel->keyType = GeneralHelper::PHRASETYPE_MOBILE;
        $approveCodeModel->input = $model->mobile;

        $timerInfo = $approveCodeModel->getTimerInfo();

        $params = [
          'model' => $approveCodeModel,
          'timerInfo' => $timerInfo,
          // 'resultStatus' => $resultStatus,
          // 'resultData' => $resultData,
          // 'message' => $messageText,
        ];

        return $this->renderAjaxModal('_form_approveCode', [
          'params' => $params,
        ]);
      // } else if ($done === true) {
      //   return $this->renderJson([
      //     'message' => Yii::t('aaa', 'Mobile approved'),
      //     // 'redirect' => Url::to(['request-mobile-approval-sent', 'mobile' => $model->mobile]),
      //     'modalDoneFragment' => 'login',
      //   ]);
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

  //for both email and mobile
  public function actionApproveCode(
    $kt = null, //key type: E:email, M:mobile
    $input = null,
    $code = null
  ) {
		// if (Yii::$app->user->isGuest)
		// 	return $this->goHome();

		// $model = $this->findUserModel();
    // if ($model->isSoftDeleted())
    //   throw new BadRequestHttpException('به دلیل حذف این کاربر، ادامه عملیات امکان پذیر نمی‌باشد.');

    if (empty($input)) {
      if (Yii::$app->user->isGuest)
        throw new BadRequestHttpException('ایمیل/موبایل وارد نشده است.');

  		$userModel = $this->findUserModel();

      if ($kt == GeneralHelper::PHRASETYPE_EMAIL) {
        if (empty($userModel->usrEmail) || (empty($userModel->usrEmailApprovedAt) == false)) {
          throw new BadRequestHttpException('این ایمیل قبلا تایید شده است.');
        }
        $input = $userModel->usrEmail;
      } else if ($kt == GeneralHelper::PHRASETYPE_MOBILE) {
        if (empty($userModel->usrMobile) || (empty($userModel->usrMobileApprovedAt) == false)) {
          throw new BadRequestHttpException('این موبایل قبلا تایید شده است.');
        }
        $input = $userModel->usrMobile;
      } else
        throw new BadRequestHttpException('نوع ورودی نامشخص است.');
    }

    $model = new ApproveCodeForm();
    $model->keyType = $kt;
    $model->input = $input;
    $model->code = $code;

    $post = Yii::$app->request->post();
    $model->load($post);

    $timerInfo = null;
    $resultStatus = 200;
    $resultData = null;
    $messageText = '';

    if (isset($post['resend']) && $post['resend'] == 1) {
      list ($resultStatus, $resultData) = $model->resend();

      if (isset($resultData['message'])) {
        $messageText = $resultData['message'];
        unset($resultData['message']);
        $messageText = Yii::t('aaa', $messageText, $resultData);

        $timerInfo = [
          'ttl' => $resultData['ttl'],
          'remained' => $resultData['remained'],
        ];
      }

    } else if (empty($model->code) == false) {
      $result = $model->process();

      if ($result === true) {
        if (Yii::$app->request->isAjax) {
          return $this->renderJson([
            'message' => Yii::t('app', 'Success'),
            // 'redirect' => Url::to(['request-mobile-approval-sent', 'mobile' => $model->mobile]),
            'modalDoneFragment' => 'login',
          ]);
        }

        return $this->redirect(['index', 'fragment' => 'login']);
      }

      if (is_array($result)) {
        list ($resultStatus, $resultData) = $result;

        if (isset($resultData['message'])) {
          $messageText = $resultData['message'];
          unset($resultData['message']);
          $messageText = Yii::t('aaa', $messageText, $resultData);
        }

        if ($messageText == 'code expired') {
          $timerInfo = [
            'ttl' => 0,
            'remained' => 0,
          ];
        } else if (key_exists('ttl', $resultData)) {
          $timerInfo = [
            'ttl' => $resultData['ttl'],
            'remained' => $resultData['remained'],
          ];
        // } else {
        //   $timerInfo = $model->getTimerInfo();
        }
      // } else { //$result === false
      //   $timerInfo = $model->getTimerInfo();
      }
    // } else {
    //   $timerInfo = $model->getTimerInfo();
    }

    if ($timerInfo === null) {
      try {
        $timerInfo = $model->getTimerInfo();
      } catch (\Throwable $th) {
        $a = 0;
      }
    }

    if (empty($model->keyType)) {
      list ($normalizedInput, $type) = GeneralHelper::recognizeLoginPhrase($model->input, false);
      $model->keyType = $type;
    }

    $params = [
      'model' => $model,
      'timerInfo' => $timerInfo,
      'resultStatus' => $resultStatus,
      'resultData' => $resultData,
      'message' => $messageText,
    ];

    if (Yii::$app->request->isAjax) {
      return $this->renderAjaxModal('_form_approveCode', [
        'params' => $params
      ]);
    }

    return $this->render('approveCode', [
      'params' => $params
    ]);
  }

}
