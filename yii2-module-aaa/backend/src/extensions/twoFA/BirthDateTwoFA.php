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

class BirthDateTwoFA
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

		if (empty($userModel->usrBirthDate))
			throw new UnprocessableEntityHttpException("Birth Date not defined for user");

		// $a = str_replace('-', '/', $userModel->usrBirthDate);
		// $b = str_replace('-', '/', $args[0]);

		$a = new \DateTime($userModel->usrBirthDate);
		$b = new \DateTime($args[0]);

		if ($a != $b)
			throw new UnprocessableEntityHttpException("Mismatched Birth Date");

		return true;
	}

}
