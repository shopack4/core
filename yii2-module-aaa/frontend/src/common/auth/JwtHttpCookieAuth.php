<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\auth;

use Yii;

class JwtHttpCookieAuth extends \yii\filters\auth\AuthMethod
{
  // public $cookieName = 'token';
  public $pattern; // = '/^Bearer\s+(.*?)$/';
  // public $realm = 'api';

  public function authenticate($user, $request, $response)
  {
    $authCookie = Yii::$app->user->getJwtByCookie();
    if ($authCookie !== null) {
      if ($this->pattern !== null) {
        if (preg_match($this->pattern, $authCookie, $matches)) {
          $authCookie = $matches[1];
        } else {
          return null;
        }
      }

      $identity = $user->loginByAccessToken($authCookie, get_class($this));
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
    return $response->redirect(\Yii::$app->user->loginUrl);
  }

}
