<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\cmn\backend\controllers;

use shopack\base\backend\controller\BaseCrudController;

class LanguageController extends BaseCrudController
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

	public $modelClass = \shopack\cmn\backend\models\LanguageModel::class;

	public function permissions()
	{
		return [
			// 'index'  => ['cmn/language/crud' => '0100'],
			// 'view'   => ['cmn/language/crud' => '0100'],
			'create' => ['cmn/language/crud' => '1000'],
			'update' => ['cmn/language/crud' => '0010'],
			'delete' => ['cmn/language/crud' => '0001'],
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
