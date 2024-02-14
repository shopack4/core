<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\helpers;

use yii\web\UnprocessableEntityHttpException;

class GeneralHelper
{
  const PHRASETYPE_EMAIL  = 'E';
  const PHRASETYPE_MOBILE = 'M';
  const PHRASETYPE_SSID   = 'S';
  const PHRASETYPE_NONE   = 'N';

  static function isValidIranSSID(string $ssid)
  {
    $ssid = trim($ssid);

    if (preg_match('/^[0-9]{8,10}$/', $ssid) == false)
      return false;

    if ((strlen($ssid) < 8) || (strlen($ssid) > 10))
      return false;

    while (strlen($ssid) != 10) {
      $ssid = '0' . $ssid;
    }

    $sum = 0;
    for ($i=0; $i<10; $i++) {
      if (preg_match('/^' . $i . '{10}$/', $ssid)) {
        return false;
      }

      if ($i < 9) {
        $sum = $sum + (intval(substr($ssid, $i, 1)) * (10 - $i));
      }
    }
    $rem = $sum % 11;

    $parity = intval(substr($ssid, 9, 1));
    if(($rem < 2 && $rem == $parity) || ($rem >= 2 && $rem == 11 - $parity))
      return true;

    return false;
  }

  static function isEmail($email)
  {
    if (strpos($email, '@') !== false) {
      if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
        return true;

      throw new UnprocessableEntityHttpException('Invalid email address');
    }

    return false;
  }

  static function recognizeLoginPhrase($input, $checkSSID = true)
  {
    $input = strtolower(trim($input));

    if (empty($input))
      return [$input, static::PHRASETYPE_NONE];

    //email
    if (static::isEmail($input))
      return [$input, static::PHRASETYPE_EMAIL];

    //ssid
    if ($checkSSID) {
      if (self::isValidIranSSID($input))
      // $sidMatched = preg_match('/^[0-9]{8,10}$/', $input);
      // if ($sidMatched === 1)
        return [$input, static::PHRASETYPE_SSID];
    }

    //mobile
    try {
      $phone = PhoneHelper::normalizePhoneNumber($input);
      if ($phone)
        return [$phone, static::PHRASETYPE_MOBILE];
    } catch(\Exception $exp) {
      $message = $exp->getMessage();
    }

    //
    return [$input, static::PHRASETYPE_NONE];
  }

  static function checkLoginPhrase($input, $checkSSID = true)
  {
    list ($normalizedInput, $type) = static::recognizeLoginPhrase($input, $checkSSID);

    if ($type == self::PHRASETYPE_NONE)
      throw new UnprocessableEntityHttpException('Invalid input');

    return [$normalizedInput, $type];
  }

  static function formatTimeFromSeconds($seconds)
  {
    $days = intval($seconds / (24 * 60 * 60));
    $seconds -= $days * (24 * 60 * 60);

    $hours = intval($seconds / (60 * 60));
    $seconds -= $hours * (60 * 60);

    $minutes = intval($seconds / 60);
    $seconds -= $minutes * 60;

    $parts = [];

    if ($days > 0)
      $parts[] = $days;

    if (($days > 0) || ($hours > 0))
      $parts[] = $hours;

    if (($days > 0) || ($hours > 0) || ($minutes > 0))
      $parts[] = $minutes;

    $parts[] = $seconds;

    $result = implode(':', $parts);

    if (count($parts) == 1)
      $result = '0:' . $result;

    return $result;
  }

}
