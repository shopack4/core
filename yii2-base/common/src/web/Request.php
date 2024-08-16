<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\web;

use Yii;

class Request extends \yii\web\Request
{
	const HEADER_X_TIMEZONE = 'X-Time-Zone';

	public function init()
	{
		parent::init();

		//set time zone to formatter
		$requestTimeZone = $this->getHeaders()->get('x-time-zone'); //self::HEADER_X_TIMEZONE);
		$timeZone = $requestTimeZone ?? $_COOKIE['_timezone'] ?? '+00:00';
		if (strpos($timeZone, ':') === false)
			$timeZone = '+00:00';
		Yii::$app->formatter->timeZone = 'GMT' . $timeZone;

		//define form data parser: just for non POST requests:
		if (empty($this->parsers['multipart/form-data']))
			$this->parsers['multipart/form-data'] = \yii\web\MultipartFormDataParser::class;
	}

	/**
	 * $key: string|array
	 */
	public static function GetOrPost($key, $def=null)
	{
		$bodyParams = Yii::$app->request->getBodyParams();

		if (!is_array($key))
			$key = [$key];

		foreach ($key as $k)
		{
			if (isset($bodyParams[$k]))
				return $bodyParams[$k];

			if (isset($_GET[$k]))
				return $_GET[$k];
		}

		return $def;
	}

}
