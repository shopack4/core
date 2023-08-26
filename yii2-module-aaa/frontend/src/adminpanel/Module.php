<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel;

use Yii;
use yii\base\BootstrapInterface;
use shopack\base\common\helpers\Url;
use shopack\aaa\frontend\common\controllers\AuthController;
use shopack\aaa\frontend\common\controllers\ProfileController;
// use shopack\aaa\frontend\common\controllers\BasketController;
// use shopack\aaa\frontend\common\controllers\WalletController;
// use shopack\aaa\frontend\common\controllers\FinController;

class Module
	extends \shopack\base\common\base\BaseModule
	implements BootstrapInterface
{
	public $allowSignup = true;

	/*
		default:
			'globalUserViewLink' => [
				'url' => '/aaa/user/view',
				'idField' => 'id',
			],
	*/
	public $globalUserViewLink = null;
	public function createUserViewUrl($id)
	{
		if (empty($this->globalUserViewLink)) {
			$this->globalUserViewLink = [
				'url' => '/aaa/user/view',
				'idField' => 'id',
			];
		}

		return Url::to([
			$this->globalUserViewLink['url'],
			$this->globalUserViewLink['idField'] => $id,
		]);
	}

	/*
		default:
			'/aaa/user/select2-list'
	*/
	public $globalSearchUserForSelect2ListUrl = null;
	public function searchUserForSelect2ListUrl()
	{
		if (empty($this->globalSearchUserForSelect2ListUrl))
			return Url::to(['/aaa/user/select2-list']);

		return Url::to([$this->globalSearchUserForSelect2ListUrl]);
	}

	//------------
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
			$this->controllerNamespace = 'shopack\aaa\frontend\adminpanel\commands';
		}
	}

}
