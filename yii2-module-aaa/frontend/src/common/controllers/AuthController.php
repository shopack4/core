<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use shopack\base\frontend\helpers\Html;
use shopack\base\common\helpers\HttpHelper;
use shopack\aaa\frontend\common\auth\BaseController;
use shopack\aaa\frontend\common\models\UserModel;
use shopack\aaa\frontend\common\models\SignupForm;
use shopack\aaa\frontend\common\models\LoginForm;
use shopack\aaa\frontend\common\models\LoginByMobileForm;
use shopack\aaa\frontend\common\models\ChallengeForm;
use shopack\aaa\frontend\common\helpers\AuthHelper;
use shopack\aaa\frontend\common\models\ApproveCodeForm;
// use shopack\aaa\frontend\common\models\PasswordSetForm;
use shopack\aaa\frontend\common\models\PasswordChangeForm;
use shopack\aaa\frontend\common\models\PasswordResetByForgotCodeForm;
use shopack\aaa\frontend\common\models\RequestForgotPasswordForm;
use shopack\base\common\helpers\GeneralHelper;

class AuthController extends BaseController
{
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

	public function behaviors()
	{
		$behaviors = parent::behaviors();

		$behaviors[BaseController::BEHAVIOR_AUTHENTICATOR]['optional'] = [
		  'signup',
		];

		// $behaviors[BaseController::BEHAVIOR_AUTHENTICATOR]['only'] = [
		// ];

		$behaviors[BaseController::BEHAVIOR_AUTHENTICATOR]['except'] = [
			'login',
			'login-by-mobile',
			'challenge',
			// 'request-login-by-mobile-code',
			// 'request-approval-code',
			'accept-approval',
			'request-forgot-password',
			'password-reset-by-forgot-code',
			'password-reset-by-forgot-code-done',
		];

		// $behaviors['verbs'] = [
		// 	'class' => VerbFilter::class,
		// 	'actions' => [
		// 		'login' => ['post'],
		// 		'logout' => ['get', 'post'],
		// 	],
		// ];

		return $behaviors;
	}

  public function beforeAction($action)
  {
    if ($action->id == 'logout')
      $this->enableCsrfValidation = false;

    return parent::beforeAction($action);
  }

  // public function behaviors()
  // {
  //   return [
  //     'access' => [
  //       'class' => AccessControl::class,
  //       'only' => ['logout'],
  //       'rules' => [
  //         [
  //           'actions' => ['logout'],
  //           'allow' => true,
  //           'roles' => ['@'],
  //         ],
  //       ],
  //     ],
  //     'verbs' => [
  //       'class' => VerbFilter::class,
  //       'actions' => [
  //         'logout' => ['post'],
  //       ],
  //     ],
  //   ];
  // }

  // public function actions()
  // {
  //   return [
  //     'error' => [
  //       'class' => 'yii\web\ErrorAction',
  //     ],
  //     'captcha' => [
  //       'class' => 'yii\captcha\CaptchaAction',
  //       'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
  //     ],
  //   ];
  // }

  protected function findUserModel()
	{
		if (($model = UserModel::findOne(Yii::$app->user->id)) === null)
      throw new NotFoundHttpException('The requested item not exist.');

    return $model;
	}

  // public function actionIndex()
  // {
  //   return $this->render('index');
  // }

  public function actionSignup()
  {
    if (Yii::$app->controller->module->allowSignup == false)
      return $this->goHome();

    if (Yii::$app->user->isGuest == false)
      return $this->goHome();

    $resultStatus = 200;
    $resultData = null;
    $messageText = '';
    $challenge = null;

    $model = new SignupForm();
    if ($model->load(Yii::$app->request->post())) {
      $result = $model->process();

      if ($result === true)
        return $this->goHome();

      if (is_array($result)) {
        list ($resultStatus, $resultData) = $result;

        if (isset($resultData['challenge'])) {
          $challenge = $resultData['challenge'];

          $challengeModel = new ChallengeForm();
          $challengeModel->realm = 'signup';
          $challengeModel->type = $challenge;
          $challengeModel->key = $model->mobile;
          $challengeModel->login = true;
          $challengeModel->rememberMe = $model->rememberMe;

          return AuthHelper::redirectToChallenge($challengeModel);
        }

        if (isset($resultData['message'])) {
          $messageText = $resultData['message'];
          unset($resultData['message']);
          $messageText = Yii::t('aaa', $messageText, $resultData);
        }
      }
    }

    $model->password = '';
    $model->retypePassword = '';

    Yii::$app->controller->layout = "/login";
    return $this->render('signup', [
      'model' => $model,
      'resultStatus' => $resultStatus,
      'resultData' => $resultData,
      'message' => $messageText,
    ]);
  }

