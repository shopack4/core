<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use yii\base\Model;
use yii\web\UnprocessableEntityHttpException;
use shopack\base\common\helpers\HttpHelper;

class PasswordResetByForgotCodeForm extends Model
{
	public $input;
	public $code;
	public $newPassword;
	public $retypePassword;

  public function rules()
  {
    return [
      [['input', 'code', 'newPassword', 'retypePassword'], 'string'],
      [['input', 'code', 'newPassword', 'retypePassword'], 'required'],

      ['retypePassword', 'compare',
        'compareAttribute' => 'newPassword',
        'message' => Yii::t('aaa', "Passwords don't match"),
      ],
    ];
  }

  public function attributeLabels()
	{
		return [
			'code' => Yii::t('aaa', 'Code'),
      'newPassword'    => Yii::t('aaa', 'New Password'),
      'retypePassword' => Yii::t('aaa', 'Retype Password'),
		];
	}

  public function process()
  {
    if ($this->validate() == false)
      throw new UnprocessableEntityHttpException(implode("\n", $this->getFirstErrors()));

    $apiResponse = HttpHelper::callApi('aaa/auth/password-reset-by-forgot-code',
      HttpHelper::METHOD_POST,
      [],
      [
        'input' => $this->input,
        'code'  => $this->code,
        'newPassword' => $this->newPassword,
      ]
    );

    if ($apiResponse['status'] < 200 || $apiResponse['status'] >= 300) {
      return [$apiResponse['status'], $apiResponse['body']];
			// HttpHelper::throwApiResponseIfFailed($apiResponse, 'aaa');
    }

    return true;
  }

  public function getTimerInfo()
  {
    $apiResponse = HttpHelper::callApi('aaa/auth/forgot-password-timer-info',
      HttpHelper::METHOD_POST,
      [],
      [
        'input' => $this->input,
      ]
    );

    HttpHelper::throwApiResponseIfFailed($apiResponse, 'aaa');

    return $apiResponse['body']['result'];
  }

  public function resend()
  {
    $apiResponse = HttpHelper::callApi('aaa/auth/request-forgot-password',
      HttpHelper::METHOD_POST,
      [],
      [
        'input' => $this->input,
      ]
    );

    HttpHelper::throwApiResponseIfFailed($apiResponse, 'aaa');

    return [$apiResponse['status'], $apiResponse['body']['result']];
  }

}
