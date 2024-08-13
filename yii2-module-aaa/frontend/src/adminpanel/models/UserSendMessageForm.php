<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\models;

use Yii;
use yii\base\Model;
use yii\web\UnprocessableEntityHttpException;
use shopack\base\common\helpers\HttpHelper;

class UserSendMessageForm extends Model
{
  public $userID;
  public $message;

  public function rules()
  {
    return [
      // ['postback', 'required'],
      [['userID',
				'message',
			], 'required'],
    ];
  }

  public function attributeLabels()
	{
		return [
			'userID'  => Yii::t('mha', 'User'),
			'message'   => Yii::t('aaa', 'Message'),
		];
	}

  public function process()
  {
    if ($this->validate() == false)
      throw new UnprocessableEntityHttpException(implode("\n", $this->getFirstErrors()));

    $apiResponse = HttpHelper::callApi('aaa/user/send-message',
      HttpHelper::METHOD_POST,
      [],
      [
				'userID'  => $this->userID,
				'message' => $this->message,
			]
    );

    HttpHelper::throwApiResponseIfFailed($apiResponse, 'aaa');

    return true;
  }

}
