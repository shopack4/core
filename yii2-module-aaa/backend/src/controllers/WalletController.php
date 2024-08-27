<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use shopack\base\common\helpers\ExceptionHelper;
use shopack\base\backend\controller\BaseCrudController;
use shopack\aaa\backend\models\WalletModel;
use shopack\aaa\backend\models\WalletIncreaseForm;

class WalletController extends BaseCrudController
{
	public function beforeAction($action)
  {
		if ($action->id != 'ensure-i-have-default-wallet')
	    WalletModel::ensureIHaveDefaultWallet();

    return parent::beforeAction($action);
  }

	public $modelClass = WalletModel::class;

	public function permissions()
	{
		$checkOwner = function($model) : bool {
			return (($model != null) && ($model['walOwnerUserID'] == Yii::$app->user->id));
		};

		return [
			'index'  => [
										'aaa/wallet/crud' => '0100',
										'filter' => function($query) {
											Yii::$app->user->assertIsNotGuest();
											$query->andWhere(['walOwnerUserID' => Yii::$app->user->id]);
										},
									],
			'view'   => ['aaa/wallet/crud' => '0100', 'checker' => $checkOwner],
			// 'create' => ['aaa/wallet/crud' => '1000', 'checker' => $checkOwner],
			// 'update' => ['aaa/wallet/crud' => '0010', 'checker' => $checkOwner],
			// 'delete' => ['aaa/wallet/crud' => '0001', 'checker' => $checkOwner],
			// 'undelete' => ['aaa/wallet/undelete'],
		];
	}

	public function queryAugmentaters()
	{
		return [
			'index' => function($query) {
				$query
					->joinWith('owner')
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
			'view' => function($query) {
				$query
					->joinWith('owner')
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
		];
	}

	public function actionEnsureIHaveDefaultWallet()
	{
    return $this->modelToResponse(WalletModel::ensureIHaveDefaultWallet());
	}

	public function actionIncrease($id)
	{
		$model = new WalletIncreaseForm();
		$model->walletID = $id;

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		try {
			$result = $model->process();
			if ($result == false)
				throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

			return $result;

		} catch(\Exception $exp) {
			$msg = ExceptionHelper::CheckDuplicate($exp, $model);
			throw new UnprocessableEntityHttpException($msg);
		}
	}

}
