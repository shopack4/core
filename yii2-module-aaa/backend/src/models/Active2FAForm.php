<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

 namespace shopack\aaa\backend\models;

use Yii;
use yii\base\Model;
use yii\web\UnauthorizedHttpException;
use yii\web\UnprocessableEntityHttpException;
use shopack\base\backend\helpers\AuthHelper;
use shopack\aaa\backend\models\ApprovalRequestModel;
use shopack\base\common\helpers\PhoneHelper;

class Active2FAForm extends Model
{
  public $type;
  public $code;

  public function rules()
  {
    return [
      ['type', 'required'],
      ['code', 'required'],
    ];
  }

  public function generate()
  {
    if (Yii::$app->user->isGuest)
      throw new UnauthorizedHttpException("This process is not for guest.");

    if (empty($this->type))
      throw new UnprocessableEntityHttpException("Type is empty");

    return Yii::$app->twoFAManager->generate($this->type, Yii::$app->user->id);
  }

  public function process()
  {
    if (Yii::$app->user->isGuest)
      throw new UnauthorizedHttpException("This process is not for guest.");

    if ($this->validate() == false)
      throw new UnauthorizedHttpException(implode("\n", $this->getFirstErrors()));

    $userModel = UserModel::findOne(Yii::$app->user->id);

    if (isset($userModel->usr2FA[$this->type]))
      throw new UnprocessableEntityHttpException('This authentication method already activated');

    Yii::$app->twoFAManager->validate($this->type, Yii::$app->user->id, [
      'code' => $this->code
    ]);

    $tfa = $userModel->usr2FA ?? [];
    $tfa[$this->type] = 1;
    $userModel->usr2FA = $tfa;

    if ($userModel->save() == false) {
      throw new UnprocessableEntityHttpException("could not save user\n" . implode("\n", $userModel->getFirstErrors()));
    }

    return true;
  }

  public static function inactive2FA($type)
  {
    if (Yii::$app->user->isGuest)
      throw new UnauthorizedHttpException("This process is not for guest.");

    $userModel = UserModel::findOne(Yii::$app->user->id);

    if (isset($userModel->usr2FA[$type]) == false)
      throw new UnprocessableEntityHttpException('This authentication method not activated');

    $tfa = $userModel->usr2FA ?? [];
    unset($tfa[$type]);
    $userModel->usr2FA = $tfa;

    if ($userModel->save() == false) {
      throw new UnprocessableEntityHttpException("could not save user\n" . implode("\n", $userModel->getFirstErrors()));
    }

    return true;
  }

}
