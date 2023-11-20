<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\accounting\controllers;

use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\data\ActiveDataProvider;
use shopack\base\common\helpers\ExceptionHelper;
use shopack\base\backend\controller\BaseCrudController;
use shopack\base\backend\helpers\PrivHelper;

abstract class BaseSaleableController extends BaseCrudController
{
	public function fillGlobalSearchFromRequest(\yii\db\ActiveQuery $query, $q)
	{
		if (empty($q))
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
