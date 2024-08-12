<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\auth;

use DateTimeImmutable;
use Lcobucci\JWT\ClaimsFormatter;
use Lcobucci\JWT\Token\RegisteredClaims;

class JwtDateTimeFormatter implements ClaimsFormatter
{
	public function formatClaims(array $claims): array
	{
		//KEY_LONG_EXPIRATION
		$keys = array_merge(RegisteredClaims::DATE_CLAIMS, ['lexp']);

		foreach ($keys as $claim) {
			if (! array_key_exists($claim, $claims)) {
				continue;
			}

			$value = $claims[$claim];
			if ($value instanceof DateTimeImmutable)
				$claims[$claim] = $this->convertDate($value);
		}

		return $claims;
	}

	public function convertDate(DateTimeImmutable $date)
	{
		return (int) $date->format('U');
	}

}
