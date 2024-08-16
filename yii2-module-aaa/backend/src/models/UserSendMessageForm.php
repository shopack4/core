<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

 namespace shopack\aaa\backend\models;

use yii\base\Model;
use yii\web\UnprocessableEntityHttpException;
use shopack\aaa\backend\models\MessageModel;
use shopack\aaa\common\enums\enuGender;

class UserSendMessageForm extends Model
{
  public $userID;
  public $message;

  public function rules()
  {
    return [
      [['userID',
				'message',
			], 'required'],
    ];
  }

  public function process()
  {
    if ($this->validate() == false)
      throw new UnprocessableEntityHttpException(implode("\n", $this->getFirstErrors()));

    $userModel = UserModel::find()
      ->andWhere(['usrID' => $this->userID])
      // ->andWhere(['IS', 'usrMobile', DbExpression::notNull()])
      // ->andWhere(['IS', 'usrMobileApprovedAt', DbExpression::notNull()])
      // ->andWhere(['!=', 'usrStatus', enuUserStatus::Removed])
      ->asArray()
      ->one();

    if (empty($userModel))
      throw new UnprocessableEntityHttpException('User not found');

    if (empty($userModel['usrMobile']))
      throw new UnprocessableEntityHttpException('Mobile not defined');

    $memberFullName = [];
    if ((empty($userModel['usrGender']) == false)
        && ($userModel['usrGender'] != enuGender::NotSet))
      $memberFullName[] = enuGender::getAbrLabel($userModel['usrGender']);
    if (empty($userModel['usrFirstName']) == false)
      $memberFullName[] = $userModel['usrFirstName'];
    if (empty($userModel['usrLastName']) == false)
      $memberFullName[] = $userModel['usrLastName'];
    $memberFullName = implode(' ', $memberFullName);

    $messageTemplate = 'rawMessage';

    $messageModel = new MessageModel;
    $messageModel->sendNow = false;
    $messageModel->msgUserID   = $userModel['usrID'];
    $messageModel->msgTypeKey  = $messageTemplate;
    $messageModel->msgTarget   = $userModel['usrMobile'];
    $messageModel->msgInfo     = [
      // 'mobile'    => $userModel['usrMobile'],
      // 'gender'    => $userModel['usrGender'],
      // 'firstName' => $userModel['usrFirstName'],
      // 'lastName'  => $userModel['usrLastName'],
      'user'    => $memberFullName,
      'message' => $this->message,
    ];
    $messageModel->msgIssuer   = 'aaa:user:sendMessage';

    if ($messageModel->save() == false) {

    }

    return true;
  }

}
