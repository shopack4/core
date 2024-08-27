<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use Yii;
use shopack\base\backend\controller\BaseCrudController;
use shopack\aaa\backend\models\SessionModel;

class SessionController extends BaseCrudController
{
	public $modelClass = SessionModel::class;

	public function permissions()
	{
		$checkOwner = function($model) : bool {
			return (($model != null) && ($model['ssnUserID'] == Yii::$app->user->id));
		};

		return [
			'index'  => [
										'aaa/session/crud' => '0100',
										'filter' => function($query) {
											Yii::$app->user->assertIsNotGuest();
											$query->andWhere(['ssnUserID' => Yii::$app->user->id]);
										},
									],
			'view'   => ['aaa/session/crud' => '0100', 'checker' => $checkOwner],
			// 'create' => ['aaa/session/crud' => '1000', 'checker' => $checkOwner],
			// 'update' => ['aaa/session/crud' => '0010', 'checker' => $checkOwner],
			// 'delete' => ['aaa/session/crud' => '0001', 'checker' => $checkOwner],
			// 'undelete' => ['aaa/session/undelete'],
		];
	}

	public function queryAugmentaters()
	{
		return [
			'index' => function($query) {
				$query
					->with('user')
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
			'view' => function($query) {
				$query
					->with('user')
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
		];
	}

}
