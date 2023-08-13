<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\helpers;

use Yii;

class StringHelper extends \yii\helpers\StringHelper
{
	public static function fixPersianCharacters($str)
	{
		if (empty($str))
			return $str;

		//https://www.fileformat.info/info/unicode/category/Nd/list.htm
		$map = [
			/* ٠ */ "\u{0660}" => '0',
			/* ١ */ "\u{0661}" => '1',
			/* ٢ */ "\u{0662}" => '2',
			/* ٣ */ "\u{0663}" => '3',
			/* ٤ */ "\u{0664}" => '4',
			/* ٥ */ "\u{0665}" => '5',
			/* ٦ */ "\u{0666}" => '6',
			/* ٧ */ "\u{0667}" => '7',
			/* ٨ */ "\u{0668}" => '8',
			/* ٩ */ "\u{0669}" => '9',

			/* ۰ */ "\u{06f0}" => '0',
			/* ۱ */ "\u{06f1}" => '1',
			/* ۲ */ "\u{06f2}" => '2',
			/* ۳ */ "\u{06f3}" => '3',
			/* ۴ */ "\u{06f4}" => '4',
			/* ۵ */ "\u{06f5}" => '5',
			/* ۶ */ "\u{06f6}" => '6',
			/* ۷ */ "\u{06f7}" => '7',
			/* ۸ */ "\u{06f8}" => '8',
			/* ۹ */ "\u{06f9}" => '9',

			'أ' => 'ا',

			/* ك */ "\u{0643}" => 'ک',
			/* ؠ */ "\u{0620}" => 'ی',
			/* ى */ "\u{0649}" => 'ی',
			/* ي */ "\u{064a}" => 'ی',
			/* ݷ */ "\u{0777}" => 'ی',

			// 'ئ'	=> 'ی',

			'{}' => '‌', //ZWNJ
			'>>' => '»',
			'<<' => '«',
		];

		foreach ($map as $k => $v) {
			$str = str_replace($k, $v, $str);
		}

		return $str;
	}

	// public static function startsWith($haystack, $needle)
	// {
		// return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
	// }
	// public static function endsWith($haystack, $needle)
	// {
		// return substr_compare($haystack, $needle, -strlen($needle)) === 0;
	// }

	public static function strrtrim($message, $strip)
	{
		// break message apart by strip string
		$lines = explode($strip, $message);
		$last  = '';
		// pop off empty strings at the end
		do {
			$last = array_pop($lines);
		} while (empty($last) && (count($lines)));
		// re-assemble what remains
		return implode($strip, array_merge($lines, array($last)));
	}

	public static function generateRandomId($length=32, $prefix='a')
	{
		if (empty($prefix))
			return preg_replace('/[^a-z0-9_]/i', '_', Yii::$app->security->generateRandomString($length));

		return $prefix . preg_replace('/[^a-z0-9_]/i', '_', Yii::$app->security->generateRandomString($length - strlen($prefix)));
	}

	public static function convertToJsVarName($varName)
	{
		if (empty($varName))
			return null;

		return preg_replace('/[^a-z0-9_]/i', '_', $varName);
	}

}
