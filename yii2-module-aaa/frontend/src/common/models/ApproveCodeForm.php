<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use yii\base\Model;
use shopack\base\common\helpers\HttpHelper;

class ApproveCodeForm extends Model
{
  public $keyType;
  public $input;
  public $code;

  public function rules()
  {
    return [
      ['input', 'required'],
      ['code', 'required'],
    ];
  }

  public function attributeLabels()
	{
		return [
			'input' => Yii::t('aaa', 'Input'),
			'code' => Yii::t('aaa', 'Code'),
		];
	}

  public function attributeHints()
  {
		return [
			'code' => 'کد ارسال شده را وارد کنید',
		];
	}

  public function process()
  {
    if ($this->validate() == false)
      return false;

    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/auth/accept-approval',
      HttpHelper::METHOD_POST,
      [],
      [
        'input' => $this->input,
        'code' => $this->code,
      ]
    );

    // if (isset($resultData['keyType']))
    //   $this->keyType = $resultData['keyType'];

    if ($resultStatus < 200 || $resultStatus >= 300) {
      return [$resultStatus, $resultData];
      // throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));
    }

    //check result['token'] -> set cookie
    if (array_key_exists('token', $resultData)) {
      if ($resultData['token'] == null) {
        Yii::$app->user->logout();
      }
    }

    return true; //[$resultStatus, $resultData['result']];
  }

  public function getTimerInfo()
  {
    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/auth/challenge-timer-info',
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
    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/auth/request-approval-code',
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
