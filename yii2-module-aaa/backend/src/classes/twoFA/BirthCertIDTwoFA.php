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

class BirthCertIDTwoFA
	extends BaseTwoFA
	implements ITwoFA
{
	public function generate($userID, ?array $args = [])
	{
		$userModel = UserModel::findOne($userID);

		if (empty($userModel->usrBirthCertID))
			throw new UnprocessableEntityHttpException("Birth Cert ID not defined for user");

		return true;
	}

	public function validate($userID, ?array $args = [])
	{
    $userModel = UserModel::findOne($userID);

		if (empty($userModel->usrBirthCertID))
			throw new UnprocessableEntityHttpException("Birth Cert ID not defined for user");

		$code = $args['code'];
		if ($userModel->usrBirthCertID != $code)
			throw new UnprocessableEntityHttpException("Mismatched Birth Cert ID");

		return true;
	}

}
