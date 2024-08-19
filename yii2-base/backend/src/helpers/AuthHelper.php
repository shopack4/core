<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\helpers;

use Yii;
use yii\web\UnauthorizedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use DateTimeImmutable;
use Lcobucci\JWT\Token\RegisteredClaims;
use shopack\base\common\helpers\ArrayHelper;
use shopack\base\common\helpers\GeneralHelper;
use shopack\base\backend\helpers\PrivHelper;
use shopack\base\backend\auth\Jwt;
use shopack\aaa\common\enums\enuRole;
use shopack\aaa\common\enums\enuUserStatus;
use shopack\aaa\common\enums\enuSessionStatus;
use shopack\aaa\common\enums\enuTwoFAType;
use shopack\aaa\backend\models\SessionModel;
use shopack\aaa\backend\models\RoleModel;
use shopack\base\common\db\DbExpression;

class AuthHelper
{
	public const CHALLENGE_NONE               = 0;
	public const CHALLENGE_ENABLE             = 1;
	public const CHALLENGE_ENABLE_WITHOUT_SMS = 2;

	// private static function convertDate(DateTimeImmutable $date)
	// {
	// 	if ($date->format('u') === '000000') {
	// 		return (int) $date->format('U');
	// 	}

	// 	return (float) $date->format('U.u');
	// }

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
		try {
			$token = Yii::$app->jwt->parseFromRequest(Jwt::VALIDATE_SANITY);
			if ($token) {
				$sessionID = $token->claims()->get(\Lcobucci\JWT\Token\RegisteredClaims::ID);
				if ($sessionID) {
					$rowsAffected = SessionModel::deleteAll(['ssnID' => $sessionID]);
					Yii::info("{$rowsAffected} sessions deleted before login");
				}
			}
		} catch (\Throwable $th) { ; }

		if ($user->usrStatus == enuUserStatus::NewForLoginByMobile) {
			$user->usrStatus = enuUserStatus::Active;
			$user->save();
		}

		$settings = Yii::$app->params['settings'];

		$tokenExpireTTL		= ArrayHelper::getValue($settings['AAA']['jwt'], 'token-ttl',		 5 * 60);
		$sessionExpireTTL	= ArrayHelper::getValue($settings['AAA']['jwt'], 'session-ttl',	24 * 3600);

		$tokenExpire = Yii::$app->db->utcNow->modify("+{$tokenExpireTTL} second");

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
					->issuedAt(Yii::$app->db->utcNow)
					->expiresAt($tokenExpire)
					->withClaim(Jwt::KEY_LONG_EXPIRATION, $tokenExpire)
					// ->withClaim('privs', $privs)
					->withClaim('uid', $user->usrID)
				;

				$origin = Yii::$app->request->getOrigin();
				if (empty($origin) == false)
					$challengeToken->issuedBy($origin);

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
		$sessionExpireAt = Yii::$app->db->utcNow->modify("+{$sessionExpireTTL} second");

		//token
		$tokenBuilder = Yii::$app->jwt->getBuilder()
			->identifiedBy($sessionModel->ssnID)
			->issuedAt(Yii::$app->db->utcNow)
			->expiresAt($tokenExpire)
			->withClaim(Jwt::KEY_LONG_EXPIRATION, $sessionExpireAt)
			// ->withClaim(Jwt::KEY_LONG_EXPIRATION, self::convertDate($sessionExpireAt))
			->withClaim('privs', $privs)
			->withClaim('uid', $user->usrID)
		;

		$origin = Yii::$app->request->getOrigin();
		if (empty($origin) == false)
			$tokenBuilder->issuedBy($origin);

		if (empty($user->usrEmail) == false)
			$tokenBuilder->withClaim('email', $user->usrEmail);
		if (empty($user->usrMobile) == false)
			$tokenBuilder->withClaim('mobile', $user->usrMobile);
		if (empty($user->usrFirstName) == false)
			$tokenBuilder->withClaim('firstName', $user->usrFirstName);
		if (empty($user->usrLastName) == false)
			$tokenBuilder->withClaim('lastName', $user->usrLastName);

