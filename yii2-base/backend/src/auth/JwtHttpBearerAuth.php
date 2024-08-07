<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\auth;

use bizley\jwt\JwtHttpBearerAuth as BaseJwtHttpBearerAuth;
use Lcobucci\JWT\Token;
use yii\web\UnauthorizedHttpException;

class JwtHttpBearerAuth extends BaseJwtHttpBearerAuth
{
	public function init(): void
	{
		parent::init();
	}

	public function processToken(string $data): ?Token
	{
		try
		{
			$token = $this->getJwtComponent()->parse($data);
			// $this->getJwtComponent()->assert($token);
		} catch (\Throwable $th) {
			// $rememberMe = $token->claims()->get('rmmbr');
			// if ($rememberMe) {
			// }

			//UnauthorizedHttpException : 401

			throw $th; // -> client must call auth/refresh-token if 401 raised
		}

		return $token;
	}

}
