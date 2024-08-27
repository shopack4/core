<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use shopack\base\backend\controller\BaseCrudController;
use shopack\aaa\backend\models\RoleModel;

class RoleController extends BaseCrudController
{
	public $modelClass = RoleModel::class;

	public function permissions()
	{
		return [
			'index'  => ['aaa/role/crud' => '0100'],
			'view'   => ['aaa/role/crud' => '0100'],
			'create' => ['aaa/role/crud' => '1000'],
			'update' => ['aaa/role/crud' => '0010'],
			'delete' => ['aaa/role/crud' => '0001'],
			'undelete' => ['aaa/role/undelete'],
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
