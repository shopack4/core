<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\helpers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\ServerErrorHttpException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\InvalidArgumentException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use shopack\base\common\helpers\Json;
use shopack\base\common\classes\Curl;

class HttpHelper
{
  const METHOD_GET     = 'GET';
  const METHOD_HEAD    = 'HEAD';
  const METHOD_POST    = 'POST';
  const METHOD_PUT     = 'PUT';
  const METHOD_PATCH   = 'PATCH';
  const METHOD_DELETE  = 'DELETE';
  const METHOD_OPTIONS = 'OPTIONS';

  const PROVIDER_CURL   = 'curl';
  const PROVIDER_GUZZLE = 'guzzle';

  // public static $provider = self::PROVIDER_CURL;
  public static $provider = self::PROVIDER_GUZZLE;

  // public static $unserializers = [
  //   'application/json' => [
  //     'class' => 'shopack\base\frontend\common\rest\JsonUnserializer'
  //   ]
  // ];

  static function callApi(
    $url,
    $method = Curl::METHOD_GET,
    $urlParams = [],
    $bodyParams = [],
    $formFiles = [],
    $options = []
  ) {
    if (self::$provider == self::PROVIDER_CURL) {
      return self::callApi_curl(
        $url,
        $method,
        $urlParams,
        $bodyParams,
        $formFiles,
        $options
      );
    }

    if (self::$provider == self::PROVIDER_GUZZLE) {
      return self::callApi_guzzle(
        $url,
        $method,
        $urlParams,
        $bodyParams,
        $formFiles,
        $options
      );
    }

    throw new \Exception('unknown provider ' . self::$provider);
  }

