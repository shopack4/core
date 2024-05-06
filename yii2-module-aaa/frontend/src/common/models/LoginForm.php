<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use yii\base\Model;
use shopack\base\common\helpers\HttpHelper;

class LoginForm extends Model
{
  public $username;
  public $password;
  public $rememberMe = true;
  // public $challenge;

  public function rules()
  {
    return [
      [['username', 'password'], 'required'],
      ['rememberMe', 'boolean'],
    ];
  }

	public function attributeLabels()
	{
		return [
			'username' => Yii::t('aaa', 'SSID / Mobile / Email'),
			'password' => Yii::t('aaa', 'Password'),
			'rememberMe' => Yii::t('aaa', 'Remember Me'),
		];
	}

  public function login()
  {
    if ($this->validate() == false)
      return false;

    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/auth/login',
      HttpHelper::METHOD_POST,
      [],
      [
        'input' => $this->username,
        'password' => $this->password,
        'rememberMe' => $this->rememberMe,
      ]
    );

    if (isset($resultData['token'])) {
      $token = $resultData['token'];
      $user = UserModel::findIdentityByAccessToken($token);
      if ($user == null)
        throw new \yii\web\ForbiddenHttpException('Invalid token');

      return Yii::$app->user->login($user, 3600*24*30); //$this->rememberMe ? 3600*24*30 : 0);
    }

    // if (isset($resultData['challenge'])) {
    //   $this->challenge = $resultData['challenge'];
    //   return 'challenge';
    // }

    return [$resultStatus, $resultData];
  }

}
