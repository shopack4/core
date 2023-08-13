<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\components;

use Yii;
use yii\web\User as BaseUser;
use shopack\base\frontend\helpers\PrivHelper;

class User extends BaseUser
{
	public $identityClass = \shopack\aaa\frontend\common\models\UserModel::class;
	public $enableAutoLogin = true;
	public $loginUrl = ['aaa/auth/login'];
	public $autoRenewCookie = false;

	public $identityCookie = [
		'name' => '_jwt',
		'httpOnly' => true,
	// 	// 'sameSite' => '', //\yii\web\Cookie::SAME_SITE_STRICT,
	];

	//current sessions jwt token dataset
	public ?\Lcobucci\JWT\Token\Plain $accessToken = null;

	public function getJwtByCookie()
	{
    $cookieName = $this->identityCookie['name'];
    $authCookie = Yii::$app->request->getCookies()->getValue($cookieName);

    if ($authCookie == null)
			return null;

		$authCookie = json_decode($authCookie, true);
		$authCookie = $authCookie[1];

		// $jwtPayload = explode('.', $authCookie);
		// $jwtPayload = $jwtPayload[1];
		// $jwtPayload = base64_decode($jwtPayload);
		// $jwtPayload = json_decode($jwtPayload, true);

		// if ($this->validateJwtPayload($jwtPayload) == false) {
		// 	//is remember me checked? -> renew jwt
		// 	if ($jwtPayload['rmmbr'] ?? 0) {

		// 	}

		// 	//else: login again
		// 	return null;
		// }

		return $authCookie;
	}

	public function validateJwtPayload($jwtPayload)
	{
		if (empty($jwtPayload))
			return false;

		if (empty($jwtPayload['exp']))
			return false;

		$exp = $jwtPayload['exp'];
		$exp = number_format((float)$exp, 6, '.', '');
		$exp = \DateTimeImmutable::createFromFormat('U.u', $exp);

		$now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

		return ($now < $exp);
	}

	protected function regenerateCsrfToken()
	{
		$request = Yii::$app->getRequest();
		if ($request->enableCsrfCookie || $this->enableSession) {
			/*-*/ // $request->getCsrfToken(true);
			/*+*/ $request->getCsrfToken(false);
		}
	}

	public function loginByAccessToken($token, $type = null)
	{
		$identity = parent::loginByAccessToken($token, $type);

		if ($identity)
			$this->accessToken = Yii::$app->jwt->parse($token);

		return $identity;
	}

	public function hasPriv($path, $priv='1')
	{
		return PrivHelper::hasPriv($path, $priv);
	}

}
