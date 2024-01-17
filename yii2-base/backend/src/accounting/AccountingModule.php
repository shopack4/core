<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\accounting;

use yii\base\BootstrapInterface;

class AccountingModule
	extends \shopack\base\common\base\BaseModule
	implements BootstrapInterface
{
	public function init()
	{
		if (empty($this->id))
			$this->id = 'accounting';

		parent::init();
	}

	public function bootstrap($app)
	{
		$parentID = $this->module->id;
		$thisID = $parentID . '/' . $this->id;

		if ($app instanceof \yii\web\Application) {
			$this->controllerNamespace = str_replace('\controllers', '\accounting\controllers', $this->module->controllerNamespace);

			$rules = [];

			//-- accounting ---------------------------------
			$rules = array_merge($rules, [
				[
					'class' => \yii\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$thisID . '/unit'],
					'pluralize' => false,
				],
				[
					'class' => \yii\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$thisID . '/discount'],
					'pluralize' => false,
				],
				[
					'class' => \yii\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$thisID . '/discount-serial'],
					'pluralize' => false,
				],
				[
					'class' => \yii\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$thisID . '/discount-usage'],
					'pluralize' => false,
				],
				[
					'class' => \yii\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$thisID . '/product'],
					'pluralize' => false,
				],
				[
					'class' => \yii\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$thisID . '/saleable'],
					'pluralize' => false,
				],
				[
					'class' => \yii\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$thisID . '/user-asset'],
					'pluralize' => false,
				],

				[
					'class' => \yii\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$thisID => $thisID . '/default'],
					'pluralize' => false,

					'patterns' => [
						'GET remove-basket-item' => 'remove-basket-item',
					],
				],

			]);

			$app->urlManager->addRules($rules, false);

		} else if ($app instanceof \yii\console\Application) {
			$this->controllerNamespace = str_replace('\commands', '\accounting\commands', $this->module->controllerNamespace);
		}
	}

}
