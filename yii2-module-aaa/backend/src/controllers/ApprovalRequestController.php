<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use Yii;
use shopack\base\backend\controller\BaseCrudController;
use shopack\aaa\backend\models\ApprovalRequestModel;

class ApprovalRequestController extends BaseCrudController
{
	public $modelClass = ApprovalRequestModel::class;

	public function permissions()
	{
		$checkOwner = function($model) : bool {
			return (($model != null) && ($model['aprUserID'] == Yii::$app->user->id));
		};

		return [
			'index'  => [
				'aaa/approval-request/crud' => '0100',
				'filter' => function($query) {
					Yii::$app->user->assertIsNotGuest();
					$query->andWhere(['aprUserID' => Yii::$app->user->id]);
				},
			],
			'view'   => ['aaa/approval-request/crud' => '0100', 'checker' => $checkOwner],
			// 'create' => ['aaa/approval-request/crud' => '1000', 'checker' => $checkOwner],
			// 'update' => ['aaa/approval-request/crud' => '0010', 'checker' => $checkOwner],
			// 'delete' => ['aaa/approval-request/crud' => '0001', 'checker' => $checkOwner],
			// 'undelete' => ['aaa/approval-request/undelete'],
		];
	}

	public function queryAugmentaters()
	{
		return [
			'index' => function($query) {
				$query
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
			'view' => function($query) {
				$query
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
		];
	}

}
