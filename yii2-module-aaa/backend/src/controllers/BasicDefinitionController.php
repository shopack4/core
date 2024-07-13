<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use shopack\base\backend\controller\BaseCrudController;

class BasicDefinitionController extends BaseCrudController
{
	public function behaviors()
	{
		$behaviors = parent::behaviors();

		$behaviors[static::BEHAVIOR_AUTHENTICATOR]['except'] = [
			'index',
			'view',
		];

		return $behaviors;
	}

	public $modelClass = \shopack\aaa\backend\models\BasicDefinitionModel::class;

	public function permissions()
	{
		return [
			// 'index'  => ['aaa/basic-definition/crud', '0100'],
			// 'view'   => ['aaa/basic-definition/crud', '0100'],
			'create' => ['aaa/basic-definition/crud', '1000'],
			'update' => ['aaa/basic-definition/crud', '0010'],
			'delete' => ['aaa/basic-definition/crud', '0001'],
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
