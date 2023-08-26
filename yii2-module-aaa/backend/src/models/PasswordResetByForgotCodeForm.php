<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\models;

use yii\base\Model;
use shopack\aaa\backend\models\ForgotPasswordRequestModel;
use shopack\base\common\helpers\GeneralHelper;

//<a href='{{panel-address}}/aaa/auth/password-reset-by-forgot-code?input={{email}}&code={{code}}'>OK</a>
class PasswordResetByForgotCodeForm extends Model
{
  public $input;
  public $code;
  public $newPassword;

  public function rules()
  {
    return [
      ['input', 'required'],
      ['code', 'required'],
      ['newPassword', 'required'],
    ];
  }

  public function save()
  {
    if ($this->validate() == false)
      return false;

    // list ($normalizedInput, $inputType) = GeneralHelper::checkLoginPhrase($this->input, false);

		return ForgotPasswordRequestModel::acceptCode($this->input, $this->code, $this->newPassword);
  }

}
