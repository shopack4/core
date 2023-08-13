<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\models;

use yii\base\Model;
use yii\web\UnprocessableEntityHttpException;
use yii\web\UnauthorizedHttpException;
use shopack\base\common\helpers\PhoneHelper;
use shopack\aaa\common\enums\enuUserStatus;

class LoginByMobileForm extends Model
{
	const ERROR_MOBILE_NOT_EXISTS = 'ERROR_MOBILE_NOT_EXISTS';

  public $mobile;
  // public $code;
  public $signupIfNotExists;

  public function rules()
  {
    return [
      ['mobile', 'required'],
      // ['code', 'string'],
      ['signupIfNotExists', 'boolean'],
    ];
  }

	public function process()
	{
    if ($this->validate() == false)
      throw new UnauthorizedHttpException(implode("\n", $this->getFirstErrors()));

		$normalizedMobile = PhoneHelper::normalizePhoneNumber($this->mobile);
		if (!$normalizedMobile)
			throw new UnprocessableEntityHttpException('Invalid mobile number');

		//send code
		//------------------------
		// if (empty($this->code)) {
			// $userID = null;
			// $gender = null;
			// $firstName = null;
			// $lastName = null;

			$user = UserModel::find()
				->andWhere('usrStatus != \'' . enuUserStatus::Removed . '\'')
				->andWhere(['usrMobile' => $normalizedMobile])
				->one();

			if (!$user) {
				if (!$this->signupIfNotExists) {
					throw new UnprocessableEntityHttpException(self::ERROR_MOBILE_NOT_EXISTS);
				}

				$user = new UserModel();
				$user->usrMobile = $normalizedMobile;
				$user->bypassRequestApprovalCode = true;
				$user->usrStatus = enuUserStatus::NewForLoginByMobile;

				if ($user->save() == false)
        	throw new UnprocessableEntityHttpException("could not create new user\n" . implode("\n", $user->getFirstErrors()));
			}

			// if ($user) {
				$userID    = $user->usrID;
				$gender    = $user->usrGender;
				$firstName = $user->usrFirstName;
				$lastName  = $user->usrLastName;
			// }

			$result = ApprovalRequestModel::requestCode(
				$normalizedMobile,
				$userID,
				$gender,
				$firstName,
				$lastName,
				true
			);

			// list ($token, $mustApprove) = AuthHelper::doLogin($user, false, ['otp' => 'sms']);

			return array_merge([
				// 'token' => $token,
				'challenge' => 'otp,type=sms',
			],
			$result);
		// } // if (empty($this->code))

		//login
		//------------------------
		// $result = ApprovalRequestModel::acceptCode($normalizedMobile, $this->code);
		// $userModel = $result['userModel'];
		// if ($userModel) {
		// 	list ($token, $mustApprove) = AuthHelper::doLogin($userModel);

		// 	return [
		// 		'token' => $token,
		// 		'mustApprove' => $mustApprove,
		// 	];
		// }

		// throw new UnauthorizedHttpException("could not login. \n" . implode("\n", $this->getFirstErrors()));
	}

}
