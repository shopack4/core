<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\accounting\controllers;

use shopack\base\backend\controller\BaseCrudController;

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

}
