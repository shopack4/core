<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\extensions\twoFA;

use shopack\aaa\backend\classes\twoFA\BaseTwoFA;
use shopack\aaa\backend\classes\twoFA\ITwoFA;
use shopack\aaa\backend\models\ApprovalRequestModel;

class SMSOTPTwoFA
	extends BaseTwoFA
	implements ITwoFA
{
	public function generate($args = [])
	{
		return ApprovalRequestModel::requestCode(
			$args['emailOrMobile'],
			$args['userID'],
			$args['gender'],
			$args['firstName'],
			$args['lastName'],
			$args['forLogin']
		);
	}

	public function verify()
	{
	}

}
