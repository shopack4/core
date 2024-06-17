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

	public $ownerUserLabel = null;
	public function getOwnerUserLabel()
	{
		if (empty($this->ownerUserLabel))
			return Yii::t('aaa', 'Owner');

		if (is_array($this->ownerUserLabel))
			return Yii::t($this->ownerUserLabel[0], $this->ownerUserLabel[1]);

		return $this->ownerUserLabel;
	}

	/*
		default:
			'userViewUrl' => [
				'url' => '/aaa/user/view',
				'idField' => 'id',
			],
	*/
	public $userViewUrl = null;
	public function createUserViewUrl($id)
	{
		if (empty($this->userViewUrl)) {
			$this->userViewUrl = [
				'url' => '/aaa/user/view',
				'idField' => 'id',
			];
		}

		return Url::to([
			$this->userViewUrl['url'],
			$this->userViewUrl['idField'] => $id,
		]);
	}

	/*
		default:
			'/aaa/user/select2-list'
	*/
	public $searchUserForSelect2ListUrl = null;
	public function getSearchUserForSelect2ListUrl()
	{
		if (empty($this->searchUserForSelect2ListUrl))
			return Url::to(['/aaa/user/select2-list']);

		return Url::to([$this->searchUserForSelect2ListUrl]);
	}

	public $offlinePaymentAfterAcceptUrl = null;
	public function createOfflinePaymentAfterAcceptUrl($id)
	{
		if (empty($this->offlinePaymentAfterAcceptUrl))
			return;

		return [
			/* 0 => */ $this->offlinePaymentAfterAcceptUrl['url'],
			$this->offlinePaymentAfterAcceptUrl['idField'] => $id,
		];
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
