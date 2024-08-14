<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\common\rest;

use Yii;
use yii\base\Component;
use yii\base\InvalidCallException;
use yii\base\InvalidParamException;
use yii\base\InvalidArgumentException;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use shopack\base\common\classes\GuzzleHttpClient;
use shopack\base\common\helpers\ArrayHelper;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\common\helpers\Json;
use shopack\base\common\helpers\LanguageHelper;
use shopack\base\common\helpers\Url;
use shopack\base\frontend\common\rest\RestClientQueryInterface;
use shopack\base\frontend\common\rest\RestClientActiveRecord;
use yii\web\UnauthorizedHttpException;

/**
 * Class RestClientQuery
 * HTTP transport by GuzzleHTTP
 *
 * @package shopack\base\frontend\common\rest
 */
class RestClientQuery
	extends \yii\db\ActiveQuery //Component
	implements RestClientQueryInterface
{
	// use ActiveRelationTrait;

	/**
	 * Data type for requests and responses
	 * Required.
	 * @var string
	 */
	public $dataType = self::JSON_TYPE;

	/**
	 * Headers for requests
	 * @var array
	 */
	public $requestHeaders = [];

	/**
	 * Wildcard for response headers object
	 * @see RestClientQuery::count()
	 * @var array
	 */
	public $responseHeaders = [
		'totalCount'    => 'X-Pagination-Total-Count',
		'pageCount'     => 'X-Pagination-Page-Count',
		'currPage'      => 'X-Pagination-Current-Page',
		'perPageCount'  => 'X-Pagination-Per-Page',
		'links'         => 'Link',
	];

	/**
	 * Response unserializer class
	 * @var array|object
	 */
	public $unserializers = [
		self::JSON_TYPE => [
			'class' => 'shopack\base\frontend\common\rest\JsonUnserializer'
		]
	];

	/**
	 * HTTP client that performs HTTP requests
	 * @var object
	 */
	public $httpClient;

	/**
	 * Configuration to be supplied to the HTTP client
	 * @var array
	 */
	public $httpClientExtraConfig = [];

	/**
	 * Model class
	 * @var RestClientActiveRecord
	 */
	public $modelClass;

	/**
	 * Get param name for select fields
	 * @var string
	 */
	public $selectFieldsKey = 'fields';

	public $filterKey = 'filter';

	public $orderByKey = 'order-by';

	/**
	 * Request LIMIT param name
	 * @see shopack\base\frontend\common\rest\RestClientActiveRecord::$limitKey
	 * @var string
	 */
	public $limitKey;

	/**
	 * Request OFFSET param name
	 * @see shopack\base\frontend\common\rest\RestClientActiveRecord::$offsetKey
	 * @var string
	 */
	public $offsetKey;

	/**
	 * Model class envelope
	 * @see shopack\base\frontend\common\rest\RestClientActiveRecord::$collectionEnvelope
	 * @var string
	 */
	protected $_collectionEnvelope;

	/**
	 * Model class pagination envelope
	 * @see shopack\base\frontend\common\rest\RestClientActiveRecord::$paginationEnvelope
	 * @var string
	 */
	protected $_paginationEnvelope;

	/**
	 * Model class pagination envelope keys mapping
	 * @see shopack\base\frontend\common\rest\RestClientActiveRecord::$paginationEnvelopeKeys
	 * @var array
	 */
	private $_paginationEnvelopeKeys;

	/**
	 * Pagination data from pagination envelope in GET request
	 * @var array
	 */
	private $_pagination;

	/**
	 * Array of fields to select from REST
	 * @var array
	 */
	private $_select = [];

	/**
	 * Conditions
	 * @var array
	 */
	// private $_where;

	/**
	 * RestClientQuery limit
	 * @var int
	 */
	private $_limit;

	/**
	 * RestClientQuery offset
	 * @var int
	 */
	private $_offset;

	/**
	 * Flag Is this query is sub-query
	 * to prevent recursive requests
	 * for get enveloped pagination
	 * @see RestClientQuery::count()
	 * @var bool
	 */
	private $_subQuery = false;

	//used for creating url
	private $_urlParameters = null;

	public $_endpoint;

	/**
	 * Constructor. Really.
	 * @param RestClientActiveRecord $modelClass
	 * @param array $config
	 */
	public function __construct($modelClass, $config = [])
	{
		parent::__construct($config);

		$modelClass::staticInit();
		$this->modelClass = $modelClass;
		$this->_collectionEnvelope = $modelClass::$collectionEnvelope;
		$this->_paginationEnvelope = $modelClass::$paginationEnvelope;
		$this->_paginationEnvelopeKeys = $modelClass::$paginationEnvelopeKeys;
		$this->offsetKey = $modelClass::$offsetKey;
		$this->limitKey = $modelClass::$limitKey;

		$this->requestHeaders = ['Accept' => RestClientQuery::JSON_TYPE];

		$currentLanguage = LanguageHelper::getCurrentLanguage();
		if (empty($currentLanguage) == false)
			$this->requestHeaders = ['Accept-Language' => $currentLanguage];

		//moved to client
		// if (Yii::$app->request->headers->has('Authorization'))
		//   $this->requestHeaders['Authorization'] = Yii::$app->request->headers->get('Authorization');
		// else {
		//   if (Yii::$app->user->isGuest == false) {
		//     $jwt = Yii::$app->user->identity->accessToken; //getJwtByCookie();
		//     if ($jwt !== null)
		//       $this->requestHeaders['Authorization'] = 'Bearer ' . $jwt;
		//   }
		// }

		$this->requestHeaders['Origin'] = rtrim(Url::to(['/'], true), '/\\');

		$httpClientConfig = array_merge([

				// \GuzzleHttp\RequestOptions::CONNECT_TIMEOUT => 10, //seconds
				// \GuzzleHttp\RequestOptions::TIMEOUT => 10, //seconds

				/* @link http://docs.guzzlephp.org/en/latest/quickstart.html */
				'base_uri' => $this->_getUrl('api'),
				/* @link http://docs.guzzlephp.org/en/latest/request-options.html#headers */
				'headers' => $this->_getRequestHeaders(),
			],
			$this->httpClientExtraConfig
		);

		if (defined('YII_DEV_LOCAL_PROXY')) {
			$httpClientConfig['proxy'] = constant('YII_DEV_LOCAL_PROXY');
		}

		$this->httpClient = new GuzzleHttpClient($httpClientConfig);

		if (Yii::$app->isJustForMe)
			$this->addUrlParameter('justForMe', 1);
	}

	public function andWhere($condition, $params = [])
	{
		if (empty($this->where))
			$this->where = null;

		return parent::andWhere($condition, $params);
	}

	public function orWhere($condition, $params = [])
	{
		if (empty($this->where))
			$this->where = null;

		return parent::orWhere($condition, $params);
	}

	protected function fillWhereByRelation()
	{
		if (empty($this->primaryModel))
			return;

		$thisKey = array_keys($this->link)[0];

		// $this->where = is_array($this->where) ? $this->where : [];
		///@TODO: make correct finding in complex where array: ['or', ['and', ['like', ...
		if (isset($this->where[$thisKey]))
			return;

		$primaryKey = array_values($this->link)[0];

		$this->andWhere([
			$thisKey => $this->primaryModel->$primaryKey
		]);
	}

	protected function getIdFromWhere()
	{
		$id = null;
		$primaryKey = $this->modelClass::primaryKey();

		if (isset($primaryKey[0])) {
			$this->where = is_array($this->where) ? $this->where : [];
			///@TODO: make correct finding in complex where array: ['or', ['and', ['like', ...

			foreach ($this->where as $k => $v) {
				if ($k == $primaryKey[0]) {
					$id = $v;
					unset($this->where[$k]);
					break;
				}
			}
		}

		return $id;
	}

	/**
	 * GET resource collection request
	 * @inheritdoc
	 */
	public function all($db = null)
	{
		$modelClass = $this->modelClass;
		$resourceParams = $modelClass::getResourceParams();
		if (empty($resourceParams) == false) {
			$this->andWhere($resourceParams);
		}

		$this->fillWhereByRelation();

		$apiResponse = $this->_request(
			/* method     */ 'get',
			/* url        */ $this->_getUrl('collection'),
			/* urlParams  */ $this->_buildQueryParams()
			/* bodyParams */
			/* formFiles  */
			/* options    */
		);

		return $this->_populate($apiResponse);
	}

	/**
	 * Get collection count
	 * If $this->_pagination isset (from get request before call this method) return count from it
	 * else execute HEAD request to collection and get count from X-Pagination-Total-Count(default) response header
	 * If header is empty and isset pagination envelope - do get collection request with limit 1 to get pagination data
	 * @see RestClientQuery::$_subQuery
	 * @inheritdoc
	 */
	public function count($q = '*', $db = null)
	{
		$query = clone $this;

		$modelClass = $this->modelClass;
		$resourceParams = $modelClass::getResourceParams();
		if (empty($resourceParams) == false) {
			$query->andWhere($resourceParams);
		}

		$query->fillWhereByRelation();

		if ($query->_pagination)
			return isset($query->_pagination['totalCount']) ? (int) $query->_pagination['totalCount'] : 0;

		if ($query->_subQuery)
			return 0;

		// try to get count by HEAD request
		$apiResponse = $query->_request(
			/* method     */ 'head',
			/* url        */ $this->_getUrl('collection'),
			/* urlParams  */ $this->_buildQueryParams()
			/* bodyParams */
			/* formFiles  */
			/* options    */
		);

		$count = $apiResponse['headers'][$query->responseHeaders['totalCount']][0] ?? null;

		// REST server not allow HEAD query and X-Total header is empty
		if ($count === '' && $query->_paginationEnvelope) {
			$query->_setSubQueryFlag()->offset(0)->limit(1)->all();
			return $query->count();
		}

		return (int) $count;
	}

	/**
	 * GET resource element request
	 * @inheritdoc
	 */
	public function one($id = null)
	{
		$modelClass = $this->modelClass;
		$resourceParams = $modelClass::getResourceParams();
		if (empty($resourceParams) == false) {
			$this->andWhere($resourceParams);
		}

		$this->fillWhereByRelation();

		if (empty($id))
			$id = $this->getIdFromWhere();

		if (empty($id))
			return null;
			// return new $this->modelClass;
			// throw new InvalidArgumentException('The id not provided');

		$apiResponse = $this->_request(
			/* method     */ 'get',
			/* url        */ $this->_getUrl('element', $id),
			/* urlParams  */ $this->_buildQueryParams()
			/* bodyParams */
			/* formFiles  */
			/* options    */
		);

		return $this->_populate($apiResponse, false);
	}

	public function restGet()
	{
		$this->fillWhereByRelation();

		$id = $this->getIdFromWhere();

		$apiResponse = $this->_request(
			/* method     */ 'get',
			/* url        */ $this->_getUrl('element', $id),
			/* urlParams  */ $this->_buildQueryParams()
			/* bodyParams */
			/* formFiles  */
			/* options    */
		);

		return $this->_populate($apiResponse, false);
	}

	/**
	 * return : null|array of multipart attriutes
	 */
	protected function convertForFileDataIfHas($attributes)
	{
		$hasFileData = false;
		foreach ($attributes as $k => $v) {
			if ($v instanceof FileData) {
				$hasFileData = true;
				break;
			}
		}

		if ($hasFileData == false)
			return false;

		$multipartAttributes = [];

		foreach ($attributes as $k => $v) {
			if ($v instanceof FileData) {
				if (empty($v->tmp_name) == false) {
					$multipartAttributes[] = [
						'name' => $k,
						'contents' => fopen($v->tmp_name, 'r'),
						'filename' => $v->name,
					];
				}
			} else {
				$multipartAttributes[] = [
					'name' => $k,
					'contents' => (is_array($v) && (empty($v) == false) ? json_encode($v) : $v),
				];
			}
		}

		return $multipartAttributes;
	}

	/**
	 * POST request
	 * @inheritdoc
	 */
	public function restPost(RestClientActiveRecord $model)
	{
		$options = [];

		$attributes = array_filter($model->getAttributes());
		$multipartAttributes = $this->convertForFileDataIfHas($attributes);
		if ($multipartAttributes) {
			$options['multipart'] = $multipartAttributes;
		} else {
			$options['json'] = $attributes;
		}

		$apiResponse = $this->_request(
			/* method     */ 'post',
			/* url        */ $this->_getUrl('element', $model->getParentKey()),
			/* urlParams  */ null,
			/* bodyParams */ $options['json'] ?? null,
			/* formFiles  */ $options['multipart'] ?? null
			/* options    */ //$options
		);

		return $this->_populate($apiResponse, false, $model);
	}

	public function restCreate(RestClientActiveRecord $model)
	{
		return $this->restPost($model);
	}

	/**
	 * PUT request
	 * @inheritdoc
	 */
	public function restPut(RestClientActiveRecord $model)
	{
		$options = [];

		$attributes = $model->getDirtyAttributes();
		$multipartAttributes = $this->convertForFileDataIfHas($attributes);
		if ($multipartAttributes) {
			$options['multipart'] = $multipartAttributes;
		} else {
			$options['json'] = $attributes;
		}

		$apiResponse = $this->_request(
			/* method     */ 'put',
			/* url        */ $this->_getUrl('element', $model->getParentKey()),
			/* urlParams  */ $this->_buildQueryParams(),
			/* bodyParams */ $options['json'] ?? null,
			/* formFiles  */ $options['multipart'] ?? null
			/* options    */ //$options
		);

		return $this->_populate($apiResponse, false);
	}

	public function restUpdate(RestClientActiveRecord $model)
	{
		return $this->restPut($model);
	}

	public function restUpdateAll($attributes, $condition = '', $params = [])
	{
		if (isset($condition)) {
			foreach ((array)$condition as $name => $value) {
				$this->andWhere([$name => $value], $params);
			}
		}

		$id = $this->getIdFromWhere();

		$options = [];

		$multipartAttributes = $this->convertForFileDataIfHas($attributes);
		if ($multipartAttributes) {
			$options['multipart'] = $multipartAttributes;
		} else {
			$options['json'] = $attributes;
		}

		$apiResponse = $this->_request(
			/* method     */ 'put',
			/* url        */ $this->_getUrl('element', $id),
			/* urlParams  */ $this->_buildQueryParams(),
			/* bodyParams */ $options['json'] ?? null,
			/* formFiles  */ $options['multipart'] ?? null
			/* options    */ //$options
		);

		$this->_populate($apiResponse, false);
	}

	public function restDelete(RestClientActiveRecord $model)
	{
		$apiResponse = $this->_request(
			/* method     */ 'delete',
			/* url        */ $this->_getUrl('element', $model->getPrimaryKey()),
			/* urlParams  */ $model->getAttributes()
			/* bodyParams */
			/* formFiles  */
			/* options    */
		);

		return $apiResponse['status'] == 204;
	}

	public function restDeleteAll($condition, $params = [])
	{
		if (isset($condition)) {
			foreach ((array)$condition as $name => $value) {
				$this->andWhere([$name => $value], $params);
			}
		}

		$id = $this->getIdFromWhere();

		$apiResponse = $this->_request(
			/* method     */ 'delete',
			/* url        */ $this->_getUrl('element', $id),
			/* urlParams  */ $this->_buildQueryParams()
			/* bodyParams */
			/* formFiles  */
			/* options    */
		);

		$this->_populate($apiResponse, false);

		return ($apiResponse['status'] == 200);
	}

	public function restUndelete(RestClientActiveRecord $model)
	{
		$apiResponse = $this->_request(
			/* method     */ 'undelete',
			/* url        */ $this->_getUrl('element', $model->getPrimaryKey()),
			/* urlParams  */ $model->getAttributes()
			/* bodyParams */
			/* formFiles  */
			/* options    */
		);

		$this->_populate($apiResponse, false);

		return ($apiResponse['status'] == 200);
	}

	/**
	 * @inheritdoc
	 */
	public function select($columns, $option = null)
	{
		$this->_select = $columns;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	// public function where($condition, $params = [])
	// {
	//   if (empty($this->_where))
	//     $this->_where = [$condition];
	//   else
	//     $this->_where[] = $condition;

	//   return $this;
	// }

	// public function andWhere($condition, $params = [])
	// {
	//   return $this->where($condition, $params);
	// }

	/**
	 * @inheritdoc
	 */
	public function noLimit()
	{
		$this->_limit = 0;
		return $this;
	}

	public function limit($limit)
	{
		if (empty($limit))
			$this->_limit = null;
		else
			$this->_limit = (int)$limit;

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function offset($offset)
	{
		if (empty($offset))
			$this->_offset = null;
		else
			$this->_offset = (int)$offset;

		return $this;
	}

	public function endpoint($endpoint)
	{
		$this->_endpoint = $endpoint;
		return $this;
	}

	public $headerNames = [
		'date'							=> 'Date',
		'server'						=> 'Server',
		'x-powered-by'			=> 'X-Powered-By',
		'vary'							=> 'Vary',
		'www-authenticate'	=> 'Www-Authenticate',
		'x-debug-tag'				=> 'X-Debug-Tag',
		'x-debug-duration'	=> 'X-Debug-Duration',
		'x-debug-link'			=> 'X-Debug-Link',
		'content-length'		=> 'Content-Length',
		'keep-alive'				=> 'Keep-Alive',
		'connection'				=> 'Connection',
		'content-type'			=> 'Content-Type',
	];

	public function getHeaderName($header)
	{
		if (!isset($this->headerNames[strtolower($header)]))
			return $header;

		return $this->headerNames[strtolower($header)];
	}

	public function getHeaderLine($apiResponse, $header): string
	{
		return implode(', ', $apiResponse['headers'][$this->getHeaderName($header)] ?? []);
	}

	/**
	 * HTTP request
	 * @param string $method
	 * @param string $url
	 * @param array $options
	 * @return ResponseInterface
	 * @throws ServerErrorHttpException
	 */
	private function _request(
		$method,
		$url,
		$urlParams = null,
		$bodyParams = null,
		$formFiles = null,
		array $options = null
	) {
		$apiResponse = HttpHelper::callApi(
			/* url        */ $url,
			/* method     */ $method,
			/* urlParams  */ $urlParams,
			/* bodyParams */ $bodyParams,
			/* formFiles  */ $formFiles,
			/* options    */ null
		);

		return $apiResponse;

		// if (empty($urlParams) == false)
		//   $options['query'] = $urlParams;

		// if (empty($bodyParams) == false)
		//   $options['json'] = $bodyParams;

		// if (empty($formFiles) == false)
		//   $options['formFiles'] = $formFiles;

//    return $this->httpClient->{$method}($url, $options);
		//$response->getHeaderLine('Content-type');

		/*
		// moved to GuzzleHttpClient:
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
			$response = $this->httpClient->{$method}($url, $options);
			$profile and Yii::endProfile($rawQuery, $loggingCategory);

		} catch (ClientException $e) {
			$profile and Yii::endProfile($rawQuery, $loggingCategory);
			$response = $e->getResponse();

		} catch (ConnectException|RequestException $e) {
			$profile and Yii::endProfile($rawQuery, $loggingCategory);
			$this->_throwServerError($e);

		}

		return $response;
		*/
	}

	/**
	 * Throw 500 error exception
	 * @param \Exception $e
	 * @throws ServerErrorHttpException
	 */
	/*
	private function _throwServerError(\Exception $e)
	{
		$uri = (string) $this->httpClient->getConfig('base_uri');

		throw new ServerErrorHttpException(get_class($e).': url='.$uri .' '. $e->getMessage(), 500);
	}
	*/

	/**
	 * Unserialize and create models
	 * @param ResponseInterface $response
	 * @param bool $asCollection
	 * @return $this|RestClientActiveRecord|array|void
	 * @throws HttpException
	 */
	protected function _populate(
		$apiResponse, //ResponseInterface $response,
		$asCollection = true,
		RestClientActiveRecord $model = null
	) {
		$statusCode = $apiResponse['status']; // $response->getStatusCode();
		$data = $this->_unserializeResponseBody($apiResponse);

		// if (is_object($data)) {
		//   $dataAsArray = [(array)$data];
		// } else
		//   $dataAsArray = (array)$data;

		$messageCategory = 'aaa';

		$fnFormatMessage = function($message) use($messageCategory) {
			$saveMsg = $message;
			$message = json_decode($message, true);
			if (empty($message))
				return $saveMsg;

			$msg = array_shift($message);
			$message = Yii::t($messageCategory, $msg, $message);

			return $message;
		};

		// errors
		if ($statusCode >= 400) {
			if (($model !== null) && ($statusCode === 422)) { // && count($data) === 1 && isset($data[0])) {
				$model->addError($data->field ?? null, $fnFormatMessage($data['message']));
				return $model;
			}

			throw new HttpException(
				$statusCode,
				is_string($data) ? $data : $fnFormatMessage($data['message']),
				$statusCode
			);
		}

		if ($asCollection)
			return $this->_populateAsCollection($data);

		$models = $this->_createModels($data);
		return ($asCollection ? $models : $models[0]);

		/*
		$models = [];
		// array of objects or arrays - probably resource collection
		if (is_array($data)) {
			return $this->_createModels($data);
		}
		// collection with data envelope or single element
		if (is_object($data)) {

			if ($asCollection)
				return $this->_populateAsCollection($data);

			$models = $this->_createModels([$data])[0];
		}

		return $models;*/
	}

	/**
	 * @param RestClientActiveRecord $data
	 *
	 * @return RestClientActiveRecord[]
	 */
	protected function _populateAsCollection($data)
	{
		$elements = [];

		if (is_array($data)) {
			if ($this->_collectionEnvelope)
				$elements = $data[$this->_collectionEnvelope] ?? [];

			if ($this->_paginationEnvelope && isset($data[$this->_paginationEnvelope]))
				$this->_setPagination($this->_getProps($data[$this->_paginationEnvelope]));

		} else if (is_object($data)) {
			if ($this->_collectionEnvelope)
				$elements = $data->{$this->_collectionEnvelope} ?? [];

			if ($this->_paginationEnvelope && isset($data->{$this->_paginationEnvelope}))
				$this->_setPagination($this->_getProps($data->{$this->_paginationEnvelope}));

		}

		return $this->_createModels($elements);
	}

	/**
	 * Create models from array of elements
	 * @param array $elements
	 * @return array
	 */
	protected function _createModels(array $elements)
	{
		if (ArrayHelper::isIndexed($elements) == false)
			$elements = [$elements];

		$modelClass = $this->modelClass;
		$models = [];
		foreach ($elements as $element) {
			$attributes = $this->_getProps($element);
			$model      = $modelClass::instantiate($attributes); //->setAttributes($attributes);

			$modelClass::populateRecord($model, $attributes);

			//load relations
			$this->_loadRelations($model, $attributes);

			//--------------
			if (!$this->asArray)
				$model->afterFind();

			//--------------
			$models[] = $model;

			// $models[]   = $model->setId(
			//   $model->getAttribute($modelClass::primaryKey())
			// );
		}

		return $models;
	}

	function _loadRelations($model, $attributes)
	{
		$columns = array_flip($model->attributes());
		foreach ($attributes as $name => $value) {
			if (empty($value) || isset($columns[$name]))
				continue;

			$relatedMethodName = 'get' . ucfirst($name);
			if (method_exists($model, $relatedMethodName)) {
				$relation = call_user_func(array($model, $relatedMethodName));

				$relationModelClass = $relation->modelClass;

				$attrs = $this->_getProps($value);

				//TODO: check if $value is array for hasMany

				$relatedModel = $relationModelClass::instantiate($attrs);
				$relationModelClass::populateRecord($relatedModel, $attrs);
				$model->populateRelation($name, $relatedModel);

				//nested relations
				$this->_loadRelations($relatedModel, $attrs);
			}
		}
	}

	/**
	 * Try to unserialize response body data
	 * @param ResponseInterface $response
	 * @return object[]|object|string
	 * @throws \yii\base\InvalidConfigException
	 */
	protected function _unserializeResponseBody($apiResponse)
	{
		return $apiResponse['body'];




		$contentType = $this->getHeaderLine($apiResponse, 'Content-type');
		$body = $apiResponse['body']; //(string) $response->getBody();

		try {
			if (false !== stripos($contentType, $this->dataType)
				&& isset($this->unserializers[$this->dataType])
			) {
				/** @var UnserializerInterface $unserializer */
				$unserializer = \Yii::createObject($this->unserializers[$this->dataType]);
				if ($unserializer instanceof UnserializerInterface) {
					return $unserializer->unserialize($body, false);
				}
			}

			return $body;

		} catch (InvalidArgumentException $e) {
			return $body;

		} catch (InvalidParamException $e) {
			return $body;
		}
	}

	/**
	 * Pagination data setter
	 * If pagination data isset in GET request result
	 * @param array $pagination
	 * @return $this
	 */
	private function _setPagination(array $pagination)
	{
		foreach ($this->_paginationEnvelopeKeys as $key => $name) {
			$this->_pagination[$key] = isset($pagination[$name])
				? $pagination[$name]
				: null;
		}

		return $this;
	}

	/**
	 * Get array of properties from object
	 * @param $object
	 * @return array
	 */
	private function _getProps($object)
	{
		return is_object($object) ? get_object_vars($object) : $object;
	}

	public function addUrlParameter($name, $value)
	{
		if (isset($this->_urlParameters[$name]))
			$this->_urlParameters[$name] = array_merge((array)$this->_urlParameters[$name], [$value]);
		else
			$this->_urlParameters[$name] = $value;

		return $this;
	}

	/**
	 * Build query params
	 * @return array
	 */
	private function _buildQueryParams()
	{
		$query = [];

		if (count($this->_select)) {
			$query[$this->selectFieldsKey] = implode(',', $this->_select);
		}

		$this->_buildQueryFilterPart($query);

		if (empty($this->orderBy) == false) {
			$orders = [];
			foreach ($this->orderBy as $name => $direction) {
				$orders[] = ($direction === SORT_DESC ? '-' : '') . $name;
				// $orders[] = $name . ($direction === SORT_DESC ? ' DESC' : '');
			}

			$query[$this->orderByKey] = implode(',', $orders);
		}

		if ($this->_limit !== null) {
			$query[$this->limitKey] = $this->_limit;
		}

		if ($this->_offset !== null) {
			if ($this->_limit === null)
				$query[$this->offsetKey] = $this->_offset;
			else
				$query[$this->offsetKey] = ($this->_offset / $this->_limit) + 1;
		}

		if (isset($this->_urlParameters)) {
			foreach ($this->_urlParameters as $name => $value) {
				if (is_array($value))
					$query[$name] = /*urlencode*/(implode(',', $value));
				else
					$query[$name] = /*urlencode*/($value);
			}
		}

		return $query;
	}

	private function _buildQueryFilterPart(&$query)
	{
		$this->where = is_array($this->where) ? $this->where : [];
		if (empty($this->where))
			return;

		$query[$this->filterKey] = Json::encode($this->where);
	}

	/**
	 * Get headers for request
	 * @return array
	 */
	private function _getRequestHeaders()
	{
		return $this->requestHeaders ?: ['Accept' => $this->dataType];
	}

	/**
	 * Get url to collection or element of resource
	 * with check base url trailing slash
	 * @param string $type api|collection|element
	 * @param string $id
	 * @return string
	 */
	private function _getUrl($type = 'base', $id = null)
	{
		$modelClass = $this->modelClass;

		if ($type == 'api')
			return $this->_trailingSlash($modelClass::getApiUrl());

		$collection = $modelClass::getResourceName();
		if (empty($this->_endpoint) == false)
			$collection .= '/' . $this->_endpoint;

		$url = $this->_trailingSlash($collection, false);

		if (($type == 'element') && (empty($id) == false))
			$url .= '/' . $this->_trailingSlash($id, false);

		return $url;

		// switch ($type) {
		//   case 'api':
		//     return $this->_trailingSlash($modelClass::getApiUrl());
		//     break;

		//   case 'collection':
		//     return $this->_trailingSlash($collection, false);
		//     break;

		//   case 'element':
		//     if (is_null($id))
		//       return $this->_trailingSlash($collection, false);
		//     return $this->_trailingSlash($collection) . $this->_trailingSlash($id, false);
		//     break;
		// }

		// return '';
	}

	/**
	 * Check trailing slash
	 * if $add - add trailing slash
	 * if not $add - remove trailing slash
	 * @param $string
	 * @param bool $add
	 * @return string
	 */
	private function _trailingSlash($string, $add = true)
	{
		return substr($string, -1) === '/'
			? ($add ? $string : substr($string, 0, strlen($string) - 1))
			: ($add ? $string . '/' : $string);
	}

	/**
	 * Mark query as subquery to prevent queries recursion
	 * @see count()
	 * @return RestClientQuery
	 */
	private function _setSubQueryFlag()
	{
		$this->_subQuery = true;
		return $this;
	}

}
