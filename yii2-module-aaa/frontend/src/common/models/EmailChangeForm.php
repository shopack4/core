<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use yii\base\Model;
use yii\web\UnprocessableEntityHttpException;
use shopack\base\common\helpers\HttpHelper;

class EmailChangeForm extends Model
{
  public $email;

  public function rules()
  {
    return [
      ['email', 'required'],
    ];
  }

  public function attributeLabels()
	{
		return [
			'email' => Yii::t('aaa', 'Email'),
		];
	}

  public function process()
  {
    Yii::$app->user->assertIsNotGuest();

    if ($this->validate() == false)
      throw new UnprocessableEntityHttpException(implode("\n", $this->getFirstErrors()));

    $apiResponse = HttpHelper::callApi('aaa/user/email-change',
      HttpHelper::METHOD_POST,
      [],
      [
        'email' => $this->email,
      ]
    );

    HttpHelper::throwApiResponseIfFailed($apiResponse, 'aaa');

    return true;
  }

}
