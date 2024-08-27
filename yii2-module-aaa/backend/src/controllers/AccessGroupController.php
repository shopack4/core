<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use shopack\base\backend\controller\BaseCrudController;
use shopack\aaa\backend\models\AccessGroupModel;

class AccessGroupController extends BaseCrudController
{
	public $modelClass = AccessGroupModel::class;

	public function permissions()
	{
		return [
			'index'  => ['aaa/access-group/crud' => '0100'],
			'view'   => ['aaa/access-group/crud' => '0100'],
			'create' => ['aaa/access-group/crud' => '1000'],
			'update' => ['aaa/access-group/crud' => '0010'],
			'delete' => ['aaa/access-group/crud' => '0001'],
			'undelete' => ['aaa/access-group/undelete'],
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
