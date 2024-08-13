<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use yii\base\Model;
use shopack\base\common\helpers\HttpHelper;
use yii\web\ForbiddenHttpException;

class SignupForm extends Model
{
  public $email;
  public $password;
  public $retypePassword;
  public $mobile;
  public $rememberMe = true;

  public function rules()
  {
    return [
      [[
        'email',
        'mobile',
        'password',
        'retypePassword',
      ], 'string'],

      [[
        'email',
        'mobile',
        'password',
        'retypePassword',
      ], 'required'],

      [
        'retypePassword',
        'compare',
        'compareAttribute' => 'password',
        'message' => Yii::t('aaa', "Passwords don't match"),
      ],

      ['rememberMe', 'boolean'],
    ];
  }

	public function attributeLabels()
	{
		return [
			'email'           => Yii::t('aaa', 'Email'),
			'password'        => Yii::t('aaa', 'Password'),
			'retypePassword'  => Yii::t('aaa', 'Retype Password'),
			'rememberMe'      => Yii::t('aaa', 'Remember Me'),
			'mobile'          => Yii::t('aaa', 'Mobile'),
		];
	}

  public function process()
  {
    if ($this->validate() == false)
      return false;

    $apiResponse = HttpHelper::callApi('aaa/auth/signup',
      HttpHelper::METHOD_POST,
      [],
      [
        'email'    => $this->email,
        'mobile'   => $this->mobile,
        'password' => $this->password,
      ]
    );

    if (isset($apiResponse['data']['token'])) {
      $token = $apiResponse['data']['token'];
      $user = UserModel::findIdentityByAccessToken($token);
      if ($user == null)
        throw new ForbiddenHttpException('Invalid token');

      return Yii::$app->user->login($user, 3600*24*30); //$this->rememberMe ? 3600*24*30 : 0);
    }

    return [$apiResponse['status'], $apiResponse['data']];
  }

}
