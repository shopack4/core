<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\helpers;

use InvalidArgumentException;
use Yii;
// use yii\base\InvalidParamException;
use yii\base\InvalidParamException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;
// use GuzzleHttp\Exception\ClientException;
// use GuzzleHttp\Exception\ConnectException;
// use GuzzleHttp\Exception\InvalidArgumentException;
// use GuzzleHttp\Exception\RequestException;
// use Psr\Http\Message\ResponseInterface;
use shopack\base\common\auth\AuthHelper;
use shopack\base\common\helpers\Json;
use shopack\base\common\classes\Curl;
use shopack\base\common\classes\GuzzleHttpClient;
use shopack\base\common\classes\JsonUnserializer;
use shopack\base\common\classes\UnserializerInterface;
use shopack\base\common\web\Request;

class HttpHelper
{
	const METHOD_GET			= 'GET';
	const METHOD_HEAD			= 'HEAD';
	const METHOD_POST			= 'POST';
	const METHOD_PUT			= 'PUT';
	const METHOD_PATCH		= 'PATCH';
	const METHOD_DELETE		= 'DELETE';
	const METHOD_UNDELETE	= 'UNDELETE';
	const METHOD_OPTIONS	= 'OPTIONS';

	const PROVIDER_CURL   = 'curl';
	const PROVIDER_GUZZLE = 'guzzle';

	// public static $provider = self::PROVIDER_CURL;
	public static $provider = self::PROVIDER_GUZZLE;
	// public static $provider = (YII_ENV_DEV ? self::PROVIDER_GUZZLE : self::PROVIDER_CURL);

	public static $unserializers = [
	  'application/json' => [
	    'class' => JsonUnserializer::class
	  ]
	];

