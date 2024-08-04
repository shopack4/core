<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\classes\twoFA;

use yii\web\UnprocessableEntityHttpException;
use shopack\aaa\backend\classes\twoFA\BaseTwoFA;
use shopack\aaa\backend\classes\twoFA\ITwoFA;
use shopack\aaa\backend\models\UserModel;

class MicrosoftAuthTwoFA
	extends BaseTwoFA
	implements ITwoFA
{
	public function generate($userID, ?array $args = [])
	{
		$userModel = UserModel::findOne($userID);

		throw new UnprocessableEntityHttpException("Not implemented yet");

		return true;
	}

	public function validate($userID, ?array $args = [])
	{
		$userModel = UserModel::findOne($userID);

		$code = $args['code'];

		throw new UnprocessableEntityHttpException("Not implemented yet");

		return true;
	}

}