		if ($rememberMe)
			$tokenBuilder->withClaim('rmmbr', 1);

		if (empty($additionalInfo) == false) {
			foreach ($additionalInfo as $k => $v) {
				$tokenBuilder->withClaim($k, $v);
			}
		}

		$mustApprove = [];
		if ($user->usrStatus != enuUserStatus::NewForLoginByMobile) {
			if (empty($user->usrEmail) == false && empty($user->usrEmailApprovedAt))
				$mustApprove[] = 'email';

			if (empty($user->usrMobile) == false && empty($user->usrMobileApprovedAt))
				$mustApprove[] = 'mobile';

			if (empty($mustApprove) == false)
				$tokenBuilder->withClaim('mustApprove', implode(',', $mustApprove));
		}

		$signer = Yii::$app->jwt->getConfiguration()->signer();
		$signingKey = Yii::$app->jwt->getConfiguration()->signingKey();

		$token = $tokenBuilder->getToken($signer, $signingKey);
		$tokenString = $token->toString();

		//update session
		$sessionModel->ssnJWT = $tokenString;
		$sessionModel->ssnStatus = ($user->usrStatus == enuUserStatus::NewForLoginByMobile
			? enuSessionStatus::ForLoginByMobile
			: enuSessionStatus::Active);

		$sessionModel->ssnTokenExpireAt = new DbExpression("FROM_UNIXTIME({$tokenExpire->getTimestamp()})");
		//new DbExpression("DATE_ADD(NOW(), INTERVAL {$tokenExpireTTL} SECOND)"); //$tokenExpire->format('Y-m-d H:i:s');

		$sessionModel->ssnSessionExpireAt = new DbExpression("FROM_UNIXTIME({$sessionExpireAt->getTimestamp()})");
		//new DbExpression("DATE_ADD(NOW(), INTERVAL {$sessionExpireTTL} SECOND)");

		$ipv4 = $_SERVER['REMOTE_ADDR'] ?? null;
		if ($ipv4 != null) {
			$ipv4 = ip2long($ipv4);
		}
		$sessionModel->ssnIPv4 = $ipv4;

		$sessionModel->ssnInfo = array_filter([
			'user-agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
			'origin' => $origin,
		]);

		$sessionModel->save();

		//-----------------------
		return [$tokenString, $mustApprove, $sessionModel, $challenge];
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

