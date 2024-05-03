<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\components;

use shopack\aaa\backend\classes\twoFA\BaseTwoFA;
use Yii;
use yii\base\Component;
use shopack\aaa\backend\classes\twoFA\ITwoFA;

class TwoFAManager extends Component
{
	const TYPE_SMSOTP				= 'smsOtp';
	const TYPE_SSID					= 'ssid';
	const TYPE_BirthCertID	= 'birthCertID';
	const TYPE_BirthDate		= 'birthDate';
	const TYPE_GoogleAuth		= 'googleAuth';

	public static $drivers = [
		self::TYPE_SMSOTP				=> \shopack\aaa\backend\extensions\twoFA\SMSOTPTwoFA::class,
		self::TYPE_SSID					=> \shopack\aaa\backend\extensions\twoFA\SSIDTwoFA::class,
		self::TYPE_BirthCertID	=> \shopack\aaa\backend\extensions\twoFA\BirthCertIDTwoFA::class,
		self::TYPE_BirthDate		=> \shopack\aaa\backend\extensions\twoFA\BirthDateTwoFA::class,
		self::TYPE_GoogleAuth		=> \shopack\aaa\backend\extensions\twoFA\GoogleAuthTwoFA::class,
	];

	public static function getDriver($type) : ITwoFA
	{
		if (self::$drivers[$type] instanceof ITwoFA) {
			$driver = self::$drivers[$type];
		} else {
			$driver = self::$drivers[$type] = Yii::createObject(self::$drivers[$type]);
		}

		return $driver;
	}

	public function generate($type, $args = [])
	{
		$driver = self::getDriver($type);
		return $driver->generate($args);
	}

}
