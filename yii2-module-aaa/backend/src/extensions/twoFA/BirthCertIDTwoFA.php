<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\extensions\twoFA;

use Yii;
use yii\web\UnauthorizedHttpException;
use shopack\aaa\backend\classes\twoFA\BaseTwoFA;
use shopack\aaa\backend\classes\twoFA\ITwoFA;
use shopack\aaa\backend\models\UserModel;
use yii\web\UnprocessableEntityHttpException;

class BirthCertIDTwoFA
	extends BaseTwoFA
	implements ITwoFA
{
	public function generate(?array $args = [])
	{
		return true;
	}

	public function validate(?array $args = [])
	{
    if (Yii::$app->user->isGuest)
      throw new UnauthorizedHttpException("This process is not for guest.");

    $userModel = UserModel::findOne(Yii::$app->user->id);

		if (empty($userModel->usrBirthCertID))
			throw new UnprocessableEntityHttpException("Birth Cert ID not defined for user");

		if ($userModel->usrBirthCertID != $args[0])
			throw new UnprocessableEntityHttpException("Mismatched Birth Cert ID");

		return true;
	}

}
