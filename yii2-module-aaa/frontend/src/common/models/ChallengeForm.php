<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use yii\base\Model;
use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\frontend\common\helpers\Html;
use yii\web\ForbiddenHttpException;

class ChallengeForm extends Model
{
  public $realm;
  public $token;
  // public $type;
  // public $key;
  public $code;
  // public $login = true;
  // public $rememberMe = true;

  public function rules()
  {
    return [
      [[
        'realm',
        'token',
        // 'type',
        // 'key',
        'code',
      ], 'required'],
      // ['rememberMe', 'boolean'],
    ];
  }

	public function attributeLabels()
	{
		return [
			// 'key' => Yii::t('aaa', 'Key'),
			'code' => Yii::t('aaa', 'Code'),
      // 'rememberMe' => Yii::t('aaa', 'Remember Me'),
		];
	}

  private $_challengeData = null;
  public function challengeData()
  {
    if ($this->_challengeData === null) {
      $this->_challengeData = json_decode(base64_decode(explode('.', $this->token)[1]), true);
    }

    return $this->_challengeData;
  }

  public function redirectToChallenge($donelink = null)
  {
    $request = Yii::$app->getRequest();
    $csrfMetaTags = Html::csrfMetaTags();
    $csrfToken = $request->getCsrfToken();
    $challengeUrl = Url::to(['challenge', 'donelink' => $donelink]);
    $formName = $this->formName();

    $html =<<<HTML
<html>
  <head>
    {$csrfMetaTags}
  </head>
  <body onload="document.redirectform.submit()">
    <form method="POST" action="{$challengeUrl}" name="redirectform">
      <input type="hidden" name="{$request->csrfParam}" value="{$csrfToken}">
      <input type="hidden" name="{$formName}[realm]" value="{$this->realm}">
      <input type="hidden" name="{$formName}[token]" value="{$this->token}">
    </form>
  </body>
  </html>
HTML;
    // <input type="hidden" name="{$formName}[type]" value="{$this->type}">
    // <input type="hidden" name="{$formName}[key]" value="{$this->key}">
    // <input type="hidden" name="{$formName}[rememberMe]" value="{$this->rememberMe}">

    Yii::$app->controller->layout = false;
    return Yii::$app->controller->renderContent($html);
  }

  public function process()
  {
    if ($this->validate() == false)
      return false;

    // if ($this->login) {
    //   $params['rememberMe'] = $this->rememberMe;
    // }

    // $parts = explode('.', $this->token);
    // $parts[2] = 'aaaa';
    // $this->token = implode('.', $parts);

    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/auth/challenge',
      HttpHelper::METHOD_POST,
      [],
      [
        // 'key' => $this->key,
        'token' => $this->token,
        'code' => $this->code,
      ]
    );

    // HttpHelper::throwResultIfFailed('aaa', $resultStatus, $resultData);

    if (isset($resultData['token'])) {
      $token = $resultData['token'];
      $user = UserModel::findIdentityByAccessToken($token);
      if ($user == null)
        throw new ForbiddenHttpException('Invalid token');

      return Yii::$app->user->login($user, 3600*24*30); //$this->rememberMe ? 3600*24*30 : 0);
    }

    return [$resultStatus, $resultData];
  }

  public function getTimerInfo()
  {
		$challengeData = $this->challengeData();
    $key = $challengeData['email'] ?? $challengeData['mobile'] ?? $challengeData['ssid'];

    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/auth/challenge-timer-info',
      HttpHelper::METHOD_POST,
      [],
      [
        'input' => $key,
      ]
    );

    HttpHelper::throwResultIfFailed('aaa', $resultStatus, $resultData);

    return $resultData['result'];
  }

  public function resend()
  {
    //valid if ['mobile'] exists in token
		$challengeData = $this->challengeData();
    $key = $challengeData['email'] ?? $challengeData['mobile'] ?? $challengeData['ssid'];

    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/auth/request-approval-code',
      HttpHelper::METHOD_POST,
      [],
      [
        'input' => $key,
      ]
    );

    HttpHelper::throwResultIfFailed('aaa', $resultStatus, $resultData);

    return [$resultStatus, $resultData['result']];
  }

}
