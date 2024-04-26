<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\models;

use Yii;
use yii\base\Model;
use yii\web\HttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\web\NotFoundHttpException;
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
      throw new UnauthorizedHttpException(implode("\n", $this->getFirstErrors()));

    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/user/send-message',
      HttpHelper::METHOD_POST,
      [],
      [
				'userID'  => $this->userID,
				'message' => $this->message,
			]
    );

    if ($resultStatus < 200 || $resultStatus >= 300)
      throw new HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));

    return true; //[$resultStatus, $resultData['result']];
  }

}
