<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuTwoFAType extends BaseEnum
{
	const SSID				= 'ssid';
	const BirthCertID	= 'birthCertID';
	const BirthDate		= 'birthDate';
	const SMSOTP			= 'smsOtp';
	// const GoogleAuth	= 'googleAuth';
	// const MSAuth			= 'msAuth';

	public static $messageCategory = 'aaa';

	public static $list = [
		self::SSID				=> 'SSID',
		self::BirthCertID	=> 'Birth Cert ID',
		self::BirthDate		=> 'Birth Date',
		self::SMSOTP			=> 'SMS OTP',
		// self::GoogleAuth	=> 'Google Authenticator',
		// self::MSAuth			=> 'Microsoft Authenticator',
	];

};
