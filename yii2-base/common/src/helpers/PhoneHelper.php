<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\helpers;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;

class PhoneHelper
{
	static function normalizePhoneNumber($phone, $country = 'IR')
	{
		try {
			$phoneUtil = PhoneNumberUtil::getInstance();
			$phoneNumber = $phoneUtil->parse($phone, $country);

			if ($phoneUtil->isValidNumber($phoneNumber))
				return $phoneUtil->format($phoneNumber, PhoneNumberFormat::E164);

			return false;

		} catch (\Throwable $exp) {
			// echo "Error. phone: " . $phone;
			throw $exp;
		}
	}

	static function formatPhoneNumber($phone, $format = PhoneNumberFormat::E164, $country = 'IR')
	{
		try {
			$phoneUtil = PhoneNumberUtil::getInstance();
			$phoneNumber = $phoneUtil->parse($phone, $country);

			if ($phoneUtil->isValidNumber($phoneNumber))
				return $phoneUtil->format($phoneNumber, $format);

		} catch (\Throwable $exp) { ; }

		return $phone;
	}

}
