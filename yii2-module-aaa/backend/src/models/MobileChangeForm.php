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

class MobileChangeForm extends Model
{
  public $mobile;

  public function rules()
  {
    return [
      ['mobile', 'required'],
    ];
  }

  public function process()
  {
    if (Yii::$app->user->isGuest)
      throw new UnauthorizedHttpException("This process is not for guest.");

    $this->mobile = strtolower(trim($this->mobile));

    if ($this->validate() == false)
      throw new UnauthorizedHttpException(implode("\n", $this->getFirstErrors()));

    $mobile = PhoneHelper::normalizePhoneNumber($this->mobile);

    if (empty($mobile))
      throw new UnprocessableEntityHttpException("Invalid mobile");

    $user = UserModel::findOne(Yii::$app->user->id);

    if ((empty($user->usrMobile) == false) && ($mobile == $user->usrMobile))
      throw new UnprocessableEntityHttpException("New mobile is the same as the current.");

    return ApprovalRequestModel::requestCode(
      $mobile,
      $user->usrID,
      $user->usrGender,
      $user->usrFirstName,
      $user->usrLastName
    );
  }

}
