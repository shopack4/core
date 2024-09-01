<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use Yii;
use shopack\base\backend\helpers\PrivHelper;
use shopack\base\backend\controller\BaseCrudController;
use shopack\aaa\backend\models\OfflinePaymentModel;

class OfflinePaymentController extends BaseCrudController
{
	public $modelClass = OfflinePaymentModel::class;

	public function permissions()
	{
		$checkOwner = function($model) : bool {
			return (($model != null) && ($model['ofpOwnerUserID'] == Yii::$app->user->id));
		};

		return [
			'index'  => [
										'aaa/offline-payment/crud' => '0100',
										'filter' => function($query) {
											Yii::$app->user->assertIsNotGuest();
											$query->andWhere(['ofpOwnerUserID' => Yii::$app->user->id]);
										},
									],
			'view'   => ['aaa/offline-payment/crud' => '0100', 'checker' => $checkOwner],
			'create' => ['aaa/offline-payment/crud' => '1000', 'checker' => $checkOwner],
			'update' => ['aaa/offline-payment/crud' => '0010', 'checker' => $checkOwner],
			'delete' => ['aaa/offline-payment/crud' => '0001', 'checker' => $checkOwner],
			'undelete' => ['aaa/offline-payment/undelete'],
		];
	}

	public function queryAugmentaters()
	{
		return [
			'index' => function($query) {
				$query
					->joinWith('owner')
					->joinWith('voucher')
					->joinWith('imageFile')
					->joinWith('wallet')
					->with('destBank')
					->with('destBankKart')
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
			'view' => function($query) {
				$query
					->joinWith('owner')
					->joinWith('voucher')
					->joinWith('imageFile')
					->joinWith('wallet')
					->with('destBank')
					->with('destBankKart')
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
		];
	}

	public function actionAccept($id)
	{
		PrivHelper::checkPriv('aaa/offline-payment/accept');

		$model = $this->findModel($id);
		$model->doAccept();

		return [
			'result' => true,
		];
	}

	public function actionReject($id)
	{
		PrivHelper::checkPriv('aaa/offline-payment/reject');

		$bodyParams = Yii::$app->request->getBodyParams();

		$model = $this->findModel($id);
		$model->doReject($bodyParams['reasons'] ?? null, $bodyParams['comment'] ?? null);

		return [
			'result' => true,
		];
	}

}
