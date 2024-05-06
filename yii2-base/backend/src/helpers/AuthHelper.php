<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\helpers;

use Yii;
use shopack\base\backend\helpers\PrivHelper;
use shopack\base\common\helpers\PhoneHelper;
use yii\web\UnauthorizedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use shopack\base\common\helpers\ArrayHelper;
use shopack\aaa\backend\models\UserModel;
use shopack\aaa\backend\models\SessionModel;
use shopack\aaa\backend\models\RoleModel;
use shopack\aaa\common\enums\enuRole;
use shopack\aaa\common\enums\enuUserStatus;
use shopack\aaa\common\enums\enuSessionStatus;
use shopack\aaa\common\enums\enuTwoFAType;
use shopack\base\common\helpers\GeneralHelper;

class AuthHelper
{
  public const CHALLENGE_NONE               = 0;
  public const CHALLENGE_ENABLE             = 1;
  public const CHALLENGE_ENABLE_WITHOUT_SMS = 2;

  /**
   * @return: [$token, $mustApprove, $sessionModel, $challenge]
   */
  static function doLogin(
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
    $ttl = ArrayHelper::getValue($settings['AAA']['jwt'], 'ttl', 5 * 60);
    $now = new \DateTimeImmutable();
    $expire = $now->modify("+{$ttl} second");

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
          ->expiresAt($expire)
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

        $challengeToken = $challengeToken->getToken(
          Yii::$app->jwt->getConfiguration()->signer(),
          Yii::$app->jwt->getConfiguration()->signingKey()
        );
        $challengeToken = $challengeToken->toString();

        return [null, false, null, $challengeToken];
      }
    }

    //create session
    //-----------------------
    $sessionModel = new SessionModel();
    $sessionModel->ssnUserID = $user->usrID;
    if ($sessionModel->save() == false)
      throw new UnauthorizedHttpException(implode("\n", $sessionModel->getFirstErrors()));

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

    //token
    //-----------------------
    $token = Yii::$app->jwt->getBuilder()
      ->identifiedBy($sessionModel->ssnID) //Yii::$app->session->id) // Configures the id (jti claim)
      ->issuedAt($now)
      ->expiresAt($expire)
      ->withClaim('privs', $privs)
      ->withClaim('uid', $user->usrID)
    ;

    if (empty($user->usrEmail) == false)
      $token->withClaim('email', $user->usrEmail);
    if (empty($user->usrMobile) == false)
      $token->withClaim('mobile', $user->usrMobile);
    if (empty($user->usrFirstName) == false)
      $token->withClaim('firstName', $user->usrFirstName);
    if (empty($user->usrLastName) == false)
      $token->withClaim('lastName', $user->usrLastName);

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

    $token = $token->getToken(
      Yii::$app->jwt->getConfiguration()->signer(),
      Yii::$app->jwt->getConfiguration()->signingKey()
    );
    $token = $token->toString();

    //update session
    //-----------------------
    $sessionModel->ssnJWT = $token;
    $sessionModel->ssnStatus = ($user->usrStatus == enuUserStatus::NewForLoginByMobile
      ? enuSessionStatus::ForLoginByMobile
      : enuSessionStatus::Active);
    $sessionModel->ssnExpireAt = $expire->format('Y-m-d H:i:s');
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

}
