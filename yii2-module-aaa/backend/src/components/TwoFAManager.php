<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\components;

use Yii;
use yii\base\Component;
use shopack\aaa\common\enums\enuTwoFAType;
use shopack\aaa\backend\classes\twoFA\ITwoFA;
use yii\web\UnauthorizedHttpException;

class TwoFAManager extends Component
{
	public static $drivers = [
		enuTwoFAType::SSID				=> \shopack\aaa\backend\classes\twoFA\SSIDTwoFA::class,
		enuTwoFAType::BirthCertID	=> \shopack\aaa\backend\classes\twoFA\BirthCertIDTwoFA::class,
		enuTwoFAType::BirthDate		=> \shopack\aaa\backend\classes\twoFA\BirthDateTwoFA::class,
		enuTwoFAType::SMSOTP			=> \shopack\aaa\backend\classes\twoFA\SMSOTPTwoFA::class,
		// enuTwoFAType::GoogleAuth	=> \shopack\aaa\backend\classes\twoFA\GoogleAuthTwoFA::class,
		// enuTwoFAType::MSAuth			=> \shopack\aaa\backend\classes\twoFA\MicrosoftAuthTwoFA::class,
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

	public function generate($type, $userID, ?array $args = [])
	{
		$driver = self::getDriver($type);
		return $driver->generate($userID, $args);
	}

	public function validate($type, $userID, ?array $args = [])
	{
		$driver = self::getDriver($type);

		$result = $driver->validate($userID, $args);
		if ($result === false)
	    throw new UnauthorizedHttpException("Code not approved");

		return $result;
	}

}