	static function callApi(
		$url,
		$method = Curl::METHOD_GET,
		$urlParams = null,
		$bodyParams = null,
		$formFiles = null,
		$callOptions = null
	) {
		if ($urlParams === null)		$urlParams = [];
		if ($bodyParams === null)		$bodyParams = [];
		if ($formFiles === null)		$formFiles = [];
		if ($callOptions === null)	$callOptions = [];

		$method = strtoupper($method);

		$url = ltrim(rtrim($url, '/'), '/');
		if (empty($url))
			throw new ServerErrorHttpException('url is not defined');

		$isLocalApiServer = (preg_match('/^https?:\/\//i', $url) != 1);

		if ($isLocalApiServer) {
			$apiServerAddress = Yii::$app->params['apiServerAddress'] ?? null;
			if (empty($apiServerAddress))
				throw new ServerErrorHttpException('apiServerAddress is not defined');

			$apiServerAddress = rtrim($apiServerAddress, '/');

			$url = $apiServerAddress . '/' . $url;

			// $clientConfig['base_uri'] = $apiServerAddress;

			//
			if (Yii::$app->isJustForMe)
				$urlParams['justForMe'] = 1;

			$timeZone = $_COOKIE['_timezone'] ?? null;
			if ($timeZone)
				$callOptions['headers'][Request::HEADER_X_TIMEZONE] = $timeZone;
		}

		$fnCallApi = function($provider) use (
			$isLocalApiServer,
			$url,
			$method,
			&$urlParams,
			&$bodyParams,
			&$formFiles,
			&$callOptions
		) {
			if ($provider == self::PROVIDER_CURL) {
				return self::callApi_curl(
					$isLocalApiServer,
					$url,
					$method,
					$urlParams,
					$bodyParams,
					$formFiles,
					$callOptions
				);
			}

			if ($provider == self::PROVIDER_GUZZLE) {
				return self::callApi_guzzle(
					$isLocalApiServer,
					$url,
					$method,
					$urlParams,
					$bodyParams,
					$formFiles,
					$callOptions
				);
			}

			throw new \Exception('unknown provider ' . $provider);
		};

		$apiRefreshTokenAddress = Yii::$app->params['apiRefreshTokenAddress'] ?? null;
		$urlIsRefreshToken = ($isLocalApiServer && str_ends_with($url, $apiRefreshTokenAddress));
		$urlIsLogout = ($isLocalApiServer && str_ends_with($url, 'aaa/auth/logout'));

		if ($isLocalApiServer && ($urlIsRefreshToken == false)) {
			if ((Yii::$app->user->isGuest == false) && isset(Yii::$app->user->identity->accessToken)) {
				$jwt = Yii::$app->user->identity->accessToken;
				if (empty($jwt) == false)
					$callOptions['headers']['Authorization'] = 'Bearer ' . $jwt;
			}

			if (empty($jwt) && method_exists(Yii::$app->user, 'getJwtByCookie')) {
				$jwt = Yii::$app->user->getJwtByCookie();
				if (empty($jwt) == false)
					$callOptions['headers']['Authorization'] = 'Bearer ' . $jwt;
			}

			if (empty($jwt) && Yii::$app->request->headers->has('Authorization')) {
				$callOptions['headers']['Authorization'] = Yii::$app->request->headers->get('Authorization');
			}
		}

		list ($resultStatus, $responseHeaders, $responseBody) = $fnCallApi(self::$provider);

		$refreshToken = (($resultStatus == 401)
			&& $isLocalApiServer
			&& ($urlIsRefreshToken == false)
			&& ($urlIsLogout == false)
			&& (empty($apiRefreshTokenAddress) == false)
			&& (Yii::$app->user->isGuest == false)
		);
		// 'Your request was made with invalid or expired JSON Web Token.'

		if ($refreshToken) {
			$newToken = AuthHelper::refreshToken(Yii::$app->user->identity->accessToken);

			if (empty($newToken)) {
				if (Yii::$app->isBackend)
					throw new UnauthorizedHttpException('could not refresh token');
				else {
					Yii::$app->user->logout();
					Yii::$app->response->redirect(Yii::$app->getHomeUrl());
					Yii::$app->response->send();
					die();
				}
			} else {
				if (Yii::$app->isBackend == false) {
					Yii::$app->user->replaceToken($newToken);
				}
			}

			$callOptions['headers']['Authorization'] = 'Bearer ' . $newToken;

			list ($resultStatus, $responseHeaders, $responseBody) = $fnCallApi(self::$provider);

			//still invalid jwt?
			if (($resultStatus == 401) && (Yii::$app->isBackend == false)) {
				Yii::$app->user->logout();
				Yii::$app->response->redirect(Yii::$app->getHomeUrl());
				Yii::$app->response->send();
				die();
			}
		}

		$result = [
			'status'	=> $resultStatus,
			'headers'	=> $responseHeaders,
			'body'		=> $responseBody
		];

		//todo: implement behaviors of replace new token
		// if (empty($newToken) == false)
		// 	$result['token'] = $newToken;

		return $result;
	}

	protected static function callApi_curl(
		$isLocalApiServer,
		$url,
		$method = Curl::METHOD_GET,
		$urlParams = [],
		$bodyParams = [],
		$formFiles = [],
		$options = []
	) {
		$curl = Curl::start($method, $url)
			->setUrlParams($urlParams)
			->setBodParams($bodyParams)
			->setFormFiles($formFiles)
			->setOptions($options)
		;

		list ($resultStatus, $responseHeaders, $responseBody) = $curl->execute();

		return self::formatResponse($resultStatus, $responseHeaders, $responseBody);
	}