  protected static function callApi_curl(
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

    list ($resultStatus, $response) = $curl->execute();
    $resultData = [];

    /*
      $response:
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
      if (isset($response['message'])) {
        try {
          $json = Json::decode($response['message']);
        } catch (\Throwable $th) { }

        if (empty($json))
          $resultData = [
            'message' => $response['message']
          ];
        else
          $resultData = [
            'message' => $json
          ];
      } else {
        $resultData = [
          'message' => 'UNKNOWN_ERROR',
        ];
      }
    }

    /*
      $response:
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
    else if (isset($response['message'])) {
      $message = (array)$response['message'];
      unset($response['message']);

      $resultData = [
        'message' => array_shift($message),
      ];

      if (empty($message == false))
        $resultData = array_merge($resultData, $message);

      if (empty($response) == false)
        $resultData = array_merge($resultData, $response);
    }

    /*
      $response:
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
    else if (isset($response['rows'])) {
      $resultData = $response;
    }

    else if (isset($response['data'])) {
      $resultData = $response;
    }

    else
      $resultData = $response;

    return [$resultStatus, $resultData];
  }

  public static function formatResultIfFailed(
    $messageCategory,
    $resultStatus,
    $resultData
  ) {
    if ($resultStatus < 200 || $resultStatus >= 300) {
      $message = $resultData['message'];

      if (is_array($message)) {
        $msg = array_shift($message);
        $message = Yii::t($messageCategory, $msg, $message);
      } else {
        $message = Yii::t($messageCategory, $message, $resultData);
      }

      return $message;
    }

    return null;
  }

  public static function throwResultIfFailed(
    $messageCategory,
    $resultStatus,
    $resultData
  ) {
    $message = self::formatResultIfFailed(
      $messageCategory,
      $resultStatus,
      $resultData
    );

    if (empty($message) == false) {
      throw new \yii\web\HttpException($resultStatus, $message);
    }
  }

  protected static function callApi_guzzle(
    $url,
    $method = Curl::METHOD_GET,
    $urlParams = [],
    $bodyParams = [],
    $formFiles = [],
    $options = []
  ) {
    $clientConfig = [];
    $callOptions = [];

    //set address
    $isLocalApiServer = false;

    if ((str_starts_with($url, 'http://') == false)
      && (str_starts_with($url, 'https://') == false)
    ) {
      $apiServerAddress = Yii::$app->params['apiServerAddress'] ?? null;
      if (empty($apiServerAddress))
        throw new ServerErrorHttpException('apiServerAddress is not defined');

      if (str_ends_with($apiServerAddress, '/'))
        $apiServerAddress = rtrim($apiServerAddress, '/');

      if (str_starts_with($url, '/'))
        $url = ltrim($url, '/');

      $url = $apiServerAddress . '/' . $url;

      $clientConfig['base_uri'] = $apiServerAddress;

      $isLocalApiServer = true;

    } else
      $clientConfig['base_uri'] = $url;

    //justForMe
    if ($isLocalApiServer) {
      $urlParams['caller-address'] = Url::to(['/'], true);

      if (Yii::$app->isJustForMe)
        $urlParams['justForMe'] = 1;
    }

    //Url params
    if (empty($urlParams) == false) {
      $UrlParamsParts = [];

      foreach ($urlParams as $k => $v) {
        if (is_array($v))
          $UrlParamsParts[$k] = implode(',', $v);
        else if ($v == '')
          $UrlParamsParts[$k] = $k;
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
    if (empty($bodyParams) == false
      && (($method == Curl::METHOD_POST) //create
        || ($method == Curl::METHOD_PUT) //update
        || ($method == Curl::METHOD_PATCH) //update
    )) {
      $postFields = array_merge($postFields, $bodyParams);
    }

    $hasFileData = false;
    $attrs = [];

    //form files
    if (empty($formFiles) == false) {
      //POST=create, PUT,PATCH=update
      if (in_array($method, [Curl::METHOD_POST, Curl::METHOD_PUT, Curl::METHOD_PATCH]) == false) {
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
            'content' => (is_array($attrContent) && (empty($attrContent) == false) ? json_encode($attrContent) : $attrContent),
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
    $headers = [];

    if ($isLocalApiServer) {
      $headers['Accept'] = 'application/json';
      // $headers[] = 'Content-Type: application/json';

      $currentLanguage = LanguageHelper::getCurrentLanguage();
      if (empty($currentLanguage) == false)
        $headers['Accept-Language'] = $currentLanguage;

      if (Yii::$app->request->headers->has('Authorization'))
        $headers['Authorization'] = Yii::$app->request->headers->get('Authorization');
      else if (method_exists(Yii::$app->user, 'getJwtByCookie')) {
        // if (Yii::$app->request->cookies->has('token'))
        // $headers[] = 'Authorization Bearer ' . Yii::$app->request->cookies->get('token');
        $jwt = Yii::$app->user->getJwtByCookie();
        if ($jwt !== null) {
          $headers['Authorization'] = 'Bearer ' . $jwt;
        }
      }
    }

    $clientConfig['headers'] = $headers;

    $client = new \GuzzleHttp\Client($clientConfig);

    $response = self::_guzzleRequest($client, $method, $url, $callOptions);

    $statusCode = $response->getStatusCode();
    // $responseData = self::_unserializeResponseBody($response);
    $responseData = (string)$response->getBody();

    // if ($response === false) {
    //   $statusCode = (-1) * curl_errno($CurlObject);
    //   $response = [
    //     'message' => curl_error($CurlObject),
    //   ];
    // } else {
      // $statusCode = curl_getinfo($CurlObject, CURLINFO_RESPONSE_CODE);

      if (is_string($responseData)) {
        //json null
        if (strcasecmp($responseData, 'null') == 0)
          $responseData = null;

        //convert $responseData string to json array
        if (empty($responseData) == false) {
          $org = $responseData;
          $responseData = Json::decode($responseData);
          if ($responseData === null) {
            $responseData = [
              'message' => $org,
            ];
          }
        }
      }
    // }

    return [$statusCode, $responseData];
  }

  protected static function _guzzleRequest($client, $method, $url, array $options)
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
      $response = $client->{$method}($url, $options);
      $profile and Yii::endProfile($rawQuery, $loggingCategory);

    } catch (ClientException $e) {
      $profile and Yii::endProfile($rawQuery, $loggingCategory);
      $response = $e->getResponse();

    } catch (ConnectException|RequestException $e) {
      $profile and Yii::endProfile($rawQuery, $loggingCategory);
      throw new ServerErrorHttpException(get_class($e).': url='. $options['base_uri'] .' '. $e->getMessage(), 500);

    }

    $header = $response->getHeader('WWW-Authenticate');
    if (empty($header) == false) {
      Yii::$app->user->logout();
      Yii::$app->response->redirect(Yii::$app->getHomeUrl());
      Yii::$app->response->send();
      die();
    }

    return $response;
  }

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
