<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\models;

use Yii;
use yii\base\Model;
use yii\web\NotFoundHttpException;

//called from auth/password-set (by logged in user profile page)
/*
class PasswordSetForm extends Model
{
  public $newPassword;

  public function rules()
  {
    return [
      ['newPassword', 'required'],
    ];
  }

  public function save()
  {
    if ($this->validate() == false)
      return false;

		$userModel = UserModel::findOne([
			'usrID' => Yii::$app->user->id,
		]);

		if (!$userModel)
			throw new NotFoundHttpException("user not found");

		$userModel->usrPassword = $this->newPassword;
		$done = $userModel->save();

    if ($done == false)
      return false;


    //logout




    return true;

  }

}
*/
