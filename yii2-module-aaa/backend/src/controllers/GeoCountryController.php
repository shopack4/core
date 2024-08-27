<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use shopack\base\backend\controller\BaseCrudController;
use shopack\aaa\backend\models\GeoCountryModel;

class GeoCountryController extends BaseCrudController
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

	public $modelClass = GeoCountryModel::class;

	public function permissions()
	{
		return [
			// 'index'  => ['aaa/geo-country/crud' => '0100'],
			// 'view'   => ['aaa/geo-country/crud' => '0100'],
			'create' => ['aaa/geo-country/crud' => '1000'],
			'update' => ['aaa/geo-country/crud' => '0010'],
			'delete' => ['aaa/geo-country/crud' => '0001'],
			'undelete' => ['aaa/geo-country/undelete'],
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
