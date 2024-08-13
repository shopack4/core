<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\auth;

use shopack\base\common\helpers\HttpHelper;
use Yii;

class AuthHelper
{
	public static function refreshToken($token)
  {
		$apiRefreshTokenAddress = Yii::$app->params['apiRefreshTokenAddress'] ?? null;

    list ($resultStatus, $resultData) = HttpHelper::callApi($apiRefreshTokenAddress,
      HttpHelper::METHOD_POST,
      [],
      [
        'token' => $token,
      ]
    );

    if ($resultStatus == 401)
      return null; //relogin

    if ($resultStatus < 200 || $resultStatus >= 300)
      return false; //retry

    return $resultData['token'] ?? false;
  }

}
