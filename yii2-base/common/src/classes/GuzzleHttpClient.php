<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\classes;

use Yii;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use yii\web\UnauthorizedHttpException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\InvalidArgumentException;
use GuzzleHttp\Exception\RequestException;
use shopack\base\common\auth\AuthHelper;
use yii\web\ServerErrorHttpException;

class GuzzleHttpClient extends Client
{
	public function request(string $method, $uri = '', array $options = []) : ResponseInterface
	{
		$rawQuery = $uri;
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
			$response = parent::request($method, $uri, $options);
			$profile and Yii::endProfile($rawQuery, $loggingCategory);

		} catch (ClientException $e) {
			$profile and Yii::endProfile($rawQuery, $loggingCategory);
			$response = $e->getResponse();

		} catch (ConnectException|RequestException $e) {
			$profile and Yii::endProfile($rawQuery, $loggingCategory);
			throw new ServerErrorHttpException(get_class($e) . ': url=' . $uri . ' ' . $e->getMessage(), 500);

		}

		// $header = $response->getHeader('WWW-Authenticate');
		// if (empty($header) == false) {
		// 	throw new UnauthorizedHttpException('failed');

			// Yii::$app->user->logout();
			// Yii::$app->response->redirect(Yii::$app->getHomeUrl());
			// Yii::$app->response->send();
			// die();
		// }

		return $response;
	}

}
