<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\accounting\controllers;

use shopack\base\backend\controller\BaseCrudController;
use Yii;

abstract class BaseSaleableController extends BaseCrudController
{
	public function fillGlobalSearchFromRequest(\yii\db\ActiveQuery $query, $q)
	{
		if (empty($q) || ($q == '***'))
			return;

		$query->andWhere([
			'OR',
			['LIKE', 'slbCode', $q],
			['LIKE', 'slbName', $q],
			['LIKE', 'prdCode', $q],
			['LIKE', 'prdName', $q],
		]);
	}

	public function queryAugmentaters()
	{
		$actorID = (Yii::$app->user->isGuest ? 0 : Yii::$app->user->id);
		$modelClass = $this->modelClass;

		return [
			'index' => function($query) use ($actorID, $modelClass) {
				$modelClass::appendDiscountQuery($query, $actorID);
				$query
					->joinWith('product')
					->joinWith('product.unit')
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},

			'view' => function($query) use ($actorID, $modelClass) {
				$modelClass::appendDiscountQuery($query, $actorID);
				$query
					->joinWith('product')
					->joinWith('product.unit')
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
		];
	}

}
