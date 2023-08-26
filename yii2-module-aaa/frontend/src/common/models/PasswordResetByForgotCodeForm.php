<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use yii\base\Model;
use yii\web\UnauthorizedHttpException;
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
      throw new UnauthorizedHttpException(implode("\n", $this->getFirstErrors()));

    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/auth/password-reset-by-forgot-code',
      HttpHelper::METHOD_POST,
      [],
      [
        'input' => $this->input,
        'code'  => $this->code,
        'newPassword' => $this->newPassword,
      ]
    );

    if ($resultStatus < 200 || $resultStatus >= 300) {
      return [$resultStatus, $resultData];
      // throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));
    }

    return true; //[$resultStatus, $resultData['result']];
  }

  public function getTimerInfo()
  {
    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/auth/forgot-password-timer-info',
      HttpHelper::METHOD_POST,
      [],
      [
        'input' => $this->input,
      ]
    );

    if ($resultStatus < 200 || $resultStatus >= 300)
      throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));

    return $resultData['result'];
  }

  public function resend()
  {
    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/auth/request-forgot-password',
      HttpHelper::METHOD_POST,
      [],
      [
        'input' => $this->input,
      ]
    );

    if ($resultStatus < 200 || $resultStatus >= 300)
      throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));

    return [$resultStatus, $resultData['result']];
  }

}
