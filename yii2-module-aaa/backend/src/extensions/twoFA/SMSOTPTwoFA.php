<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\extensions\twoFA;

use shopack\aaa\backend\classes\twoFA\BaseTwoFA;
use shopack\aaa\backend\classes\twoFA\ITwoFA;
use shopack\aaa\backend\models\ApprovalRequestModel;
use shopack\aaa\backend\models\UserModel;
use yii\web\UnprocessableEntityHttpException;

class SMSOTPTwoFA
	extends BaseTwoFA
	implements ITwoFA
{
	public function generate($userID, ?array $args = [])
	{
    // if (Yii::$app->user->isGuest)
    //   throw new UnauthorizedHttpException("This process is not for guest.");
    // $userModel = UserModel::findOne(Yii::$app->user->id);

		$userModel = UserModel::findOne($userID);

		if (empty($userModel->usrMobile))
			throw new UnprocessableEntityHttpException("Mobile not defined for user");

		$result = ApprovalRequestModel::requestCode(
			$userModel->usrMobile,
			$userID
			// $args['gender'],
			// $args['firstName'],
			// $args['lastName'],
			// $args['forLogin']
		);

		return $result;
	}

	public function validate($userID, ?array $args = [])
	{
    // if (Yii::$app->user->isGuest)
    //   throw new UnauthorizedHttpException("This process is not for guest.");
    // $userModel = UserModel::findOne(Yii::$app->user->id);

		$userModel = UserModel::findOne($userID);

		$code = $args['code'];

		$result = ApprovalRequestModel::acceptCode(
			$userModel->usrMobile,
			$code
		);

		return $result;
	}

}
