<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\cmn\frontend\adminpanel;

use yii\base\BootstrapInterface;

class Module
	extends \shopack\base\common\base\BaseModule
	implements BootstrapInterface
{
	public function init()
	{
		if (empty($this->id))
			$this->id = 'cmn';

		parent::init();
	}

	public function bootstrap($app)
	{
		if ($app instanceof \yii\web\Application) {
			// $rules = [
			// 	[
			// 		'class' => 'yii\web\UrlRule',
			// 		'pattern' => $this->id . '/sample-entity/webhook/<gtwUUID:[\w-]+>/<command:[\w-]+>',
			// 		'route' => $this->id . '/sample-entity/webhook',
			// 	],
			// ];

			// $app->urlManager->addRules($rules, false);

			$this->addDefaultRules($app);

		} elseif ($app instanceof \yii\console\Application) {
			$this->controllerNamespace = 'shopack\cmn\frontend\adminpanel\commands';
		}
	}

}
