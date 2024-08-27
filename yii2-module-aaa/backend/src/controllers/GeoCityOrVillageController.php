<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use shopack\base\backend\controller\BaseCrudController;
use shopack\aaa\backend\models\GeoCityOrVillageModel;

class GeoCityOrVillageController extends BaseCrudController
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

	public $modelClass = GeoCityOrVillageModel::class;

	public function permissions()
	{
		return [
			// 'index'  => ['aaa/geo-city-or-village/crud' => '0100'],
			// 'view'   => ['aaa/geo-city-or-village/crud' => '0100'],
			'create' => ['aaa/geo-city-or-village/crud' => '1000'],
			'update' => ['aaa/geo-city-or-village/crud' => '0010'],
			'delete' => ['aaa/geo-city-or-village/crud' => '0001'],
			'undelete' => ['aaa/geo-city-or-village/undelete'],
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

	public function fillGlobalSearchFromRequest(\yii\db\ActiveQuery $query, $q)
	{
		if (empty($q) || ($q == '***'))
			return;

		$query->andWhere([
			'OR',
			['LIKE', 'ctvName', $q],
		]);
	}

}
