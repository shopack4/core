<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use Yii;
use shopack\base\backend\controller\BaseCrudController;
use shopack\aaa\backend\models\WalletTransactionModel;

class WalletTransactionController extends BaseCrudController
{
	public $modelClass = WalletTransactionModel::class;

	public function permissions()
	{
		$checkOwner = function($model) : bool {
			return (($model != null) && ($model['wallet']['walOwnerUserID'] == Yii::$app->user->id));
		};

		return [
			'index'  => [
										'aaa/wallet-transaction/crud' => '0100',
										'filter' => function($query) {
											Yii::$app->user->assertIsNotGuest();
											$query->andWhere(['walOwnerUserID' => Yii::$app->user->id]);
										},
									],
			'view'   => ['aaa/wallet-transaction/crud' => '0100', 'checker' => $checkOwner],
			// 'create' => ['aaa/wallet-transaction/crud' => '1000', 'checker' => $checkOwner],
			// 'update' => ['aaa/wallet-transaction/crud' => '0010', 'checker' => $checkOwner],
			// 'delete' => ['aaa/wallet-transaction/crud' => '0001', 'checker' => $checkOwner],
			// 'undelete' => ['aaa/wallet-transaction/undelete'],
		];
	}

	public function queryAugmentaters()
	{
		return [
			'index' => function($query) {
				$query
					->joinWith('wallet')
					->joinWith('voucher')
					->joinWith('onlinePayment')
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
			'view' => function($query) {
				$query
					->joinWith('wallet')
					->joinWith('voucher')
					->joinWith('onlinePayment')
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
		];
	}

}
