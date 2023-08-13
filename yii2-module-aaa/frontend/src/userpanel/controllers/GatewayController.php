<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\userpanel\controllers;

use Yii;
use yii\web\Response;
use shopack\base\common\helpers\HttpHelper;
use shopack\aaa\frontend\common\auth\BaseController;

class GatewayController extends BaseController
{
  public function behaviors()
	{
		$behaviors = parent::behaviors();

		$behaviors[static::BEHAVIOR_AUTHENTICATOR]['except'] = [
			'webhook',
    ];

		return $behaviors;
	}

  public function actionWebhook($gtwUUID, $command)
  {
    Yii::$app->response->format = Response::FORMAT_JSON;

    $requert = Yii::$app->request;

    $result = HttpHelper::callApi('aaa/gateway/webhook', $requert->method,
      array_merge($requert->queryParams, [
        'gtwUUID' => $gtwUUID,
        'command' => $command,
      ]),
      $requert->post()
    );

    return $result;
  }

}
