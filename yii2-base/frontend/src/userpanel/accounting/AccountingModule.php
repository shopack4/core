<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\userpanel\accounting;

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
		if ($app instanceof \yii\web\Application) {
			$this->controllerNamespace = str_replace('\controllers', '\accounting\controllers', $this->module->controllerNamespace);

			$this->addDefaultRules($app, $this->module->id);

			// $rules = [
			// 	[
			// 		'class' => 'yii\web\UrlRule',
			// 		'pattern' => $this->module->id . '/' . $this->id . '/add-to-basket',
			// 		'route' => $this->module->id . '/' . $this->id . '/default/add-to-basket',
			// 	],
			// ];
			// $app->urlManager->addRules($rules, false);

		} else if ($app instanceof \yii\console\Application) {
			$this->controllerNamespace = str_replace('\commands', '\accounting\commands', $this->module->controllerNamespace);
		}
	}

}