	public static function refreshToken(string $refresh_token)
	{
		$token = Yii::$app->jwt->parse($refresh_token, Jwt::VALIDATE_SANITY);

		if (Yii::$app->jwt->verifyTokenExpiration($token)) {
			throw new UnprocessableEntityHttpException('The token is still alive');
		}

		Yii::$app->jwt->assertSessionExpiration($token);

		$sessionID = $token->claims()->get(\Lcobucci\JWT\Token\RegisteredClaims::ID);

		$dbLockKey = "session.refresh.{$sessionID}";

		//retry in 10 seconds
		if (Yii::$app->mutex->acquire($dbLockKey, 10) == false) {
			throw new UnprocessableEntityHttpException('lock not released');
		}

		try {
			$sessionModel = SessionModel::find()
				->noCache()
				->andWhere(['ssnID' => $sessionID])
				->one();

			if ($sessionModel == null)
				throw new NotFoundHttpException("The session not found");

			if (YII_DEBUG && empty($sessionModel->ssnRefreshedAt) == false) {
				$dtRefreshedAt = new \DateTimeImmutable($sessionModel->ssnRefreshedAt, new \DateTimeZone('UTC'));
				$refreshedAtSeconds = $dtRefreshedAt->getTimestamp();
			}

			$nowSeconds = Yii::$app->db->utcNow->getTimestamp();

			if ((empty($sessionModel->ssnRefreshedAt) == false)
				&& ($sessionModel->ssnJWT != $refresh_token)
			 ) {
				//has this been refreshed in the last 5 seconds?

				$dtRefreshedAt = new \DateTimeImmutable($sessionModel->ssnRefreshedAt, new \DateTimeZone('UTC'));
				$refreshedAtSeconds = $dtRefreshedAt->getTimestamp();
				$seconds = $nowSeconds - $refreshedAtSeconds;

				if ($sessionModel->ssnOldJwt == $refresh_token) {
					if ($seconds <= 5) {
						return [
							'ph' => 'od',
							// 'refreshed' => [
							// 	$dtRefreshedAt,
							// 	$refreshedAtSeconds,
							// ],
							// 'now' => [
							// 	Yii::$app->db->utcNow,
							// 	$nowSeconds,
							// ],
							// 's' => $seconds,
							'token' => $sessionModel->ssnJWT,
						];
					}

					//jwt expired more than 5 seconds ago
					throw new UnauthorizedHttpException('RELOGIN:Token is dead');
				}
			}

			$instanceID = Yii::$app->getInstanceID();

			// lock / re-lock
			$sessionModel->ssnLockedAt = DbExpression::now();
			$sessionModel->ssnLockedBy = $instanceID;
			if ($sessionModel->save() == false)
				throw new UnprocessableEntityHttpException(implode("\n", $sessionModel->getFirstErrors()));

			//compute token expire
			$settings = Yii::$app->params['settings'];
			$tokenExpireTTL = ArrayHelper::getValue($settings['AAA']['jwt'], 'token-ttl', 5 * 60);
			$tokenExpire = Yii::$app->db->utcNow->modify("+{$tokenExpireTTL} second");

			$ssnSessionExpireAt = new \DateTimeImmutable($sessionModel->ssnSessionExpireAt, new \DateTimeZone('UTC'));

			if ($tokenExpire > $ssnSessionExpireAt)
				$tokenExpire = $ssnSessionExpireAt;

			//regenerate jwt
			$tokenBuilder = Yii::$app->jwt->getBuilder();
			foreach ($token->claims()->all() as $k => $v) {
				switch ($k) {
					case RegisteredClaims::AUDIENCE:
						$tokenBuilder->permittedFor($v);
						break;
					case RegisteredClaims::EXPIRATION_TIME:
						$tokenBuilder->expiresAt($tokenExpire);
						break;
					case RegisteredClaims::ID:
						$tokenBuilder->identifiedBy($v);
						break;
					case RegisteredClaims::ISSUED_AT:
						$tokenBuilder->issuedAt($v);
						break;
					case RegisteredClaims::ISSUER:
						$tokenBuilder->issuedBy($v);
						break;
					case RegisteredClaims::NOT_BEFORE:
						$tokenBuilder->canOnlyBeUsedAfter($v);
						break;
					case RegisteredClaims::SUBJECT:
						$tokenBuilder->relatedTo($v);
						break;

					default:
						$tokenBuilder->withClaim($k, $v);
						break;
				}
			}

			$signer = Yii::$app->jwt->getConfiguration()->signer();
			$signingKey = Yii::$app->jwt->getConfiguration()->signingKey();

			$newToken = $tokenBuilder->getToken($signer, $signingKey);
			$tokenString = $newToken->toString();

			//store old and new jwt
			$sessionModel->ssnOldJwt = $refresh_token;
			$sessionModel->ssnJWT = $tokenString;
			$sessionModel->ssnTokenExpireAt = new DbExpression("FROM_UNIXTIME({$tokenExpire->getTimestamp()})");

			//unlock
			$sessionModel->ssnLockedAt = DbExpression::null();
			$sessionModel->ssnLockedBy = DbExpression::null();

			try {
				//save
				$sessionModel->ssnRefreshedAt = DbExpression::now();
				$sessionModel->ssnRefreshCount = new DbExpression('IFNULL(ssnRefreshCount, 0) + 1');
				if ($sessionModel->save() == false)
					throw new UnprocessableEntityHttpException(implode("\n", $sessionModel->getFirstErrors()));

				return [
					'ph' => 'rn',
					'token' => $tokenString,
				];

			} catch (\Throwable $th) {
				//unlock
				$sessionModel->ssnLockedAt = null;
				$sessionModel->ssnLockedBy = null;
				$sessionModel->save();

				throw $th;
			}
		} catch (\Throwable $th) {
			throw $th;
		} finally {
			Yii::$app->mutex->release($dbLockKey);
		}
	}

}
