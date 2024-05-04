<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\components;

use Yii;
use yii\base\Component;
use shopack\aaa\common\enums\enuTwoFAType;
use shopack\aaa\backend\classes\twoFA\ITwoFA;

class TwoFAManager extends Component
{
	public static $drivers = [
		enuTwoFAType::SSID				=> \shopack\aaa\backend\extensions\twoFA\SSIDTwoFA::class,
		enuTwoFAType::BirthCertID	=> \shopack\aaa\backend\extensions\twoFA\BirthCertIDTwoFA::class,
		enuTwoFAType::BirthDate		=> \shopack\aaa\backend\extensions\twoFA\BirthDateTwoFA::class,
		enuTwoFAType::SMSOTP			=> \shopack\aaa\backend\extensions\twoFA\SMSOTPTwoFA::class,
		// enuTwoFAType::GoogleAuth	=> \shopack\aaa\backend\extensions\twoFA\GoogleAuthTwoFA::class,
		// enuTwoFAType::MSAuth			=> \shopack\aaa\backend\extensions\twoFA\MicrosoftAuthTwoFA::class,
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

	public function generate($type, ?array $args = [])
	{
		$driver = self::getDriver($type);
		return $driver->generate($args);
	}

	public function validate($type, ?array $args = [])
	{
		$driver = self::getDriver($type);
		return $driver->validate($args);
	}

}
