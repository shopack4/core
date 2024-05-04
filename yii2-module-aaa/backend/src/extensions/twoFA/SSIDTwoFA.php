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

class SSIDTwoFA
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

		if (empty($userModel->usrSSID))
			throw new UnprocessableEntityHttpException("SSID not defined for user");

		if ($userModel->usrSSID != $args[0])
			throw new UnprocessableEntityHttpException("Mismatched SSID");

		return true;
	}

}
