<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\auth;

use Yii;
use shopack\base\common\helpers\Url;

class JwtHttpCookieAuth extends \yii\filters\auth\AuthMethod
{
  // public $cookieName = 'token';
  public $pattern; // = '/^Bearer\s+(.*?)$/';
  // public $realm = 'api';

  public function authenticate($user, $request, $response)
  {
    $token = Yii::$app->user->getJwtByCookie();
    if ($token !== null) {
      if ($this->pattern !== null) {
        if (preg_match($this->pattern, $token, $matches)) {
          $token = $matches[1];
        } else {
          return null;
        }
      }

      //validate
      /*
      $parsedToken = Yii::$app->jwt->parse($token);
      $jwtPayload = $parsedToken->claims()->all();
      $sessionid = $jwtPayload['jti'];
      if (Yii::$app->cache->)
      $exp = $jwtPayload['exp'];
      if (($exp instanceof \DateTimeImmutable) == false) {
        $exp = number_format((float)$exp, 6, '.', '');
        $exp = \DateTimeImmutable::createFromFormat('U.u', $exp);
      }
      $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
      if ($now >= $exp) { //expired -> refresh token
      }
      */

      //
      $identity = $user->loginByAccessToken($token, get_class($this));
      if ($identity === null) {
        $this->challenge($response);
        $this->handleFailure($response);
      }

      return $identity;
    }

    return null;
  }

  // public function challenge($response)
  // {
    // $response->getHeaders()->set('WWW-Authenticate', "Bearer realm=\"{$this->realm}\"");
  // }

  public function handleFailure($response)
  {
    $loginUrl = (array)Yii::$app->user->loginUrl;

    $loginUrl['donelink'] = Url::current([], true);

    return $response->redirect($loginUrl);
  }

}