	protected static function callApi_guzzle(
		$isLocalApiServer,
		$url,
		$method = Curl::METHOD_GET,
		$urlParams = [],
		$bodyParams = [],
		$formFiles = [],
		$options = []
	) {
		$clientConfig = [];
		$callOptions = [];

		// if ((str_starts_with($url, 'http://') == false)
		// 	&& (str_starts_with($url, 'https://') == false)
		// ) {
		// 	$apiServerAddress = Yii::$app->params['apiServerAddress'] ?? null;
		// 	if (empty($apiServerAddress))
		// 		throw new ServerErrorHttpException('apiServerAddress is not defined');

		// 	if (str_ends_with($apiServerAddress, '/'))
		// 		$apiServerAddress = rtrim($apiServerAddress, '/');

		// 	if (str_starts_with($url, '/'))
		// 		$url = ltrim($url, '/');

		// 	$url = $apiServerAddress . '/' . $url;

		// 	$clientConfig['base_uri'] = $apiServerAddress;

		// 	$isLocalApiServer = true;

		// } else
		// 	$clientConfig['base_uri'] = $url;

		//Url params
		if (empty($urlParams) == false) {
			$UrlParamsParts = [];

			foreach ($urlParams as $k => $v) {
				if (($v === null) || ($v === ''))
					$UrlParamsParts[$k] = null;
				else if (is_array($v))
					$UrlParamsParts[$k] = implode(',', $v);
				// else if ($v == '')
				// 	$UrlParamsParts[$k] = $k;
				else
					$UrlParamsParts[$k] = (is_numeric($v) ? (int)$v : $v);
			}

			if (empty($UrlParamsParts) == false) {
				$callOptions['query'] = $UrlParamsParts;
				// $url = $url . '?' . implode('&', $UrlParamsParts);
			}
		}

		//
		$postFields = []; //$bodyParams;

		//body params
		if ((empty($bodyParams) == false)
			&& in_array($method, [self::METHOD_POST, self::METHOD_PUT, self::METHOD_PATCH])
		) {
			$postFields = array_merge($postFields, $bodyParams);
		}

		$hasFileData = false;
		$attrs = [];

		//form files
		if (empty($formFiles) == false) {
			//POST=create, PUT,PATCH=update
			if (in_array($method, [self::METHOD_POST, self::METHOD_PUT, self::METHOD_PATCH]) == false) {
				throw new ServerErrorHttpException('form files only allowed with post, put or patch methods.');
			}

			foreach ($formFiles as $attrID => $attrContent) {
				$attrs[] = [
					'name'      => $attrID,
					'contents'  => fopen($attrContent['tempFileName'], 'r'),
					'filename'  => $attrContent['fileName'],
				];
			}

			$hasFileData = true;
		}

		if (empty($postFields) == false) {
			foreach ($postFields as $attrID => $attrContent) {
				if ($hasFileData) {
					$attrs[] = [
						'name'    => $attrID,
						'contents' => (is_array($attrContent) && (empty($attrContent) == false) ? json_encode($attrContent) : $attrContent),
					];
				} else {
					$attrs[$attrID] = (is_array($attrContent) && (empty($attrContent) == false) ? json_encode($attrContent) : $attrContent);
				}
			}
		}

		if (empty($attrs) == false) {
			$fieldsKey = $hasFileData ? 'multipart' : 'json';
			$callOptions[$fieldsKey] = $attrs;
		}

		//headers
		$headers = $options['headers'] ?? [];

		if ($isLocalApiServer) {
			$headers['Origin'] = rtrim(Url::to(['/'], true), '/\\');

			$headers['Accept'] = 'application/json';
			// $headers[] = 'Content-Type: application/json';

			$currentLanguage = LanguageHelper::getCurrentLanguage();
			if (empty($currentLanguage) == false)
				$headers['Accept-Language'] = $currentLanguage;

			//moved to client
			// if (Yii::$app->request->headers->has('Authorization'))
			//   $headers['Authorization'] = Yii::$app->request->headers->get('Authorization');
			// else if (method_exists(Yii::$app->user, 'getJwtByCookie')) {
			//   $jwt = Yii::$app->user->getJwtByCookie();
			//   if ($jwt !== null) {
			//     $headers['Authorization'] = 'Bearer ' . $jwt;
			//   }
			// }
		}

		$clientConfig['headers'] = $headers;

		if (defined('YII_DEV_LOCAL_PROXY')) {
			$clientConfig['proxy'] = constant('YII_DEV_LOCAL_PROXY');
		}

		$httpClient = new GuzzleHttpClient($clientConfig);

		// $response = self::_guzzleRequest($httpClient, $method, $url, $callOptions);
		$response = $httpClient->{$method}($url, $callOptions);

		$resultStatus = $response->getStatusCode();
		$responseHeaders = $response->getHeaders();
		// $responseBody = self::_unserializeResponseBody($response);
		$responseBody = (string)$response->getBody();

		return self::formatResponse($resultStatus, $responseHeaders, $responseBody);
	}

