<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\models;

use Yii;
use yii\base\Model;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;

class PasswordChangeForm extends Model
{
  public $curPassword;
  public $newPassword;

  public function rules()
  {
    return [
      ['curPassword', 'required'],
      ['newPassword', 'required'],
    ];
  }

  public function save()
  {
    if ($this->validate() == false)
      return false;

		$userModel = UserModel::find()
      ->addSelect('usrPasswordHash')
      ->andWhere(['usrID' => Yii::$app->user->id])
      ->one();

		if (!$userModel)
			throw new NotFoundHttpException('user not found');

		if ($userModel->validatePassword($this->curPassword) == false)
			throw new ForbiddenHttpException('incorrect current password');

		$userModel->usrPassword = $this->newPassword;
		return $userModel->save();
  }

}
