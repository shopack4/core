<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\classes\twoFA;

use yii\web\UnprocessableEntityHttpException;
use shopack\aaa\backend\classes\twoFA\BaseTwoFA;
use shopack\aaa\backend\classes\twoFA\ITwoFA;
use shopack\aaa\backend\models\ApprovalRequestModel;
use shopack\aaa\backend\models\UserModel;

class SMSOTPTwoFA
	extends BaseTwoFA
	implements ITwoFA
{
	public function generate($userID, ?array $args = [])
	{
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
		$userModel = UserModel::findOne($userID);

		$code = $args['code'];

		$result = ApprovalRequestModel::acceptCode(
			$userModel->usrMobile,
			$code
		);

		return $result;
	}

}
