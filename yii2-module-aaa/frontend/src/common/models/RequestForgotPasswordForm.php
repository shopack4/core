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

class RequestForgotPasswordForm extends Model
{
  public $input;

  public function rules()
  {
    return [
      ['input', 'required'],
    ];
  }

  public function attributeLabels()
	{
		return [
			'input' => Yii::t('aaa', 'SSID / Mobile / Email'),
		];
	}

  public function process()
  {
    if ($this->validate() == false)
      throw new UnauthorizedHttpException(implode("\n", $this->getFirstErrors()));

    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/auth/request-forgot-password',
      HttpHelper::METHOD_POST,
      [],
      [
        'input' => $this->input,
      ]
    );

    HttpHelper::throwResultIfFailed('aaa', $resultStatus, $resultData);

    return true; //[$resultStatus, $resultData['result']];
  }

}