  public function actionLogin($donelink = null)
  {
    if (Yii::$app->user->isGuest == false)
      return $this->redirect($donelink ?? Yii::$app->getHomeUrl());

    $resultStatus = 200;
    $resultData = null;
    $messageText = '';
    $challenge = null;

    $model = new LoginForm();
    if ($model->load(Yii::$app->request->post())) {
      $result = $model->login();

      if ($result === true)
        return $this->redirect($donelink ?? Yii::$app->getHomeUrl());

      if ($result === 'challenge') {
        $challengeModel = new ChallengeForm();
        $challengeModel->realm = 'login-by-mobile';
        $challengeModel->type = $model->challenge;
        $challengeModel->key = $model->mobile;
        $challengeModel->login = true;
        $challengeModel->rememberMe = $model->rememberMe;

        return AuthHelper::redirectToChallenge($challengeModel, $donelink);
      }

      if (is_array($result)) {
        list ($resultStatus, $resultData) = $result;

        // if (isset($resultData['challenge'])) {
        //   $challenge = $resultData['challenge'];

        //   $challengeModel = new ChallengeForm();
        //   $challengeModel->realm = 'login-by-mobile';
        //   $challengeModel->type = $challenge;
        //   $challengeModel->key = $model->mobile;
        //   $challengeModel->login = true;
        //   $challengeModel->rememberMe = $model->rememberMe;

        //   return AuthHelper::redirectToChallenge($challengeModel);
        // }

        if (isset($resultData['message'])) {
          $messageText = $resultData['message'];
          unset($resultData['message']);
          $messageText = Yii::t('aaa', $messageText, $resultData);
        }
      }
    }

    $model->password = '';

    Yii::$app->controller->layout = "/login";
    return $this->render('login', [
      'model' => $model,
      'resultStatus' => $resultStatus,
      'resultData' => $resultData,
      'message' => $messageText,
    ]);
  }

  // public function actionRequestLoginByMobileCode()
  // {
  //   if (Yii::$app->user->isGuest == false)
  //     return $this->goHome();
  // }

  public function actionLoginByMobile(
    $donelink = null,
    $realm = null,
    $signupIfNotExists = false
  ) {
    if (Yii::$app->user->isGuest == false)
      return $this->redirect($donelink ?? Yii::$app->getHomeUrl());

    if (empty($realm))
      $realm = 'login-by-mobile';

    if (str_starts_with($realm, 'login'))
      Yii::$app->controller->layout = "/login";
    else
      Yii::$app->controller->layout = '/' . $realm;

    $resultStatus = 200;
    $resultData = null;
    $messageText = '';
    $challenge = null;
    $showCreateNewUser = false;

    $model = new LoginByMobileForm();
    $model->signupIfNotExists = $signupIfNotExists;

    if ($model->load(Yii::$app->request->post())) {
      $result = $model->process();

      if ($result === true)
        return $this->redirect($donelink ?? Yii::$app->getHomeUrl());

      if ($result === 'challenge') {
        $challengeModel = new ChallengeForm();
        $challengeModel->realm = $realm;
        $challengeModel->type = $model->challenge;
        $challengeModel->key = $model->mobile;
        $challengeModel->login = true;
        $challengeModel->rememberMe = $model->rememberMe;

        return AuthHelper::redirectToChallenge($challengeModel, $donelink);
      }

      if (is_array($result)) {
        list ($resultStatus, $resultData) = $result;

        // if (isset($resultData['challenge'])) {
        //   $challenge = $resultData['challenge'];

        //   $challengeModel = new ChallengeForm();
        //   $challengeModel->realm = 'login-by-mobile';
        //   $challengeModel->type = $challenge;
        //   $challengeModel->key = $model->mobile;
        //   $challengeModel->login = true;
        //   $challengeModel->rememberMe = $model->rememberMe;

        //   return AuthHelper::redirectToChallenge($challengeModel);
        // }

        if (isset($resultData['message'])) {
          $messageText = $resultData['message'];
          unset($resultData['message']);

          if ($messageText == LoginByMobileForm::ERROR_MOBILE_NOT_EXISTS) {
            $showCreateNewUser = true;
          }

          $messageText = Yii::t('aaa', $messageText, $resultData);
        }
      }
    }

    return $this->render('loginByMobile', [
      'model' => $model,
      'resultStatus' => $resultStatus,
      'resultData' => $resultData,
      'message' => $messageText,
      'showCreateNewUser'=> $showCreateNewUser,
      'realm' => $realm,
    ]);
  }

