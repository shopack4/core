<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\extensions\twoFA;

use shopack\aaa\backend\classes\twoFA\BaseTwoFA;
use shopack\aaa\backend\classes\twoFA\ITwoFA;
use shopack\aaa\backend\models\UserModel;
use yii\web\UnprocessableEntityHttpException;

class MicrosoftAuthTwoFA
	extends BaseTwoFA
	implements ITwoFA
{
	public function generate($userID, ?array $args = [])
	{
    // if (Yii::$app->user->isGuest)
    //   throw new UnauthorizedHttpException("This process is not for guest.");
    // $userModel = UserModel::findOne(Yii::$app->user->id);

		$userModel = UserModel::findOne($userID);

		throw new UnprocessableEntityHttpException("Not implemented yet");

		return true;
	}

	public function validate($userID, ?array $args = [])
	{
    // if (Yii::$app->user->isGuest)
    //   throw new UnauthorizedHttpException("This process is not for guest.");
    // $userModel = UserModel::findOne(Yii::$app->user->id);

		$userModel = UserModel::findOne($userID);

		$code = $args['code'];

		throw new UnprocessableEntityHttpException("Not implemented yet");

		return true;
	}

}
