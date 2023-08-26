<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\userpanel;

use Yii;
use yii\base\BootstrapInterface;
use shopack\aaa\frontend\common\controllers\AuthController;
use shopack\aaa\frontend\common\controllers\ProfileController;
// use shopack\aaa\frontend\common\controllers\BasketController;
// use shopack\aaa\frontend\common\controllers\WalletController;
// use shopack\aaa\frontend\common\controllers\FinController;
// use shopack\aaa\frontend\common\controllers\OrderController;

class Module
	extends \shopack\base\common\base\BaseModule
	implements BootstrapInterface
{
	public $allowSignup = true;

	public function init()
	{
		if (empty($this->id))
			$this->id = 'aaa';

		parent::init();
	}

	public function bootstrap($app)
	{
		if ($app instanceof \yii\web\Application) {

			$this->controllerMap['auth'] = AuthController::class;
			$this->controllerMap['profile'] = ProfileController::class;
			// $this->controllerMap['basket'] = BasketController::class;
			// $this->controllerMap['wallet'] = WalletController::class;
			// $this->controllerMap['fin'] = FinController::class;
			// $this->controllerMap['order'] = OrderController::class;

			$rules = [
				[
					'class' => 'yii\web\UrlRule',
					'pattern' => $this->id . '/gateway/webhook/<gtwUUID:[\w-]+>/<command:[\w-]+>',
					'route' => $this->id . '/gateway/webhook',
				],
			];

			$app->urlManager->addRules($rules, false);

			$this->addDefaultRules($app);

		} elseif ($app instanceof \yii\console\Application) {
			$this->controllerNamespace = 'shopack\aaa\frontend\userpanel\commands';
		}
	}

}
