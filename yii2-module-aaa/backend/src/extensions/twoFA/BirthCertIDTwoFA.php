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
	public function generate($userID, ?array $args = [])
	{
    // if (Yii::$app->user->isGuest)
    //   throw new UnauthorizedHttpException("This process is not for guest.");
    // $userModel = UserModel::findOne(Yii::$app->user->id);

		$userModel = UserModel::findOne($userID);

		if (empty($userModel->usrBirthCertID))
			throw new UnprocessableEntityHttpException("Birth Cert ID not defined for user");

		return true;
	}

	public function validate($userID, ?array $args = [])
	{
    // if (Yii::$app->user->isGuest)
    //   throw new UnauthorizedHttpException("This process is not for guest.");
    // $userModel = UserModel::findOne(Yii::$app->user->id);

    $userModel = UserModel::findOne($userID);

		if (empty($userModel->usrBirthCertID))
			throw new UnprocessableEntityHttpException("Birth Cert ID not defined for user");

		$code = $args['code'];
		if ($userModel->usrBirthCertID != $code)
			throw new UnprocessableEntityHttpException("Mismatched Birth Cert ID");

		return true;
	}

}
