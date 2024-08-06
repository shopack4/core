<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\auth;

use bizley\jwt\Jwt as BaseJwt;
use Lcobucci\JWT\ClaimsFormatter;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Token;

class Jwt extends BaseJwt
{
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

	public function sanityCheck($jwt): bool
	{
		$configuration = $this->getConfiguration();
		$token = $jwt instanceof Token ? $jwt : $this->parse($jwt);

		$signer = $jwt->getConfiguration()->signer();
		$signingKey = $jwt->getConfiguration()->signingKey();
		$constraints = [
			new \Lcobucci\JWT\Validation\Constraint\SignedWith($signer, $signingKey),
		];

		return $configuration->validator()->validate($token, ...$constraints);
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