  public function actionChallenge(
    $realm = null,
    $type = null,
    $key = null,
    $value = null,
    $rememberMe = false,
    $donelink = null
  ) {
    $model = new ChallengeForm();
    $post = Yii::$app->request->post();

    if ($model->load($post) == false) {
      $model->realm = $realm;
      $model->type = $type;
      $model->key = $key;
      $model->value = $value;
      $model->login = str_starts_with($model->realm, 'login');
      $model->rememberMe = $rememberMe;
    }

    if (empty($model->realm)
      || empty($model->type)
      || empty($model->key)
    ) {
      throw new \Exception('invalid data');
    }

    if (str_starts_with($model->realm, 'login'))
      Yii::$app->controller->layout = "/login";
    else
      Yii::$app->controller->layout = '/' . $model->realm;

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

    } else if (empty($model->value) == false) {
      $result = $model->process();

      if ($result === true)
        return $this->redirect($donelink ?? Yii::$app->getHomeUrl());

      if (is_array($result)) {
        list ($resultStatus, $resultData) = $result;

        //next challenge?
        if (isset($resultData['challenge'])) {
          $challenge = $resultData['challenge'];

          // $challengeModel = new ChallengeForm();
          $model->type = $challenge;
          $model->value = null;

          // return AuthHelper::redirectToChallenge($challengeModel);
        }

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

    return $this->render('challenge', [
      'model' => $model,
      'timerInfo' => $timerInfo,
      'resultStatus' => $resultStatus,
      'resultData' => $resultData,
      'message' => $messageText,
    ]);
  }

  public function actionLogout()
  {
    $result = HttpHelper::callApi('aaa/auth/logout',
      HttpHelper::METHOD_POST
    );

    if (isset($result['error']['message']))
      throw new \Exception($result['error']['message']);

    Yii::$app->user->logout();

    return $this->goHome();
  }

  public function actionAcceptApproval()
  {
    $resultStatus = 200;
    $resultData = null;
    $messageText = '';
    $challenge = null;

    $model = new ApproveCodeForm();

    $requestParams = array_merge(Yii::$app->request->getBodyParams(), Yii::$app->request->getQueryParams());

    if ($model->load($requestParams, '')) {
      $result = $model->process();

      if ($result === true)
        return $this->goHome();

      if ($result === 'challenge') {
        $challengeModel = new ChallengeForm();
        $challengeModel->realm = 'accept-approval';
        $challengeModel->type = $model->challenge;
        $challengeModel->key = $model->mobile;
        $challengeModel->login = true;
        $challengeModel->rememberMe = $model->rememberMe;

        return AuthHelper::redirectToChallenge($challengeModel);
      }

      if (is_array($result)) {
        list ($resultStatus, $resultData) = $result;

        // if (isset($resultData['challenge'])) {
        //   $challenge = $resultData['challenge'];

        //   $challengeModel = new ChallengeForm();
        //   $challengeModel->realm = 'login-by-mobile';
        //   $challengeModel->type = $challenge;
        //   $challengeModel->key = $model->mobile;
        //   $challengeModel->login = true;
        //   $challengeModel->rememberMe = $model->rememberMe;

        //   return AuthHelper::redirectToChallenge($challengeModel);
        // }

        if (isset($resultData['message'])) {
          $messageText = $resultData['message'];
          unset($resultData['message']);
          $messageText = Yii::t('aaa', $messageText, $resultData);
        }
      }
    }

    return $this->render('accept-approval', [
      'model' => $model,
      'resultStatus' => $resultStatus,
      'resultData' => $resultData,
      'message' => $messageText,
    ]);
  }

  //for users without password
/*  public function actionPasswordSet()
  {
		if (Yii::$app->user->isGuest)
			return $this->goHome();

		$userModel = $this->findUserModel();

    if ($userModel->isSoftDeleted())
      throw new BadRequestHttpException('این آیتم حذف شده است و قابل ویرایش نمی‌باشد.');

    if ($userModel->hasPassword)
      throw new BadRequestHttpException('شما قبلا نام رمز تعیین کرده‌اید.');

    if (empty($userModel->usrEmailApprovedAt))
      throw new BadRequestHttpException('لطفا ایمیل خود را تایید کنید.');

    if (empty($userModel->usrMobileApprovedAt))
      throw new BadRequestHttpException('لطفا موبایل خود را تایید کنید.');

    $model = new PasswordSetForm();

    $formPosted = $model->load(Yii::$app->request->post());
    $done = false;
    if ($formPosted)
      $done = $model->process();

    if ($done) {
      return $this->renderJson([
        'message' => Yii::t('app', 'Success'),
        'modalDoneFragment' => 'login',
      ]);
    }

    if ($formPosted) {
      return $this->renderJson([
        'status' => 'Error',
        'message' => Yii::t('app', 'Error'),
        'error' => Html::errorSummary($model),
      ]);
    }

    return $this->renderAjaxModal('_form_password_set', [
      'model' => $model,
    ]);
  }
*/
  //for users with password
  public function actionPasswordChange()
  {
		if (Yii::$app->user->isGuest)
			return $this->goHome();

		$userModel = $this->findUserModel();

    if ($userModel->isSoftDeleted())
      throw new BadRequestHttpException('این آیتم حذف شده است و قابل ویرایش نمی‌باشد.');

    // if ($model->hasPassword == false)
    //   throw new BadRequestHttpException('شما قبلا نام رمز تعیین نکرده‌اید.');

    $model = new PasswordChangeForm();
    $model->hasPassword = $userModel->hasPassword;

    $formPosted = $model->load(Yii::$app->request->post());
    $done = false;
    if ($formPosted)
      $done = $model->process();

    if ($done) {
      return $this->renderJson([
        'message' => Yii::t('app', 'Success'),
        'modalDoneFragment' => 'login',
      ]);
    }

    if ($formPosted) {
      return $this->renderJson([
        'status' => 'Error',
        'message' => Yii::t('app', 'Error'),
        'error' => Html::errorSummary($model),
      ]);
    }

    return $this->renderAjaxModal('_form_password_change', [
      'model' => $model,
    ]);
  }

	public function actionRequestForgotPassword()
  {
    $model = new RequestForgotPasswordForm();

    $message = null;

    $formPosted = $model->load(Yii::$app->request->post());
    $done = false;
    try {
      if ($formPosted)
        $done = $model->process();

      if ($done) {
        if (GeneralHelper::isEmail($model->input)) {
          $message = Yii::t('aaa', 'A new password reset link has been sent to the given email');
        } else {
          return $this->redirect(['password-reset-by-forgot-code',
            'input' => $model->input,
          ]);
        }
      }
    } catch (\Throwable $exp) {
      $message = $exp->getMessage();
    }

    Yii::$app->controller->layout = "/login";
    return $this->render('requestForgotPassword', [
      'model' => $model,
      'message' => $message,
    ]);
  }

  public function actionPasswordResetByForgotCode(
    $input,
    $code = null
  ) {
    $model = new PasswordResetByForgotCodeForm();
    $model->input = $input;
    $model->code = $code;

    $post = Yii::$app->request->post();
    $formPosted = $model->load($post);

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
        // $model->addError(null, $messageText);

        $timerInfo = [
          'ttl' => $resultData['ttl'],
          'remained' => $resultData['remained'],
        ];
      }

    } else if ($formPosted) {
      $result = $model->process();

      if ($result === true) {
        return $this->redirect(['password-reset-by-forgot-code-done']);
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

    list ($normalizedInput, $keyType) = GeneralHelper::recognizeLoginPhrase($model->input, false);

    $params = [
      'model' => $model,
      'timerInfo' => $timerInfo,
      'resultStatus' => $resultStatus,
      'resultData' => $resultData,
      'message' => $messageText,
      'keyType' => $keyType,
    ];

    return $this->render('passwordResetByForgotCode', [
      'params' => $params,
    ]);
  }

  public function actionPasswordResetByForgotCodeDone()
  {
    return $this->render('passwordResetByForgotCodeDone');
  }

}
