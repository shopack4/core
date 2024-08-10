<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\helpers;

use DateTimeImmutable;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use shopack\base\common\helpers\ArrayHelper;
use shopack\base\common\helpers\GeneralHelper;
use shopack\base\backend\helpers\PrivHelper;
use shopack\aaa\common\enums\enuRole;
use shopack\aaa\common\enums\enuUserStatus;
use shopack\aaa\common\enums\enuSessionStatus;
use shopack\aaa\common\enums\enuTwoFAType;
use shopack\aaa\backend\models\SessionModel;
use shopack\aaa\backend\models\RoleModel;
use shopack\base\backend\auth\Jwt;

class AuthHelper
{
	public const CHALLENGE_NONE               = 0;
	public const CHALLENGE_ENABLE             = 1;
	public const CHALLENGE_ENABLE_WITHOUT_SMS = 2;

	/**
	 * @return: [$token, $mustApprove, $sessionModel, $challenge]
	 */
	static function login(
		$user,
		bool $rememberMe = false,
		$inputType = null,
		$challengeNeeded = self::CHALLENGE_NONE,
		?Array $additionalInfo = []
	) {
		if ($user->usrStatus == enuUserStatus::NewForLoginByMobile) {
			$user->usrStatus = enuUserStatus::Active;
			$user->save();
		}

		$settings = Yii::$app->params['settings'];

		$tokenExpireTTL		= ArrayHelper::getValue($settings['AAA']['jwt'], 'token-ttl',		 5 * 60);
		$sessionExpireTTL	= ArrayHelper::getValue($settings['AAA']['jwt'], 'session-ttl',	24 * 3600);

		$now = new \DateTimeImmutable();
		$tokenExpire = $now->modify("+{$tokenExpireTTL} second");

		$challenge = null;
		if ($challengeNeeded !== self::CHALLENGE_NONE) {
			$usr2FA = $user->usr2FA;

			//remove sms based from array
			if (empty($usr2FA) == false) {
				if ($challengeNeeded === self::CHALLENGE_ENABLE_WITHOUT_SMS) {
					foreach ($usr2FA as $k => $v) {
						if ($k == enuTwoFAType::SMSOTP) {
							unset($usr2FA[$k]);
						}
					}
				}
			}

			if (empty($usr2FA) == false) {
				if (count($usr2FA) == 1)
					$r = 0;
				else
					$r = rand(0, count($usr2FA)-1);

				$challenge = array_keys($usr2FA)[$r];

				$challengeToken = Yii::$app->jwt->getBuilder()
					// ->identifiedBy($sessionModel->ssnID)
					->issuedAt($now)
					->expiresAt($tokenExpire)
					// ->withClaim('privs', $privs)
					->withClaim('uid', $user->usrID)
				;

				// if (empty($user->usrEmail) == false)
				//   $challengeToken->withClaim('email', $user->usrEmail);
				// if (empty($user->usrMobile) == false)
				//   $challengeToken->withClaim('mobile', $user->usrMobile);
				// if (empty($user->usrFirstName) == false)
				//   $challengeToken->withClaim('firstName', $user->usrFirstName);
				// if (empty($user->usrLastName) == false)
				//   $challengeToken->withClaim('lastName', $user->usrLastName);

				if ($rememberMe)
					$challengeToken->withClaim('rmmbr', 1);

				// if (empty($additionalInfo) == false) {
				//   foreach ($additionalInfo as $k => $v) {
				//     $challengeToken->withClaim($k, $v);
				//   }
				// }

				// $mustApprove = [];
				// if ($user->usrStatus != enuUserStatus::NewForLoginByMobile) {
				//   if (empty($user->usrEmail) == false && empty($user->usrEmailApprovedAt))
				//     $mustApprove[] = 'email';
				//   if (empty($user->usrMobile) == false && empty($user->usrMobileApprovedAt))
				//     $mustApprove[] = 'mobile';
				//   if (empty($mustApprove) == false)
				//     $challengeToken->withClaim('mustApprove', implode(',', $mustApprove));
				// }

				$challengeToken->withClaim('2fa', 1);
				$challengeToken->withClaim('type', /*'2fa:' .*/ $challenge);

				if ($inputType == GeneralHelper::PHRASETYPE_EMAIL) {
					$challengeToken->withClaim('email', $user->usrEmail);
				} else if ($inputType == GeneralHelper::PHRASETYPE_MOBILE) {
					$challengeToken->withClaim('mobile', $user->usrMobile);
				} else if ($inputType == GeneralHelper::PHRASETYPE_SSID) {
					$challengeToken->withClaim('ssid', $user->usrSSID);
				}

				$signer = Yii::$app->jwt->getConfiguration()->signer();
				$signingKey = Yii::$app->jwt->getConfiguration()->signingKey();

				$challengeToken = $challengeToken->getToken($signer, $signingKey);
				$challengeToken = $challengeToken->toString();

				return [null, false, null, $challengeToken];
			}
		}

		//create session
		//-----------------------
		$sessionModel = new SessionModel();
		$sessionModel->ssnUserID = $user->usrID;
		if ($sessionModel->save() == false)
			throw new UnprocessableEntityHttpException(implode("\n", $sessionModel->getFirstErrors()));

		//privs
		//-----------------------
		$privs = [];

		if ($user->usrStatus != enuUserStatus::NewForLoginByMobile) {
			if ((empty($user->usrEmail) == false && empty($user->usrEmailApprovedAt))
				|| (empty($user->usrMobile) == false && empty($user->usrMobileApprovedAt))
			) {
				//set to user role until signup email or mobile approved
				$role = RoleModel::findOne(['rolID' => enuRole::User]);
				if (empty($role->rolPrivs) == false)
					$privs = $role->rolPrivs;
			} else {
				if (empty($user->usrRoleID) == false) {
					$role = $user->role;
					if (empty($role->rolPrivs) == false)
						$privs = array_replace_recursive($privs, $role->rolPrivs);
				}

				if (empty($user->usrPrivs) == false)
					$privs = array_replace_recursive($privs, $user->usrPrivs);
			}

			PrivHelper::digestPrivs($privs);
		}

		//-----------------------
		$sessionExpireAt = $now->modify("+{$sessionExpireTTL} second");

		//token
		$token = Yii::$app->jwt->getBuilder()
			->identifiedBy($sessionModel->ssnID) //Yii::$app->session->id) // Configures the id (jti claim)
			->issuedAt($now)
			->expiresAt($tokenExpire)
			->withClaim('privs', $privs)
			->withClaim('uid', $user->usrID)
			->withClaim(Jwt::KEY_LONG_EXPIRATION, self::convertDate($sessionExpireAt))
		;

		if (empty($user->usrEmail) == false)			$token->withClaim('email', $user->usrEmail);
		if (empty($user->usrMobile) == false)			$token->withClaim('mobile', $user->usrMobile);
		if (empty($user->usrFirstName) == false)	$token->withClaim('firstName', $user->usrFirstName);
		if (empty($user->usrLastName) == false)		$token->withClaim('lastName', $user->usrLastName);

		if ($rememberMe)
			$token->withClaim('rmmbr', 1);

		if (empty($additionalInfo) == false) {
			foreach ($additionalInfo as $k => $v) {
				$token->withClaim($k, $v);
			}
		}

		$mustApprove = [];
		if ($user->usrStatus != enuUserStatus::NewForLoginByMobile) {
			if (empty($user->usrEmail) == false && empty($user->usrEmailApprovedAt))
				$mustApprove[] = 'email';

			if (empty($user->usrMobile) == false && empty($user->usrMobileApprovedAt))
				$mustApprove[] = 'mobile';

			if (empty($mustApprove) == false)
				$token->withClaim('mustApprove', implode(',', $mustApprove));
		}

		$signer = Yii::$app->jwt->getConfiguration()->signer();
		$signingKey = Yii::$app->jwt->getConfiguration()->signingKey();

		$token = $token->getToken($signer, $signingKey);
		$token = $token->toString();

		//update session
		$sessionModel->ssnJWT = $token;
		$sessionModel->ssnStatus = ($user->usrStatus == enuUserStatus::NewForLoginByMobile
			? enuSessionStatus::ForLoginByMobile
			: enuSessionStatus::Active);

		$sessionModel->ssnTokenExpireAt = new \yii\db\Expression("DATE_ADD(NOW(), INTERVAL {$tokenExpireTTL} SECOND)"); //$tokenExpire->format('Y-m-d H:i:s');

		$sessionModel->ssnSessionExpireAt = new \yii\db\Expression("DATE_ADD(NOW(), INTERVAL {$sessionExpireTTL} SECOND)");

		$ipv4 = $_SERVER['REMOTE_ADDR'] ?? null;
		if ($ipv4 != null) {
			$ipv4 = ip2long($ipv4);
		}
		$sessionModel->ssnIPv4 = $ipv4;

		$sessionModel->ssnInfo = array_filter([
			'user-agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
		]);

		$sessionModel->save();

		//-----------------------
		return [$token, $mustApprove, $sessionModel, $challenge];
	}

