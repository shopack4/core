<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\controllers;

use Yii;
use yii\web\Response;
use shopack\aaa\frontend\common\auth\BaseCrudController;
use shopack\base\common\helpers\HttpHelper;
use shopack\aaa\frontend\common\models\OnlinePaymentModel;
use shopack\aaa\frontend\common\models\OnlinePaymentSearchModel;

class OnlinePaymentController extends BaseCrudController
{
	public $modelClass = OnlinePaymentModel::class;
	public $searchModelClass = OnlinePaymentSearchModel::class;

  public function behaviors()
	{
		$behaviors = parent::behaviors();

		$behaviors[static::BEHAVIOR_AUTHENTICATOR]['except'] = [
			'webhook',
    ];

		return $behaviors;
	}

  // public function actionWebhook($gtwUUID, $command)
  // {
  //   Yii::$app->response->format = Response::FORMAT_JSON;

  //   $requert = Yii::$app->request;

  //   $result = HttpHelper::callApi('aaa/online-payment/webhook', $requert->method,
  //     array_merge($requert->queryParams, [
  //       'gtwUUID' => $gtwUUID,
  //       'command' => $command,
  //     ]),
  //     $requert->post()
  //   );

  //   return $result;
  // }

}
