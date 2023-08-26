<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\web\UnauthorizedHttpException;
use shopack\base\common\helpers\ExceptionHelper;
use shopack\base\backend\controller\BaseRestController;
use shopack\base\backend\helpers\AuthHelper;
use shopack\base\backend\helpers\PrivHelper;
use shopack\base\backend\helpers\RESTfulHelper;
use shopack\aaa\backend\models\UserModel;
use shopack\aaa\backend\models\SignupForm;
use shopack\aaa\backend\models\LoginForm;
use shopack\aaa\backend\models\LoginByMobileForm;
use shopack\aaa\backend\models\ApproveCodeForm;
use shopack\aaa\backend\models\ApprovalRequestModel;
use shopack\aaa\backend\models\ForgotPasswordRequestModel;
use shopack\aaa\backend\models\PasswordResetByForgotCodeForm;
// use shopack\aaa\backend\models\PasswordSetForm;
use shopack\aaa\backend\models\PasswordChangeForm;
use shopack\base\common\helpers\GeneralHelper;

class AuthController extends BaseRestController
{
	public function behaviors()
	{
		$behaviors = parent::behaviors();

		$behaviors[BaseRestController::BEHAVIOR_AUTHENTICATOR]['optional'] = [
		  'signup',
		];

		// $behaviors[BaseRestController::BEHAVIOR_AUTHENTICATOR]['only'] = [
		// ];

		$behaviors[BaseRestController::BEHAVIOR_AUTHENTICATOR]['except'] = [
			'login',
			'login-by-mobile',
			'request-approval-code',
			'accept-approval',
			'request-forgot-password',
			'forgot-password-timer-info',
			'password-reset-by-forgot-code',
			'challenge',
			'challenge-timer-info',
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

	public function actionOptions()
	{
		return 'options';
	}

	public function actionSignup()
	{
		$model = new SignupForm();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		try {
			if ($model->signup() == false)
				throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));
		} catch(\Exception $exp) {
			$msg = ExceptionHelper::CheckDuplicate($exp, $model);
			throw new UnprocessableEntityHttpException($msg);
		}

		//logout
		//-----------------------
		try {
			AuthHelper::logout();
		} catch (\Throwable $th) { ; }

		//login
		//-----------------------
		list ($token, $mustApprove, $sessionModel) = AuthHelper::doLogin($model->user, $model->rememberMe);

		return [
			'token' => $token,
			'mustApprove' => $mustApprove,
			// 'due' => $sessionModel->ssnLongExpireAt,
		];
	}

	public function actionLogin()
	{
		$model = new LoginForm();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("Username and Password not provided");

		return $model->login();
	}

	/**
	 * mobile
	 * code ?
	 */
	public function actionLoginByMobile()
	{
		$model = new LoginByMobileForm();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("Username and Password not provided");

		return $model->process();
	}

	/**
	 * key
	 */
	public function actionChallengeTimerInfo()
	{
		$bodyParams = Yii::$app->request->getBodyParams();

		if (empty($bodyParams['input']))
			throw new NotFoundHttpException("parameters not provided");

		return [
			'result' => ApprovalRequestModel::getTimerInfo($bodyParams['input']),
		];

		// $seconds = 120;
		// return [
		// 	'timer' => [
		// 		'ttl' => $seconds,
		// 		'remained' => GeneralHelper::formatTimeFromSeconds($seconds),
		// 	],
		// ];
	}

	/**
	 * key
	 * value
	 */
	public function actionChallenge()
	{
		$bodyParams = Yii::$app->request->getBodyParams();

		if (empty($bodyParams['key']) || empty($bodyParams['value']))
			throw new NotFoundHttpException("parameters not provided");

		$result = ApprovalRequestModel::acceptCode($bodyParams['key'], $bodyParams['value']);
		$userModel = $result['userModel'];

		if ($userModel) {
			if ($bodyParams['rememberMe'] ?? false) {
				list ($token, $mustApprove) = AuthHelper::doLogin($userModel, $bodyParams['rememberMe'] ?? false);

				return [
					'token' => $token,
					'mustApprove' => $mustApprove,
				];
			}

			return [
				'result' => true,
			];
		}

		throw new UnauthorizedHttpException("could not login.");
		// return [
		// 	'result' => ,
		// ];
	}

	public function actionLogout()
	{
		AuthHelper::logout();

		return [
			'result' => true,
		];
	}

	/**
	 * input
	 */
	public function actionRequestApprovalCode()
	{
		$bodyParams = Yii::$app->request->getBodyParams();

		if (empty($bodyParams['input']))
			throw new NotFoundHttpException("parameters not provided");

		return [
			'result' => ApprovalRequestModel::requestCode($bodyParams['input']),
		];
	}

	/**
	 * input
	 * code
	 */
	public function actionAcceptApproval()
	{
		$model = new ApproveCodeForm();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		return $model->approve();

		// return [
		// 	'result' => true,
		// ];
	}

	/**
	 * input
	 */
	public function actionRequestForgotPassword()
	{
		$bodyParams = Yii::$app->request->getBodyParams();

		if (empty($bodyParams['input']))
			throw new NotFoundHttpException("parameters not provided");

		return [
			'result' => ForgotPasswordRequestModel::requestCode($bodyParams['input']),
		];
	}

	/**
	 * key
	 */
	public function actionForgotPasswordTimerInfo()
	{
		$bodyParams = Yii::$app->request->getBodyParams();

		if (empty($bodyParams['input']))
			throw new NotFoundHttpException("parameters not provided");

		return [
			'result' => ForgotPasswordRequestModel::getTimerInfo($bodyParams['input']),
		];

		// $seconds = 120;
		// return [
		// 	'timer' => [
		// 		'ttl' => $seconds,
		// 		'remained' => GeneralHelper::formatTimeFromSeconds($seconds),
		// 	],
		// ];
	}

	/**
	 * input
	 * code
	 * newPassword
	 */
	public function actionPasswordResetByForgotCode()
	{
		$model = new PasswordResetByForgotCodeForm();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		if ($model->save() == false)
			throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

		return [
			'result' => true,
		];
	}

	/**
	 * userID
	 * newPassword
	 */
/*	public function actionPasswordSet()
	{
		PrivHelper::checkPriv('aaa/auth/passwordSet');

		$model = new PasswordSetForm();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		if ($model->save() == false)
			throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

		return [
			'result' => true,
		];
	}
*/
	/**
	 * oldPassword
	 * newPassword
	 */
	public function actionPasswordChange()
	{
		$model = new PasswordChangeForm();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		if ($model->save() == false)
			throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

		return [
			'result' => true,
		];
	}

}