	static function logout()
	{
		if (!Yii::$app->user->accessToken)
			return;

		$sessionID = Yii::$app->user->accessToken->claims()->get(\Lcobucci\JWT\Token\RegisteredClaims::ID);
		if ($sessionID == null)
			throw new NotFoundHttpException("Session not found");

		$rowsAffected = SessionModel::deleteAll([
			'ssnID' => $sessionID,
		]);

		if ($rowsAffected != 1)
			throw new NotFoundHttpException("Could not log out");

		Yii::$app->user->accessToken = null;
	}

	public static function refreshToken($refresh_token)
	{
		$token = Yii::$app->jwt->parse($refresh_token, Jwt::VALIDATE_SANITY);

		if (YII_ENV_PROD && (Yii::$app->jwt->verifyTokenExpiration($token) == false)) {
			throw new UnprocessableEntityHttpException('The token is still alive');
		}

		$sessionID = $token->claims()->get('jti');
		$sessionModel = SessionModel::findOne([
			'ssnID' => $sessionID,
		]);

		if ($sessionModel == null) {
			throw new NotFoundHttpException("The session not found");
		}

		// $sessionExp = $token->claims()->get(Jwt::KEY_LONG_EXPIRATION);
		Yii::$app->jwt->assertSessionExpiration($token);

		$dtNow = (new \DateTime('now', new \DateTimeZone('UTC')));
		$nowSeconds = $dtNow->getTimestamp();

		//is locked before?
		if (empty($sessionModel->ssnLockedAt) == false) {
			$dtLockedAt = new \DateTime($sessionModel->ssnLockedAt, new \DateTimeZone('UTC'));
			$lockedAtSeconds = $dtLockedAt->getTimestamp();
			$seconds = $nowSeconds - $lockedAtSeconds;

			if ($seconds <= 5) {
				//wait for unlock
			} else {
				//re-lock
			}
		}

		$instanceID = Yii::$app->getInstanceID();

		// lock / re-lock
		$sessionModel->ssnLockedAt = new \yii\db\Expression('NOW()');
		$sessionModel->ssnLockedBy = $instanceID;
		$sessionModel->save();

		try {
			//regenerate jwt

			//store old and new jwt

			//unlock
			$sessionModel->ssnLockedAt = null;
			$sessionModel->ssnLockedBy = null;

			//save
			$sessionModel->save();

		} catch (\Throwable $th) {
			throw $th;
		}

		// ssnTokenExpireAt
		// ssnSessionExpireAt
		// ssnOldJwt
		// ssnRefreshedAt
		// ssnRefreshCount
		// ssnLockedAt

	}

	private static function convertDate(DateTimeImmutable $date)
	{
		if ($date->format('u') === '000000') {
			return (int) $date->format('U');
		}

		return (float) $date->format('U.u');
	}

}
