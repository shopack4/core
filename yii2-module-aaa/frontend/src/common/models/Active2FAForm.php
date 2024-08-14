<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use yii\base\Model;
use yii\web\UnprocessableEntityHttpException;
use shopack\base\common\helpers\HttpHelper;

class Active2FAForm extends Model
{
  public $type;
  public $code;
  public $userModel;

  public function rules()
  {
    return [
      ['type', 'required'],
      ['code', 'required'],
    ];
  }

  public function attributeLabels()
	{
		return [
			'code' => Yii::t('aaa', 'Code'),
		];
	}

  public function process()
  {
    Yii::$app->user->assertIsNotGuest();

    if ($this->validate() == false)
      throw new UnprocessableEntityHttpException(implode("\n", $this->getFirstErrors()));

    $apiResponse = HttpHelper::callApi('aaa/user/active-2fa',
      HttpHelper::METHOD_POST,
      [],
      [
        'type' => $this->type,
        'code' => $this->code,
      ]
    );

    HttpHelper::throwApiResponseIfFailed($apiResponse, 'aaa');

    return true;
  }

  public function generate()
  {
    $apiResponse = HttpHelper::callApi('aaa/user/generate-2fa-activation-code',
      HttpHelper::METHOD_POST,
      [],
      [
        'type' => $this->type,
      ]
    );

    HttpHelper::throwApiResponseIfFailed($apiResponse, 'aaa');

    return [$apiResponse['status'], $apiResponse['body']];
  }

}
