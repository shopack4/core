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
use shopack\base\backend\helpers\AuthHelper;

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

  public function getTokenClaims(string $data): array
  {
    $token = $this->getJwtComponent()->parse($data);

    $this->getJwtComponent()->assert($token);
    // if ($this->getJwtComponent()->validate($token) == false)
    //   throw new UnauthorizedHttpException('Invalid Token');

    return $token->claims()->all();
  }

	public function process()
	{
    if ($this->validate() == false)
      throw new UnauthorizedHttpException(implode("\n", $this->getFirstErrors()));

    $challengeToken = $this->getTokenClaims($this->token);
    /*
      "iat"   : 1715021133.20162,
      "exp"   : 1715107533.20162,
      "uid"   : 20246,
      "rmmbr" : 1,
      "2fa"   : 1,
      "type"  : "ssid",
      "email" : "536@4797.dom"
    */

    $userID = $challengeToken['uid'];

    $result = Yii::$app->twoFAManager->validate($challengeToken['type'],
      $userID,
      [
        'code' => $this->code,
      ]
    );

    /*
    $result = ApprovalRequestModel::acceptCode($bodyParams['key'], $bodyParams['value']);
    $userModel = $result['userModel'];
    */

    //login?
    if (array_key_exists('rmmbr', $challengeToken)) {
      $userModel = UserModel::findOne($userID);

      list ($token, $mustApprove, $sessionModel, $challenge) = AuthHelper::doLogin(
        $userModel,
        $challengeToken['rmmbr']
      );

      return [
        'token' => $token,
        'mustApprove' => $mustApprove,
      ];
    }

    return [
      'result' => true,
    ];

    // throw new UnauthorizedHttpException("could not login.");
  }

}
