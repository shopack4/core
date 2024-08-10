<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\helpers;

use DateTimeImmutable;
use Lcobucci\JWT\Token\RegisteredClaims;
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
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

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
		$tokenBuilder = Yii::$app->jwt->getBuilder()
			->identifiedBy($sessionModel->ssnID) //Yii::$app->session->id) // Configures the id (jti claim)
			->issuedAt($now)
			->expiresAt($tokenExpire)
			->withClaim('privs', $privs)
			->withClaim('uid', $user->usrID)
			->withClaim(Jwt::KEY_LONG_EXPIRATION, self::convertDate($sessionExpireAt))
		;

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

		if (YII_ENV_PROD && (Yii::$app->jwt->verifyTokenExpiration($token) == false)) {
			throw new UnprocessableEntityHttpException('The token is still alive');
		}

		Yii::$app->jwt->assertSessionExpiration($token);

		$sessionID = $token->claims()->get('jti');
		$sessionModel = SessionModel::findOne([
			'ssnID' => $sessionID,
		]);

		if ($sessionModel == null) {
			throw new NotFoundHttpException("The session not found");
		}

		if ($sessionModel->ssnJWT != $refresh_token) {
			//is this refreshed before?
			if ($sessionModel->ssnOldJwt == $refresh_token)
				return [
					'ph' => 1,
					'token' => $sessionModel->ssnJWT,
				];

			throw new UnauthorizedHttpException('Invalid token');
		}

		$dtNow = (new \DateTime('now', new \DateTimeZone('UTC')));
		$nowSeconds = $dtNow->getTimestamp();

		//is locked before?
		if (Yii::$app->mutex->isAcquired("session.refresh.{$sessionID}")) {
			$i = 0;
			while (empty($sessionModel->ssnLockedAt) && (($i++) < 10))
				$sessionModel->refresh();

			if (empty($sessionModel->ssnLockedAt))
				Yii::$app->mutex->release("session.refresh.{$sessionID}");
			else {
				$dtLockedAt = new \DateTime($sessionModel->ssnLockedAt, new \DateTimeZone('UTC'));
				$lockedAtSeconds = $dtLockedAt->getTimestamp();
				$seconds = $lockedAtSeconds - $nowSeconds;

				Yii::$app->mutex->release("session.refresh.{$sessionID}");
				return [
					'ph' => 'locked',
					'locked' => [
						$dtLockedAt,
						$lockedAtSeconds,
					],
					'now' => [
						$dtNow,
						$nowSeconds,
					],
					's' => $seconds,
				];

				if ($seconds <= 5) {
					//wait for unlock
					Yii::$app->mutex->release("session.refresh.{$sessionID}");

					return [
						'ph' => 2,
						's' => $seconds,
						'token' => 'AAAAAAAAA',
					];
				}
			}
		}

		$instanceID = Yii::$app->getInstanceID();

		if (Yii::$app->mutex->acquire("session.refresh.{$sessionID}") == false)
			throw new ServerErrorHttpException('error in db lock');

		try {
			// lock / re-lock
			$sessionModel->ssnLockedAt = new \yii\db\Expression('NOW()');
			$sessionModel->ssnLockedBy = $instanceID;
			if ($sessionModel->save() == false)
				throw new UnprocessableEntityHttpException(implode("\n", $sessionModel->getFirstErrors()));

			try {
				//compute token expire
				$settings = Yii::$app->params['settings'];
				$tokenExpireTTL = ArrayHelper::getValue($settings['AAA']['jwt'], 'token-ttl', 5 * 60);
				$now = new \DateTimeImmutable();
				$tokenExpire = $now->modify("+{$tokenExpireTTL} second");

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
				// $sessionModel->ssnOldJwt = $refresh_token;
				// $sessionModel->ssnJWT = $tokenString;
				// $sessionModel->ssnTokenExpireAt = $tokenExpire->format('Y-m-d H:i:s');

				//unlock
				$sessionModel->ssnLockedAt = new \yii\db\Expression('NULL');
				$sessionModel->ssnLockedBy = new \yii\db\Expression('NULL');

				//save
				$sessionModel->ssnRefreshedAt = new \yii\db\Expression('NOW()');
				$sessionModel->ssnRefreshCount = new \yii\db\Expression('IFNULL(ssnRefreshCount, 0) + 1');
				if ($sessionModel->save() == false)
					throw new UnprocessableEntityHttpException(implode("\n", $sessionModel->getFirstErrors()));

				return [
					'ph' => 3,
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
			Yii::$app->mutex->release("session.refresh.{$sessionID}");
		}
	}

	private static function convertDate(DateTimeImmutable $date)
	{
		if ($date->format('u') === '000000') {
			return (int) $date->format('U');
		}

		return (float) $date->format('U.u');
	}

}
