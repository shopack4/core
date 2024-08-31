<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use yii\base\Model;
use yii\web\ForbiddenHttpException;
use shopack\base\common\helpers\HttpHelper;

class LoginByMobileForm extends Model
{
	const ERROR_MOBILE_NOT_EXISTS = 'ERROR_MOBILE_NOT_EXISTS';

  const STEP_MOBILE = 'Mobile';
  const STEP_CODE = 'Code';

  public $step = self::STEP_MOBILE;

  public $mobile;
  public $code;
  public $rememberMe = true;
  public $signupIfNotExists;

  public function rules()
  {
    $bodyParams = Yii::$app->request->getBodyParams();
		$formName = strtolower($this->formName());

    return [
      ['step', 'required'],
      ['mobile', 'required'],

      // ['resend', 'safe'],
      ['code', 'string'],
      ['code', 'required',
        'when' => function ($model) use($bodyParams) {
          return (($model->step == self::STEP_CODE) && (($bodyParams['resend'] ?? 0) == 0));
        },
        'whenClient' => "function (attribute, value) {
          return (($('#{$formName}-step').val() == '" . self::STEP_CODE . "') && ($('#resend').val() == 0));
        }"
      ],

      ['rememberMe', 'boolean'],
      ['signupIfNotExists', 'boolean'],
    ];
  }

	public function attributeLabels()
	{
		return [
			'mobile' => Yii::t('aaa', 'Mobile'),
			'code' => Yii::t('aaa', 'Code'),
			'rememberMe' => Yii::t('aaa', 'Remember Me'),
		];
	}

  public function process()
  {
    if ($this->validate() == false)
      return false;

    $bodyParams = Yii::$app->request->getBodyParams();
    if (($this->step == self::STEP_CODE) && ($bodyParams['resend'] == 1)) {
      $apiResponse = HttpHelper::callApi('aaa/auth/request-approval-code',
        HttpHelper::METHOD_POST,
        [],
        [
          'input' => $this->mobile,
        ]
      );
      $apiResponse['body'] = $apiResponse['body']['result'];
    } else {
      $apiResponse = HttpHelper::callApi('aaa/auth/login-by-mobile',
        HttpHelper::METHOD_POST,
        [],
        [
          'mobile' => $this->mobile,
          'code' => $this->code,
          'rememberMe' => $this->rememberMe,
          'signupIfNotExists' => $this->signupIfNotExists,
        ]
      );

      // if ($apiResponse['status'] == 200) {
      //   return [
      //     'resultStatus' => $apiResponse['status'],
      //     'resultData' => $apiResponse['body'],
      //     // 'next' => self::STEP_CODE,
      //   ];
      // }
    // } else {
    //   $apiResponse = HttpHelper::callApi('aaa/auth/challenge',
    //     HttpHelper::METHOD_POST,
    //     [],
    //     [
    //       'key' => $this->mobile,
    //       'value' => $this->code,
    //       'rememberMe' => $this->rememberMe,
    //     ]
    //   );
    }
    // $timerInfo = [
    //   'ttl' => $apiResponse['body']['ttl'],
    //   'remained' => $apiResponse['body']['remained'],
    // ];

    if (isset($apiResponse['body']['token'])) {
      $token = $apiResponse['body']['token'];
      $user = UserModel::findIdentityByAccessToken($token);
      if ($user == null)
        throw new ForbiddenHttpException('Invalid token');

      return Yii::$app->user->login($user, 3600*24*30); //$this->rememberMe ? 3600*24*30 : 0);
    }

    if (isset($apiResponse['body']['challenge'])) {
      return $apiResponse['body'];
    }

    $res = [
      'resultStatus' => $apiResponse['status'],
      // 'resultHeaders' => $resultHeaders,
      'resultData' => $apiResponse['body'],
    ];

    if (($this->step == self::STEP_MOBILE) && ($apiResponse['status'] == 200)) {
      $res['next'] = self::STEP_CODE;
    }

    return $res;
  }

  public function getTimerInfo()
  {
    $apiResponse = HttpHelper::callApi('aaa/auth/challenge-timer-info',
      HttpHelper::METHOD_POST,
      [],
      [
        'input' => $this->mobile,
      ]
    );

    HttpHelper::throwApiResponseIfFailed($apiResponse, 'aaa');

    return $apiResponse['body']['result'];
  }

}
