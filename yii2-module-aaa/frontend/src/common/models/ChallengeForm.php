<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use yii\base\Model;
use shopack\base\common\helpers\HttpHelper;

class ChallengeForm extends Model
{
  public $realm;
  public $type;
  public $key;
  public $value;
  public $login = true;
  public $rememberMe = true;

  public function rules()
  {
    return [
      [[
        'realm',
        'type',
        'key',
        'value',
      ], 'required'],
      ['rememberMe', 'boolean'],
    ];
  }

	public function attributeLabels()
	{
		return [
			'key' => Yii::t('aaa', 'Key'),
			'value' => Yii::t('aaa', 'Code'),
      'rememberMe' => Yii::t('aaa', 'Remember Me'),
		];
	}

  public function process()
  {
    if ($this->validate() == false)
      return false;

    $params = [
      'key' => $this->key,
      'value' => $this->value,
    ];

    if ($this->login) {
      // $params['login'] = [
      //   'rememberMe' => $this->rememberMe,
      // ];

      $params['rememberMe'] = $this->rememberMe;
    }

    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/auth/challenge',
      HttpHelper::METHOD_POST,
      [],
      $params
    );

    // if ($resultStatus < 200 || $resultStatus >= 300)
    //   throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));

    if (isset($resultData['token'])) {
      $token = $resultData['token'];
      $user = UserModel::findIdentityByAccessToken($token);
      if ($user == null)
        throw new \yii\web\ForbiddenHttpException('Invalid token');

      return Yii::$app->user->login($user, 3600*24*30); //$this->rememberMe ? 3600*24*30 : 0);
    }

    return [$resultStatus, $resultData];
  }

  public function getTimerInfo()
  {
    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/auth/challenge-timer-info',
      HttpHelper::METHOD_POST,
      [],
      [
        'input' => $this->key,
      ]
    );

    if ($resultStatus < 200 || $resultStatus >= 300)
      throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));

    return $resultData['result'];
  }

  public function resend()
  {
    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/auth/request-approval-code',
      HttpHelper::METHOD_POST,
      [],
      [
        'input' => $this->key,
      ]
    );

    if ($resultStatus < 200 || $resultStatus >= 300)
      throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));

    return [$resultStatus, $resultData['result']];
  }

}
