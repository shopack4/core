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

    $apiResponse = HttpHelper::callApi($apiRefreshTokenAddress,
      HttpHelper::METHOD_POST,
      [],
      [
        'token' => $token,
      ]
    );

    if ($apiResponse['status'] == 401)
      return null; //relogin

    if ($apiResponse['status'] < 200 || $apiResponse['status'] >= 300)
      return false; //retry

    return $apiResponse['body']['token'] ?? false;
  }

}
