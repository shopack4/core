<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

 namespace shopack\aaa\backend\models;

use Yii;
use yii\base\Model;
use yii\web\UnprocessableEntityHttpException;
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
    Yii::$app->user->assertIsNotGuest();

    $this->mobile = strtolower(trim($this->mobile));

    if ($this->validate() == false)
      throw new UnprocessableEntityHttpException(implode("\n", $this->getFirstErrors()));

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
