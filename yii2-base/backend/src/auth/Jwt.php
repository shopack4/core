<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\auth;

use bizley\jwt\Jwt as BaseJwt;
use Lcobucci\JWT\ClaimsFormatter;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Token;
use yii\web\UnauthorizedHttpException;

class Jwt extends BaseJwt
{
	public const VALIDATE_NONE		= '-';
	public const VALIDATE_SANITY	= 's';
	public const VALIDATE_FULL		= 'f';

	public function init(): void
	{
		parent::init();

		$this->validationConstraints = function (\bizley\jwt\Jwt $jwt) {
			$signer = $jwt->getConfiguration()->signer();
			$signingKey = $jwt->getConfiguration()->signingKey();
			return [
				new \Lcobucci\JWT\Validation\Constraint\SignedWith($signer, $signingKey),
				new \Lcobucci\JWT\Validation\Constraint\ValidAt(\Lcobucci\Clock\FrozenClock::fromUTC()),
			];
		};
	}

	public function sanityCheck($jwt)
	{
		$configuration = $this->getConfiguration();
		$token = $jwt instanceof Token ? $jwt : $this->parse($jwt);

		$signer = $this->getConfiguration()->signer();
		$signingKey = $this->getConfiguration()->signingKey();
		$constraints = [
			new \Lcobucci\JWT\Validation\Constraint\SignedWith($signer, $signingKey),
		];

		if ($configuration->validator()->validate($token, ...$constraints) == false)
			throw new UnauthorizedHttpException('Invalid token sign');

		return $token;
	}

	/**
	 * $validate : int (0=none, 1:sanity, 2:full)
	 */
	public function parse(string $jwt, $validate = self::VALIDATE_FULL): Token
	{
		$token = parent::parse($jwt);

		if ($validate == self::VALIDATE_SANITY) {
			$this->sanityCheck($token);
		} else if ($validate == self::VALIDATE_FULL) {
			$this->assert($token);
		}

		return $token;
	}

	// public function getBuilder(?ClaimsFormatter $claimFormatter = null): Builder
	// {
	// 	$builder = parent::getBuilder($claimFormatter);

	// 	$now = new \DateTimeImmutable();
	// 	$expire = $now->modify("+{$this->ttl} second");

	// 	$builder
	// 		->issuedAt($now)
	// 		->expiresAt($expire)
	// 		;

	// 	return $builder;
	// }

}