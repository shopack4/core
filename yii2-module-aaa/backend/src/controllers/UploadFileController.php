<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use Yii;
use shopack\base\backend\controller\BaseCrudController;
use shopack\aaa\backend\models\UploadFileModel;

class UploadFileController extends BaseCrudController
{
	public $modelClass = UploadFileModel::class;

	public function permissions()
	{
		$checkOwner = function($model) : bool {
			return (($model != null) && ($model['uflOwnerUserID'] == Yii::$app->user->id));
		};

		return [
			'index'  => [
										'aaa/upload-file/crud' => '0100',
										'filter' => function($query) {
											Yii::$app->user->assertIsNotGuest();
											$query->andWhere(['uflOwnerUserID' => Yii::$app->user->id]);
										},
									],
			'view'   => ['aaa/upload-file/crud' => '0100', 'checker' => $checkOwner],
			'create' => ['aaa/upload-file/crud' => '1000', 'checker' => $checkOwner],
			'update' => ['aaa/upload-file/crud' => '0010', 'checker' => $checkOwner],
			'delete' => ['aaa/upload-file/crud' => '0001', 'checker' => $checkOwner],
			'undelete' => ['aaa/upload-file/undelete'],
		];
	}

	public function queryAugmentaters()
	{
		return [
			'index' => function($query) {
				$query
					->with('owner')
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
			'view' => function($query) {
				$query
					->with('owner')
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
		];
	}

}
