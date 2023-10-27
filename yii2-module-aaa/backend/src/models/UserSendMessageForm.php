<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

 namespace shopack\aaa\backend\models;

use Yii;
use yii\db\Expression;
use yii\base\Model;
use yii\web\UnauthorizedHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\web\NotFoundHttpException;
use shopack\base\common\helpers\HttpHelper;
use shopack\aaa\backend\models\MessageModel;
use shopack\aaa\common\enums\enuGender;
use shopack\aaa\common\enums\enuUserStatus;

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
      throw new UnauthorizedHttpException(implode("\n", $this->getFirstErrors()));

    $userModel = UserModel::find()
      ->andWhere(['usrID' => $this->userID])
      // ->andWhere(['IS', 'usrMobile', new Expression('NOT NULL')])
      // ->andWhere(['IS', 'usrMobileApprovedAt', new Expression('NOT NULL')])
      // ->andWhere(['!=', 'usrStatus', enuUserStatus::Removed])
      ->asArray()
      ->one();

    if (empty($userModel))
      throw new UnauthorizedHttpException('User not found');

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