	protected static function unserializeResponseBody($resultStatus, $responseHeaders, $responseBody)
	{
		$contentType = implode(', ', $responseHeaders['Content-Type'] ?? []);

		try {
			if ((stripos($contentType, 'application/json') !== false)
				&& isset(self::$unserializers['application/json'])
			) {
				$unserializer = \Yii::createObject(self::$unserializers['application/json']);

				if ($unserializer instanceof UnserializerInterface) {
					return $unserializer->unserialize($responseBody, true);
				}
			}

			return $responseBody;

		} catch (InvalidArgumentException $e) {
			return $responseBody;

		} catch (InvalidParamException $e) {
			return $responseBody;
		}
	}

	protected static function formatResponse($resultStatus, $responseHeaders, $responseBody)
	{
		if ((empty($responseHeaders) == false) && is_string($responseHeaders)) {
			$h = explode("\n", $responseHeaders);
			$responseHeaders = [];
			foreach ($h as $i => $v) {
				if ($i == 0) {
					$responseHeaders[] = $v;
				} else {
					$parts = explode(':', $v);

					$headerKey = trim(array_shift($parts));
					if (isset($responseHeaders[$headerKey]) == false)
						$responseHeaders[$headerKey] = [];

					$responseHeaders[$headerKey][] = trim(implode(':', $parts));
				}
			}
		}

		if (YII_DEBUG) {
			Yii::info([
				'status'	=> $resultStatus,
				'headers'	=> $responseHeaders,
				'body'		=> $responseBody,
			], __METHOD__);
		}

		$resultBody = [];

		if (is_string($responseBody)) {
			$responseBody = self::unserializeResponseBody($resultStatus, $responseHeaders, $responseBody);

			// //json null
			// if (strcasecmp($responseBody, 'null') == 0)
			// 	$responseBody = null;

			// //convert $responseBody string to json array
			// if (empty($responseBody) == false) {
			// 	$org = $responseBody;

			// 	try {
			// 		$responseBody = Json::decode($responseBody);
			// 	} catch (\Throwable $th) {
			// 		//throw $th;
			// 		Yii::error($th, __METHOD__);
			// 		$responseBody = null;
			// 	}

			// 	if ($responseBody === null) {
			// 		$responseBody = [
			// 			'message' => $org,
			// 		];
			// 	}
			// }
		}

		/*
			$responseBody:
			{
				"name": "Unauthorized",
				"message": "{\"0\":\"THE_WAITING_TIME_HAS_NOT_ELAPSED\",\"ttl\":67,\"remained\":\"1:7\"}",
				"code": 0,
				"status": 401,
				"type": "yii\\web\\HttpException"
			}

			out:
				$status = 401
				$result = [
					"message": "THE_WAITING_TIME_HAS_NOT_ELAPSED"
					"ttl": 120,
					"remained": "2:0"
				]
		*/
		if ($resultStatus < 200 || $resultStatus >= 300) {
			if (isset($responseBody['message'])) {
				try {
					$json = Json::decode($responseBody['message']);
				} catch (\Throwable $th) { }

				if (empty($json))
					$resultBody = [
						'message' => $responseBody['message']
					];
				else
					$resultBody = [
						'message' => $json
					];
			} else {
				$resultBody = [
					'message' => 'UNKNOWN_ERROR',
				];
			}
		}

		/*
			$responseBody:
			{
				"message": {
					"0": "CODE_SENT",
					"ttl": 120,
					"remained": "2:0"
				}
			}

			out:
				$status = 200 .. 299
				$result = [
					"message": "CODE_SENT"
					"ttl": 120,
					"remained": "2:0"
				]
		*/
		else if (isset($responseBody['message'])) {
			$message = (array)$responseBody['message'];
			unset($responseBody['message']);

			$resultBody = [
				'message' => array_shift($message),
			];

			if (empty($message == false))
				$resultBody = array_merge($resultBody, $message);

			if (empty($responseBody) == false)
				$resultBody = array_merge($resultBody, $responseBody);
		}

		/*
			$responseBody:
			{
				totalCount: 100,
				rows: [
					{
						"gtwID": 1,
						"gtwName": "asanak 1",
					},
					{
						"gtwID": 2,
						"gtwName": "asanak 2",
					},
				]
			}

			out:
				$status = 200 .. 299
				$result = [
					'totalCount': 100,
					'rows': [
						{
							"gtwID": 1,
							"gtwName": "asanak 1",
						},
						{
							"gtwID": 2,
							"gtwName": "asanak 2",
						},
					],
				],
			]
		*/
		else if (isset($responseBody['rows'])) {
			$resultBody = $responseBody;
		}

		else if (isset($responseBody['data'])) {
			$resultBody = $responseBody;
		}

		else
			$resultBody = $responseBody;

		return [$resultStatus, $responseHeaders, $resultBody];
	}

