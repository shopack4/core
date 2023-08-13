<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\auth;

use Yii;
use shopack\aaa\frontend\common\auth\JwtHttpCookieAuth;

class BaseController extends \shopack\base\frontend\classes\BaseController
{
	const BEHAVIOR_AUTHENTICATOR = 'authenticator';

	public function behaviors()
	{
		$behaviors = parent::behaviors();

		$behaviors[static::BEHAVIOR_AUTHENTICATOR] = [
			'class' => JwtHttpCookieAuth::class,
		];

		return $behaviors;
	}

	public function runAction($id, $params = [])
	{
		try {
			return parent::runAction($id, $params);

		} catch (\Throwable $th) {
			if ($th->getMessage() == 'Your request was made with invalid or expired JSON Web Token.')
				return $this->redirect(\Yii::$app->user->loginUrl);

			throw $th;
		}
	}

}
