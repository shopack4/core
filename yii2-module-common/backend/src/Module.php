<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\cmn\backend;

use yii\base\BootstrapInterface;

class Module
	extends \shopack\base\common\base\BaseModule
	implements BootstrapInterface
{
	// //used for trust message channel
	// public $servicesPublicKeys = [];

	public function init()
	{
		if (empty($this->id))
			$this->id = 'cmn';

		parent::init();
	}

	public function bootstrap($app)
	{
		if ($app instanceof \yii\web\Application) {
			$rules = [
				[
					'class' => \shopack\base\common\rest\UrlRule::class,
					// 'prefix' => 'v1',
					'controller' => [$this->id . '/language'],
					'pluralize' => false,
				],
			];

			$app->urlManager->addRules($rules, false);
		} elseif ($app instanceof \yii\console\Application) {
			$this->controllerNamespace = 'shopack\cmn\backend\commands';
		}
	}

}
