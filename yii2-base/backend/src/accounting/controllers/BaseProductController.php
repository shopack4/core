<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\accounting\controllers;

use shopack\base\backend\controller\BaseCrudController;

abstract class BaseProductController extends BaseCrudController
{
	public function fillGlobalSearchFromRequest(\yii\db\ActiveQuery $query, $q)
	{
		if (empty($q) || ($q == '***'))
			return;

		$query->andWhere([
			'OR',
			['LIKE', 'prdCode', $q],
			['LIKE', 'prdName', $q],
		]);
	}

}
