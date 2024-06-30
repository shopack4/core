<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend;

use Yii;
use yii\base\BootstrapInterface;

class Module
	extends \shopack\base\common\base\BaseModule
	implements BootstrapInterface
{
	//used for trust message channel
	public $servicesPublicKeys = [];

	public function init()
	{
		if (empty($this->id))
			$this->id = 'aaa';

		parent::init();
	}

	public function bootstrap($app)
	{
		if ($app instanceof \yii\web\Application) {
			$rules = [
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/auth'],
					'pluralize' => false,
					'extraPatterns' => [
						'POST signup' => 'signup',
						'POST login' => 'login',
						'GET,POST logout' => 'logout',

						'POST login-by-mobile' => 'login-by-mobile',

						'POST challenge' => 'challenge',
						'POST challenge-timer-info' => 'challenge-timer-info',

						'POST request-approval-code' => 'request-approval-code',
						'POST accept-approval' => 'accept-approval',

						'POST request-forgot-password' => 'request-forgot-password',
						'POST forgot-password-timer-info' => 'forgot-password-timer-info',
						'POST password-reset-by-forgot-code' => 'password-reset-by-forgot-code',

						// 'POST password-set' => 'password-set',
						'POST password-change' => 'password-change',
					],
				],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/user'],
					'pluralize' => false,
					'extraPatterns' => [
						'GET,POST whoami' => 'who-am-i',
						'POST email-change' => 'email-change',
						'POST mobile-change' => 'mobile-change',
						'POST update-image' => 'update-image',
						'POST password-reset' => 'password-reset',
						'POST send-message' => 'send-message',
						'POST generate-2fa-activation-code' => 'generate-2fa-activation-code',
						'POST active-2fa' => 'active-2fa',
						'POST inactive-2fa' => 'inactive-2fa',
					],
				],
				// [
				// 	'class' => \shopack\base\common\rest\UrlRule::class,
				// 	// 'prefix' => 'v1',
				// 	'controller' => [$this->id . '/user-extra-info'],
				// 	'pluralize' => false,
				// ],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/message'],
					'pluralize' => false,
				],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/message-template'],
					'pluralize' => false,
				],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/approval-request'],
					'pluralize' => false,
				],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/request-forgot-password'],
					'pluralize' => false,
				],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/role'],
					'pluralize' => false,
				],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/access-group'],
					'pluralize' => false,
				],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/user-access-group'],
					'pluralize' => false,
				],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/gateway'],
					'pluralize' => false,
					'extraPatterns' => [
						'GET,POST plugin-list' => 'plugin-list',
						'GET,POST plugin-params-schema' => 'plugin-params-schema',
						'GET,POST plugin-restrictions-schema' => 'plugin-restrictions-schema',
						'GET,POST plugin-usages-schema' => 'plugin-usages-schema',
						'GET,POST plugin-webhooks-schema' => 'plugin-webhooks-schema',
						'webhook' => 'webhook',
					],
				],

				//fin
				// [
				// 	'class' => \shopack\base\common\rest\UrlRule::class,
				// 	// 'prefix' => 'v1',
				// 	'controller' => [$this->id . '/payment-gateway'],
				// 	'pluralize' => false,
				// 	'extraPatterns' => [
				// 		'POST callback' => 'callback',
				// 	],
				// ],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/online-payment'],
					'pluralize' => false,

					'tokens' => [
						'{paymentkey}' => '<paymentkey:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}>',
					],

					'extraPatterns' => [
						'get-allowed-types' => 'get-allowed-types',

						'pay/{paymentkey}' => 'pay',
						// 'pay' => 'pay',

						'callback/{paymentkey}' => 'callback',
						'callback' => 'callback',
					]
						//+ (YII_ENV_DEV ? ['devtestpaymentpage' => 'devtestpaymentpage'] : [])
					,
				],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/offline-payment'],
					'pluralize' => false,

					'extraPatterns' => [
						'POST accept' => 'accept',
						'POST reject' => 'reject',
					],
				],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/basket'],
					'pluralize' => false,

					'tokens' => [
						'{key}' => '<key:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}>',
					],

					'patterns' => [
						// 'GET,HEAD'					=> 'index',
						// 'GET,HEAD {uuid}'		=> 'view',
						// 'POST'							=> 'create',
						// 'PUT,PATCH {uuid}'	=> 'update',
						// 'DELETE item/{key}'		=> 'remove-item',
						// '{uuid}'						=> 'options',
						// ''									=> 'options',
					],

					'extraPatterns' => [
						'POST get-current'		=> 'get-current',
						'POST set-current'		=> 'set-current',
						// 'POST item'						=> 'add-item',
						// 'DELETE item/{key}'		=> 'remove-item',
						'POST checkout'				=> 'checkout',
					],
				],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/delivery-method'],
					'pluralize' => false,
				],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/voucher'],
					'pluralize' => false,

					'extraPatterns' => [
						'process-voucher/{id}' => 'process-voucher',

						'POST get-or-create-open-invoice' => 'get-or-create-open-invoice',
						'POST update-open-invoice' => 'update-open-invoice',
						'POST set-invoice-as-wait-for-payment' => 'set-invoice-as-wait-for-payment',

						'POST order-change-delivery-method/{id}' => 'order-change-delivery-method',
						'POST order-change-delivery-method' => 'order-change-delivery-method',

						'POST order-payment/{id}' => 'order-payment',
						'POST order-payment' => 'order-payment',

						'POST cancel/{id}' => 'cancel',
						'POST cancel' => 'cancel',
					],
				],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/wallet'],
					'pluralize' => false,

					'extraPatterns' => [
						'ensure-i-have-default-wallet' => 'ensure-i-have-default-wallet',
						'POST increase/{id}' => 'increase',
						'POST increase' => 'increase',
					],
				],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/wallet-transaction'],
					'pluralize' => false,
				],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/geo-country'],
					'pluralize' => false,
				],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/geo-state'],
					'pluralize' => false,
				],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/geo-city-or-village'],
					'pluralize' => false,
				],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/geo-town'],
					'pluralize' => false,
				],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/upload-file'],
					'pluralize' => false,
				],
			];

			$app->urlManager->addRules($rules, false);
		} elseif ($app instanceof \yii\console\Application) {
			//http://www.yiiframework.com/wiki/820/yii2-create-console-commands-inside-a-module-or-extension/
			$this->controllerNamespace = 'shopack\aaa\backend\commands';
			// $app->controllerMap['aaa'] = [
				// 'class' => 'shopack\aaa\backend\commands\SmsController',
				// 'generators' => array_merge($this->coreGenerators(), $this->generators),
				// 'module' => $this,
			// ];
		}
	}

	public function GatewayPluginList($type = null)
	{
		return $this->ExtensionList('gateways', $type);
	}

	public function GatewayPluginParamsSchema($key)
	{
		return $this->ExtensionParamsSchema('gateways', $key);
	}

	public function GatewayPluginRestrictionsSchema($key)
	{
		return $this->ExtensionRestrictionsSchema('gateways', $key);
	}

	public function GatewayPluginUsagesSchema($key)
	{
		return $this->ExtensionUsagesSchema('gateways', $key);
	}

	public function GatewayPluginWebhooksSchema($key)
	{
		return $this->ExtensionWebhooksSchema('gateways', $key);
	}

	public function GatewayClass($pluginName)
	{
		return $this->ExtensionClass('gateways', $pluginName);
	}

	public function GatewayList($type)
	{
		return $this->ExtensionList('gateways', $type);
	}

}
