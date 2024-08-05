<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\auth;

use bizley\jwt\JwtHttpBearerAuth as BaseJwtHttpBearerAuth;
use Lcobucci\JWT\Token;

class JwtHttpBearerAuth extends BaseJwtHttpBearerAuth
{
	public function init(): void
	{
		parent::init();
	}

	public function processToken(string $data): ?Token
	{
		$token = $this->getJwtComponent()->parse($data);

		try
		{
			$this->getJwtComponent()->assert($token);
		}
		catch (\Throwable $th)
		{
			// ssnSessionExpireAt
			// ssnOldJwt
			// ssnRefreshedAt
			// ssnRefreshCount
			// ssnLockedAt
			// ssnLockedBy

			// $rememberMe = $token->claims()->get('rmmbr');
			// if ($rememberMe) {
			// }

			//UnauthorizedHttpException : 401

			throw $th; // -> client must call auth/refresh-token if 401 raised
		}

		return $token;
	}

}
