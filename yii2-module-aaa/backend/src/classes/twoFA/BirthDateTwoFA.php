<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\classes\twoFA;

use Yii;
use yii\web\UnprocessableEntityHttpException;
use shopack\aaa\backend\classes\twoFA\BaseTwoFA;
use shopack\aaa\backend\classes\twoFA\ITwoFA;
use shopack\aaa\backend\models\UserModel;

class BirthDateTwoFA
	extends BaseTwoFA
	implements ITwoFA
{
	public function generate($userID, ?array $args = [])
	{
		$userModel = UserModel::findOne($userID);

		if (empty($userModel->usrBirthDate))
			throw new UnprocessableEntityHttpException("Birth Date not defined for user");

		return true;
	}

	public function validate($userID, ?array $args = [])
	{
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
