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
    // if (Yii::$app->user->isGuest)
    //   throw new UnauthorizedHttpException("This process is not for guest.");

		$userID = $args['userID'];
    $userModel = UserModel::findOne($userID);
		if (empty($userModel->usrBirthDate))
			throw new UnprocessableEntityHttpException("Birth Date not defined for user");

		$code = $args['code'];
		$a = new \DateTime($code);
		$b = new \DateTime($userModel->usrBirthDate);

		if ($a != $b)
			throw new UnprocessableEntityHttpException("Mismatched Birth Date");

		return true;
	}

}
