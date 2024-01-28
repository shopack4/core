<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\accounting;

use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;

abstract class AccountingModule
	extends \shopack\base\common\base\BaseModule
	implements BootstrapInterface
{
	public $unitModelClass;
	public $productModelClass;
	public $saleableModelClass;
	public $discountModelClass;
	public $discountUsageModelClass;
	public $userAssetModelClass;
	public $basketModelClass;

	public function init()
	{
		if (empty($this->id))
			$this->id = 'accounting';

		parent::init();

		if ($this->unitModelClass === null)
			throw new InvalidConfigException('The "unitModelClass" property must be set.');

		if ($this->productModelClass === null)
			throw new InvalidConfigException('The "productModelClass" property must be set.');

		if ($this->saleableModelClass === null)
			throw new InvalidConfigException('The "saleableModelClass" property must be set.');

		if ($this->discountModelClass === null)
			throw new InvalidConfigException('The "discountModelClass" property must be set.');

		if ($this->discountUsageModelClass === null)
			throw new InvalidConfigException('The "discountUsageModelClass" property must be set.');

		if ($this->userAssetModelClass === null)
			throw new InvalidConfigException('The "userAssetModelClass" property must be set.');

		if ($this->basketModelClass === null)
			throw new InvalidConfigException('The "basketModelClass" property must be set.');

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
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$thisID . '/unit'],
					'pluralize' => false,
				],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$thisID . '/discount'],
					'pluralize' => false,
				],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$thisID . '/discount-serial'],
					'pluralize' => false,
				],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$thisID . '/discount-usage'],
					'pluralize' => false,
				],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$thisID . '/product'],
					'pluralize' => false,
				],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$thisID . '/saleable'],
					'pluralize' => false,
				],
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$thisID . '/user-asset'],
					'pluralize' => false,
				],

				[
					'class' => \shopack\base\common\rest\UrlRule::class,
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
