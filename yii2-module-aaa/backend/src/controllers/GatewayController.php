<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\data\ActiveDataProvider;
use shopack\base\common\helpers\ExceptionHelper;
use shopack\base\backend\controller\BaseCrudController;
use shopack\base\backend\helpers\PrivHelper;
use shopack\aaa\backend\models\GatewayModel;

class GatewayController extends BaseCrudController
{
	public function behaviors()
	{
		$behaviors = parent::behaviors();

		$behaviors[static::BEHAVIOR_AUTHENTICATOR]['except'] = [
			'plugin-list',
			'plugin-params-schema',
			'plugin-restrictions-schema',
			'plugin-usages-schema',
			'plugin-webhooks-schema',
			'webhook',
		];

		return $behaviors;
	}

	public $modelClass = \shopack\aaa\backend\models\GatewayModel::class;

	public function permissions()
	{
		return [
			// 'index'  => ['aaa/gateway/crud' => '0100'],
			// 'view'   => ['aaa/gateway/crud' => '0100'],
			'create' => ['aaa/gateway/crud' => '1000'],
			'update' => ['aaa/gateway/crud' => '0010'],
			'delete' => ['aaa/gateway/crud' => '0001'],
		];
	}

	public function queryAugmentaters()
	{
		return [
			'index' => function($query) {
				$query
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
			'view' => function($query) {
				$query
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
		];
	}

	public function actionPluginList($type = null)
	{
		return $this->module->GatewayPluginList($type);
	}

	public function actionPluginParamsSchema($key)
	{
		return $this->module->GatewayPluginParamsSchema($key);
	}

	public function actionPluginRestrictionsSchema($key)
	{
		return $this->module->GatewayPluginRestrictionsSchema($key);
	}

	public function actionPluginUsagesSchema($key)
	{
		return $this->module->GatewayPluginUsagesSchema($key);
	}

	public function actionPluginWebhooksSchema($key)
	{
		return $this->module->GatewayPluginWebhooksSchema($key);
	}

	//accepts all http methods
	public function actionWebhook($gtwUUID, $command)
  {
		if (($gatewayModel = GatewayModel::findOne(['gtwUUID' => $gtwUUID])) === null) {
			Yii::error('The requested gateway does not exist.', __METHOD__);
			throw new NotFoundHttpException("The requested gateway does not exist.");
		}

		$gatewayClass = $gatewayModel->getGatewayClass();

		if (!($gatewayClass instanceof \shopack\base\common\classes\IWebhook)) {
			Yii::error('Webhook not supported by this gateway.', __METHOD__);
			throw new UnprocessableEntityHttpException('Webhook not supported by this gateway.');
		}

		//check caller
		if (!YII_ENV_DEV) {
			if (method_exists($gatewayClass, 'validateCaller')) {
				$ret = $gatewayClass->validateCaller();
				if ($ret !== true) {
					Yii::error($ret[1], __METHOD__);
					throw new UnprocessableEntityHttpException($ret[1]);
				}
			}
		}

		$ret = $gatewayClass->callWebhook($command);

		$params = [
			'get' => $_GET,
			'post' => $_POST,
		];

		$gatewayClass->log(
			/* gtwlogMethodName */ 'webhook',
			/* gtwlogRequest    */ $params,
			/* gtwlogResponse   */ $ret
		);

		return $ret;
  }

}
