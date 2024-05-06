<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\models;

use Yii;
use yii\base\Model;
use yii\web\UnauthorizedHttpException;
use yii\di\Instance;
use Lcobucci\JWT\Token;
use shopack\base\backend\auth\Jwt;

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
      $jwt = Instance::ensure($this->jwtComponentKey, Jwt::class);
      $this->JWTComponent = $jwt;
    }
    return $this->JWTComponent;
  }

  public function processToken(string $data): ?Token
  {
    $token = $this->getJwtComponent()->parse($data);

    $this->getJwtComponent()->assert($token);
    // if ($this->getJwtComponent()->validate($token) == false)
    //   throw new UnauthorizedHttpException('Invalid Token');

    return $token;
  }

	public function process()
	{
    if ($this->validate() == false)
      throw new UnauthorizedHttpException(implode("\n", $this->getFirstErrors()));

    $token = $this->processToken($this->token);
    /*
      "iat": 1715021133.20162,
      "exp": 1715107533.20162,
      "uid": 20246,
      "rmmbr": 1,
      "2fa": 1,
      "type": "ssid",
      "email": "536@4797.dom"
    */

    $result = Yii::$app->twoFAManager->validate($token->claims()->get('type'), [
      'userID' => $token->claims()->get('uid'),
      'code' => $this->code,
    ]);

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
