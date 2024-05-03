<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use yii\base\Model;
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
  public $challenge;
  public $signupIfNotExists;
  // public $resend;

  public function rules()
  {
		$formName = strtolower($this->formName());

    return [
      ['step', 'required'],
      ['mobile', 'required'],

      // ['resend', 'safe'],
      ['code', 'string'],
      ['code', 'required',
        'when' => function ($model) {
          return (($model->step == self::STEP_CODE) && (($_POST['resend'] ?? 0) == 0));
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

    if ($this->step == self::STEP_MOBILE) {
      list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/auth/login-by-mobile',
        HttpHelper::METHOD_POST,
        [],
        [
          'mobile' => $this->mobile,
          // 'code' => $this->code,
          'rememberMe' => $this->rememberMe,
          'signupIfNotExists' => $this->signupIfNotExists,
        ]
      );

      if ($resultStatus == 200) {
        return [
          'resultStatus' => $resultStatus,
          'resultData' => $resultData,
          'next' => self::STEP_CODE,
        ];
      }

    } else if ($this->step == self::STEP_CODE) {
      if ($_POST['resend'] == 1) {
        list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/auth/request-approval-code',
          HttpHelper::METHOD_POST,
          [],
          [
            'input' => $this->mobile,
          ]
        );
        $resultData = $resultData['result'];
      } else {
        list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/auth/challenge',
          HttpHelper::METHOD_POST,
          [],
          [
            'key' => $this->mobile,
            'value' => $this->code,
            'rememberMe' => $this->rememberMe,
          ]
        );
      }
    }
    // $timerInfo = [
    //   'ttl' => $resultData['ttl'],
    //   'remained' => $resultData['remained'],
    // ];

    if (isset($resultData['token'])) {
      $token = $resultData['token'];
      $user = UserModel::findIdentityByAccessToken($token);
      if ($user == null)
        throw new \yii\web\ForbiddenHttpException('Invalid token');

      return Yii::$app->user->login($user, 3600*24*30); //$this->rememberMe ? 3600*24*30 : 0);
    }

    if (isset($resultData['challenge'])) {
      $this->challenge = $resultData['challenge'];
      return 'challenge';
    }

    return [
      'resultStatus' => $resultStatus,
      'resultData' => $resultData,
    ];
  }

  public function getTimerInfo()
  {
    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/auth/challenge-timer-info',
      HttpHelper::METHOD_POST,
      [],
      [
        'input' => $this->mobile,
      ]
    );

    if ($resultStatus < 200 || $resultStatus >= 300)
      throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));

    return $resultData['result'];
  }

}
