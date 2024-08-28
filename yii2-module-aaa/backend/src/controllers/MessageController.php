<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use Yii;
use shopack\base\backend\controller\BaseCrudController;
use shopack\aaa\backend\models\MessageModel;

class MessageController extends BaseCrudController
{
	public $modelClass = MessageModel::class;

	public function permissions()
	{
		$checkOwner = function($model) : bool {
			return (($model != null) && ($model['msgUserID'] == Yii::$app->user->id));
		};

		return [
			'index'  => [
										'aaa/message/crud' => '0100',
										'filter' => function($query) {
											Yii::$app->user->assertIsNotGuest();
											$query->andWhere(['msgUserID' => Yii::$app->user->id]);
										},
									],
			'view'   => ['aaa/message/crud' => '0100', 'checker' => $checkOwner],
			'create' => ['aaa/message/crud' => '1000', 'checker' => $checkOwner],
			'update' => ['aaa/message/crud' => '0010', 'checker' => $checkOwner],
			'delete' => ['aaa/message/crud' => '0001', 'checker' => $checkOwner],
			'undelete' => ['aaa/message/undelete'],
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
