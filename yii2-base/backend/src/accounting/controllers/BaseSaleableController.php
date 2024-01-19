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

	private static $_accountingModule = null;
	public static function getAccountingModule()
	{
		if (self::$_accountingModule == null) {
			self::$_accountingModule = Yii::$app->controller->module;
			if (self::$_accountingModule->id != 'accounting')
				self::$_accountingModule = self::$_accountingModule->accounting;
		}

		return self::$_accountingModule;
	}

	public function queryAugmentaters()
	{
		$accountingModule = self::getAccountingModule();
		$actorID = (Yii::$app->user->isGuest ? 0 : Yii::$app->user->id);
		$modelClass = $this->modelClass;

		return [
			'index' => function($query) use ($accountingModule, $actorID, $modelClass) {
				$modelClass::appendDiscountQuery(
					$actorID,
					$accountingModule->discountModelClass,
					$accountingModule->discountUsageModelClass,
					$query
				);
				$query
					->joinWith('product')
					->joinWith('product.unit')
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
			'view' => function($query) use ($accountingModule, $actorID, $modelClass) {
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