	public static function formatApiResponseIfFailed($apiResponse, $messageCategory)
	{
		if ($apiResponse['status'] < 200 || $apiResponse['status'] >= 300) {
			$message = $apiResponse['body']['message'];

			if (is_array($message)) {
				$msg = array_shift($message);
				$message = Yii::t($messageCategory, $msg, $message);
			} else {
				$message = Yii::t($messageCategory, $message, $apiResponse['body']);
			}

			return $message;
		}

		return null;
	}

	public static function throwApiResponseIfFailed($apiResponse, $messageCategory)
	{
		$message = self::formatApiResponseIfFailed($apiResponse, $messageCategory);

		if (empty($message) == false) {
			throw new \yii\web\HttpException($apiResponse['status'], $message);
		}
	}

	// moved to GuzzleHttpClient:
	/*
	protected static function _guzzleRequest($httpClient, $method, $url, array $options)
	{
		$rawQuery = $url;
		if (empty($options['query']) == false) {
			$rawQuery .= '?';
			$rawQuery .= \http_build_query($options['query'], '', '&');
		}

		$loggingCategory = __METHOD__ . '(' . $method . ')';
		$profile = YII_DEBUG; //$this->enableProfiling

		// if ($this->enableLogging)
		Yii::info($rawQuery, $loggingCategory);

		$profile and Yii::beginProfile($rawQuery, $loggingCategory);
		try {
			$response = $httpClient->{$method}($url, $options);
			$profile and Yii::endProfile($rawQuery, $loggingCategory);

		} catch (ClientException $e) {
			$profile and Yii::endProfile($rawQuery, $loggingCategory);
			$response = $e->getResponse();

		} catch (ConnectException|RequestException $e) {
			$profile and Yii::endProfile($rawQuery, $loggingCategory);
			throw new ServerErrorHttpException(get_class($e) . ': url=' . $url . ' ' . $e->getMessage(), 500);
		}

		return $response;
	}
	*/

	// protected static function _unserializeResponseBody(ResponseInterface $response)
	// {
	//   $body = (string) $response->getBody();
	//   $contentType = $response->getHeaderLine('Content-type');

	//   try {
	//     if (false !== stripos($contentType, 'application/json')
	//       && isset(self::$unserializers['application/json'])
	//     ) {
	//       /** @var UnserializerInterface $unserializer */
	//       $unserializer = \Yii::createObject(self::$unserializers['application/json']);
	//       if ($unserializer instanceof UnserializerInterface) {
	//         return $unserializer->unserialize($body, false);
	//       }
	//     }

	//     return $body;

	//   } catch (InvalidArgumentException|InvalidParamException $e) {
	//     return $body;

	//   } catch (InvalidParamException $e) {
	//     return $body;
	//   }
	// }

}
