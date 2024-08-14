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

    $apiResponse = HttpHelper::callApi('aaa/auth/accept-approval',
      HttpHelper::METHOD_POST,
      [],
      [
        'input' => $this->input,
        'code' => $this->code,
      ]
    );

    // if (isset($apiResponse['body']['keyType']))
    //   $this->keyType = $apiResponse['body']['keyType'];

    if ($apiResponse['status'] < 200 || $apiResponse['status'] >= 300) {
      return [$apiResponse['status'], $apiResponse['body']];
			// HttpHelper::throwApiResponseIfFailed($apiResponse, 'aaa');
    }

    //check result['token'] -> set cookie
    if (array_key_exists('token', $apiResponse['body'])) {
      if ($apiResponse['body']['token'] == null) {
        Yii::$app->user->logout();
      }
    }

    return true;
  }

  public function getTimerInfo()
  {
    $apiResponse = HttpHelper::callApi('aaa/auth/challenge-timer-info',
      HttpHelper::METHOD_POST,
      [],
      [
        'input' => $this->input,
      ]
    );

    HttpHelper::throwApiResponseIfFailed($apiResponse, 'aaa');

    return $$apiResponse['body']['result'];
  }

  public function resend()
  {
    $apiResponse = HttpHelper::callApi('aaa/auth/request-approval-code',
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
