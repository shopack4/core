<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\controllers;

use Yii;
use yii\web\Response;
use shopack\aaa\frontend\common\auth\BaseCrudController;
use shopack\base\common\helpers\HttpHelper;
use shopack\aaa\frontend\common\models\GatewayModel;
use shopack\aaa\frontend\common\models\GatewaySearchModel;

class GatewayController extends BaseCrudController
{
	public $modelClass = GatewayModel::class;
	public $searchModelClass = GatewaySearchModel::class;

  public function behaviors()
	{
		$behaviors = parent::behaviors();

		$behaviors[static::BEHAVIOR_AUTHENTICATOR]['except'] = [
			'webhook',
    ];

		return $behaviors;
	}

  public function actionPluginList($p=null, $sel=null)
  {
		$bodyParams = Yii::$app->request->getBodyParams();

    $type = (isset($bodyParams['depdrop_parents']) ? end($bodyParams['depdrop_parents']) : $p);
		// return $this->renderJson(['output' => Yii::$app->shopack->gateway->getList($type), 'selected' => $sel]);

    Yii::$app->response->format = Response::FORMAT_JSON;

    if (empty($type)) {
      return [
        'output' => [],
        'selected' => $sel,
      ];
    }

    $apiResponse = HttpHelper::callApi('aaa/gateway/plugin-list', HttpHelper::METHOD_GET, [
      'type' => $type,
    ]);

		$pluginList = [];
    if ($apiResponse['status'] == 200) {
			foreach ($apiResponse['data'] as $k => $v) {
        $pluginList[] = [
          'id' => $k,
          'name' => $v['title']
        ];
			}
    }

    return [
      'output' => $pluginList,
      'selected' => $sel,
    ];
  }

  public function getPluginSchema($type, $key)
  {
    Yii::$app->response->format = Response::FORMAT_JSON;

    $apiResponse = HttpHelper::callApi("aaa/gateway/plugin-{$type}-schema", HttpHelper::METHOD_GET, [
      'key' => $key,
    ]);

    if ($apiResponse['status'] == 200) {
      $list = $apiResponse['data'];
      array_walk($list, function (&$item) {
        if (isset($item['label']))
          $item['label'] = Yii::t('aaa', $item['label']);

        if (isset($item['data'])) {
          if (is_string($item['data']))
            $item['data'] = Yii::t('aaa', $item['data']);
          else if (is_array($item['data'])) {
            foreach ($item['data'] as $kData => &$vData) {
              $vData = Yii::t('aaa', $vData);
            }
          }
        }
      });

      return [
        'count' => count($apiResponse['data']),
        'list' => $list,
      ];
    }

    return [];
  }

  public function actionPluginParamsSchema($key)
  {
    return $this->getPluginSchema('params', $key);
  }

  public function actionPluginRestrictionsSchema($key)
  {
    return $this->getPluginSchema('restrictions', $key);
  }

  public function actionPluginUsagesSchema($key)
  {
    return $this->getPluginSchema('usages', $key);
  }

  public function actionPluginWebhooksSchema($key)
  {
    Yii::$app->response->format = Response::FORMAT_JSON;

    $apiResponse = HttpHelper::callApi('aaa/gateway/plugin-webhooks-schema', HttpHelper::METHOD_GET, [
      'key' => $key,
    ]);

    if ($apiResponse['status'] == 200) {
      $list = $apiResponse['data'];
      array_walk($list, function (&$item) {
        if (isset($item['label']))
          $item['label'] = Yii::t('aaa', $item['label']);
      });

      return [
        'count' => count($apiResponse['data']),
        'list' => $list,
      ];
    }

    return [];
  }

  public function actionWebhook($gtwUUID, $command)
  {
    Yii::$app->response->format = Response::FORMAT_JSON;

    $requert = Yii::$app->request;

    $apiResponse = HttpHelper::callApi('aaa/gateway/webhook', $requert->method,
      array_merge($requert->queryParams, [
        'gtwUUID' => $gtwUUID,
        'command' => $command,
      ]),
      $requert->post()
    );

    return $apiResponse;
  }

}
