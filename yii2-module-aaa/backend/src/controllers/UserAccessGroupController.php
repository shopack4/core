<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use shopack\base\backend\controller\BaseCrudController;
use shopack\aaa\backend\models\UserAccessGroupModel;

class UserAccessGroupController extends BaseCrudController
{
	public $modelClass = UserAccessGroupModel::class;

	public function permissions()
	{
		return [
			'index'  => ['aaa/user-access-group/crud' => '0100'],
			'view'   => ['aaa/user-access-group/crud' => '0100'],
			'create' => ['aaa/user-access-group/crud' => '1000'],
			'update' => ['aaa/user-access-group/crud' => '0010'],
			'delete' => ['aaa/user-access-group/crud' => '0001'],
			'undelete' => ['aaa/user-access-group/undelete'],
		];
	}

	public function queryAugmentaters()
	{
		return [
			'index' => function($query) {
				$query
					->joinWith('user')
					->joinWith('accessGroup')
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
			'view' => function($query) {
				$query
					->joinWith('user')
					->joinWith('accessGroup')
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
		];
	}

}
