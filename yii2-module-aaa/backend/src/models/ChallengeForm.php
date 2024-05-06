<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\models;

use Yii;
use yii\base\Model;
use yii\web\UnauthorizedHttpException;
use yii\web\UnprocessableEntityHttpException;
use Ramsey\Uuid\Uuid;
use shopack\base\common\validators\GroupRequiredValidator;
use shopack\base\backend\helpers\AuthHelper;
use shopack\aaa\common\enums\enuUserStatus;
use shopack\base\common\security\RsaPublic;
use shopack\aaa\backend\models\VoucherModel;
use shopack\aaa\common\enums\enuVoucherType;
use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\base\backend\auth\Jwt;
use yii\di\Instance;
use Lcobucci\JWT\Token;

class ChallengeForm extends Model
{
  public $jwtComponentKey = 'jwt';

  public $token;
  public $code;

  public function rules()
  {
    return [
      ['token', 'required'],
      ['code', 'required'],
    ];
  }

  private ?Jwt $JWTComponent = null;
  public function getJwtComponent(): Jwt
  {
    if ($this->JWTComponent === null) {
      /** @var Jwt $jwt */
      $jwt = Instance::ensure($this->jwtComponentKey, Jwt::class);
      $this->JWTComponent = $jwt;
    }
    return $this->JWTComponent;
  }

  public function processToken(string $data): ?Token
  {
    $token = $this->getJwtComponent()->parse($data);

    if ($this->getJwtComponent()->validate($token) == false)
      throw new UnauthorizedHttpException('Invalid Token');

    return $token;
  }

	public function process()
	{
    if ($this->validate() == false)
      throw new UnauthorizedHttpException(implode("\n", $this->getFirstErrors()));

    $token = $this->processToken($this->token);

throw new UnauthorizedHttpException('AAAAAAAAAAAA');

/*
    $result = ApprovalRequestModel::acceptCode($bodyParams['key'], $bodyParams['value']);
    $userModel = $result['userModel'];

    if ($userModel) {
      if ($bodyParams['rememberMe'] ?? false) {
        list ($token, $mustApprove, $sessionModel, $challenge) = AuthHelper::doLogin($userModel, $bodyParams['rememberMe'] ?? false);

        return [
          'token' => $token,
          'mustApprove' => $mustApprove,
        ];
      }

      return [
        'result' => true,
      ];
    }

    throw new UnauthorizedHttpException("could not login.");
    // return [
    // 	'result' => ,
    // ];
*/
  }

}
