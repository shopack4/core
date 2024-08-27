<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use shopack\base\backend\controller\BaseCrudController;
use shopack\aaa\backend\models\GeoTownModel;

class GeoTownController extends BaseCrudController
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

	public $modelClass = GeoTownModel::class;

	public function permissions()
	{
		return [
			// 'index'  => ['aaa/geo-town/crud' => '0100'],
			// 'view'   => ['aaa/geo-town/crud' => '0100'],
			'create' => ['aaa/geo-town/crud' => '1000'],
			'update' => ['aaa/geo-town/crud' => '0010'],
			'delete' => ['aaa/geo-town/crud' => '0001'],
			'undelete' => ['aaa/geo-town/undelete'],
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
