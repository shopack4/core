<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\auth;

use bizley\jwt\Jwt as BaseJwt;
use Lcobucci\JWT\ClaimsFormatter;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\MicrosecondBasedDateConversion;
use Lcobucci\JWT\Encoding\UnifyAudience;
use Lcobucci\JWT\Token;
use shopack\base\common\auth\JwtDateTimeFormatter;
use yii\web\UnauthorizedHttpException;

class Jwt extends BaseJwt
{
	public const KEY_LONG_EXPIRATION	= 'lexp';

	public const VALIDATE_NONE		= '-';
	public const VALIDATE_SANITY	= 's';
	public const VALIDATE_FULL		= 'f';

	public function init(): void
	{
		parent::init();

		$this->validationConstraints = function (\bizley\jwt\Jwt $jwt) {
			$configuration = $jwt->getConfiguration();

			$signer = $configuration->signer();
			$verificationKey = $configuration->verificationKey();

			return [
				new \Lcobucci\JWT\Validation\Constraint\SignedWith($signer, $verificationKey),
				new \Lcobucci\JWT\Validation\Constraint\ValidAt(\Lcobucci\Clock\FrozenClock::fromUTC()),
			];
		};
	}

	public function verifySignature($jwt)
	{
		$token = $jwt instanceof Token ? $jwt : $this->parse($jwt, false);

		$configuration = $this->getConfiguration();

		$signer = $configuration->signer();
		$verificationKey = $configuration->verificationKey();

		$constraints = [
			new \Lcobucci\JWT\Validation\Constraint\SignedWith($signer, $verificationKey),
		];

		return $configuration->validator()->validate($token, ...$constraints);
	}
	public function assertSignature($jwt)
	{
		if ($this->verifySignature($jwt) === false)
			throw new UnauthorizedHttpException('Invalid token sign');
	}

	public function verifyTokenExpiration($jwt)
	{
		$token = $jwt instanceof Token ? $jwt : $this->parse($jwt, false);

		$configuration = $this->getConfiguration();

		$constraints = [
			new \Lcobucci\JWT\Validation\Constraint\ValidAt(\Lcobucci\Clock\FrozenClock::fromUTC()),
		];

		return $configuration->validator()->validate($token, ...$constraints);
	}
	public function assertTokenExpiration($jwt)
	{
		if ($this->verifyTokenExpiration($jwt) === false)
			throw new UnauthorizedHttpException('The token expired');
	}

	public function verifySessionExpiration($jwt)
	{
		$token = $jwt instanceof Token ? $jwt : $this->parse($jwt, false);

		$exp = $token->claims()->get(self::KEY_LONG_EXPIRATION);

		if (empty($exp))
			return false;

		if (($exp instanceof \DateTimeImmutable) == false) {
			$exp = number_format((float)$exp, 6, '.', '');
			$exp = \DateTimeImmutable::createFromFormat('U.u', $exp);
		}

		$now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

		return ($now < $exp);
	}
	public function assertSessionExpiration($jwt)
	{
		if ($this->verifySessionExpiration($jwt) === false)
			throw new UnauthorizedHttpException('the Session expired');
	}

	/**
	 * $validate : int (0=none, 1:sanity, 2:full)
	 */
	public function parse(string $jwt, $validate = self::VALIDATE_FULL): Token
	{
		$token = parent::parse($jwt);

		try {
			if ($validate == self::VALIDATE_SANITY) {
				$this->assertSignature($token);
			} else if ($validate == self::VALIDATE_FULL) {
				$this->assert($token);
			}
		} catch (\Throwable $th) {
			throw new UnauthorizedHttpException('token validation failed', 0, $th);
		}

		return $token;
	}

	public function getBuilder(?ClaimsFormatter $claimFormatter = null): Builder
	{
		// if (empty($claimFormatter))
		// 	$claimFormatter = ChainedFormatter::default();

		// foreach ($claimFormatter->formatters as $k => $formatter)
		// {
		// 	if (is_a($formatter, MicrosecondBasedDateConversion::class)) {
		// 		$formatter = new JwtDateTimeFormatter();
		// 		$claimFormatter->formatters[$k] = $formatter;
		// 	}
		// }

		$claimFormatter = new ChainedFormatter(
			new UnifyAudience(),
			new JwtDateTimeFormatter()
		);

		return parent::getBuilder($claimFormatter);
	}

}