<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\auth;

use Yii;
use yii\filters\VerbFilter;
use yii\web\HttpException;
use yii\web\ForbiddenHttpException;
use yii\web\UnauthorizedHttpException;
use shopack\base\common\auth\AuthHelper;
use shopack\aaa\frontend\common\auth\JwtHttpCookieAuth;
use shopack\aaa\frontend\common\models\UserModel;

class BaseController extends \shopack\base\frontend\common\classes\BaseController
{
	const BEHAVIOR_AUTHENTICATOR = 'authenticator';
	const BEHAVIOR_VERBS = 'verbs';

	public function behaviors()
	{
		$behaviors = parent::behaviors();

		$behaviors[self::BEHAVIOR_AUTHENTICATOR] = [
			'class' => JwtHttpCookieAuth::class,
		];

		$behaviors[self::BEHAVIOR_VERBS] = [
      'class' => VerbFilter::class,
      'actions' => [
        'delete' => ['POST'],
        'undelete' => ['POST'],
      ],
    ];

		return $behaviors;
	}

	public function runAction($id, $params = [])
	{
		$refreshToken = false;

		try {

			return parent::runAction($id, $params);

		} catch (\Throwable $th) {
			if ((Yii::$app->user->isGuest == false)
				&& (($th->getCode() == 401) || ($th instanceof UnauthorizedHttpException))
			) {
				$refreshToken = true;
			}

			if ($refreshToken == false) {
				if ($th->getMessage() == 'Your request was made with invalid or expired JSON Web Token.')
					return $this->redirect(\Yii::$app->user->loginUrl);

				throw $th;
			}
		}

		if ($refreshToken) {
			$newToken = AuthHelper::refreshToken(Yii::$app->user->identity->accessToken);

			if (($newToken == false) || ($newToken == null)) {
				return $this->redirect(\Yii::$app->user->loginUrl);
			}

			//use new token
			$token = $newToken;

			$user = UserModel::findIdentityByAccessToken($token);
			if ($user == null)
				throw new ForbiddenHttpException('Invalid token');

			Yii::$app->user->login($user, 3600*24*30); //$this->rememberMe ? 3600*24*30 : 0);

			//
			return parent::runAction($id, $params);
		}
	}

}
