<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\rest;

use Yii;
use shopack\base\common\helpers\Json;
use shopack\base\backend\rest\RestServerQuery;

abstract class RestServerActiveRecord extends \yii\db\ActiveRecord
	implements \shopack\base\common\rest\ActiveRecordInterface
{
	use \shopack\base\common\rest\ActiveRecordTrait;

  public $filterKey = 'filter';
	public $orderByKey = 'order-by';

	public static function find() //: RestServerQuery
	{
    $query = \Yii::createObject(RestServerQuery::class, [
      get_called_class()
    ]);

    return $query;
	}

	// public function fillGlobalSearchFromRequest(\yii\db\ActiveQuery $query, $q)
	// {
	// 	if (empty($q) || ($q == '***'))
	// 		return;

	// 	$globalSearchableColumns = $this->globalSearchableColumns();

	// 	if (empty($globalSearchableColumns)) {
	// 		//warning
	// 		return;
	// 	}

	// 	$likes = ['OR'];
	// 	foreach ($globalSearchableColumns as $column => $info) {
	// 		// if ($column[enuColumnInfo::search] == )
	// 		$likes[] = ['like', $column, $q];
	// 	}

	// 	$query->andWhere($likes);
	// }

	public function fillQueryFromRequest(\yii\db\ActiveQuery $query)
	{
		$queryParams = Yii::$app->request->getQueryParams();

		//-------------
		$this->fillQueryOrderByPart($queryParams, $query);

		//-------------
		$this->_fillQueryFilterPart($queryParams, $query);

		//-------------
		foreach ($queryParams as $k => $v) {
			if ($this->hasAttribute($k))
				$query->andWhere([$k => $v]);
		}
	}

	private function _fillQueryFilterPart(&$queryParams, &$query)
	{
		if (empty($queryParams[$this->filterKey]))
			return;

		$query->where = Json::decode($queryParams[$this->filterKey]);

		// $filters =
		// foreach ($filters as $filter) {
		// 	$query->andWhere($filter);

		// 	// if ($this->hasAttribute($k))
		// 	// 	$query->andWhere([$k => $v]);
		// }

		unset ($queryParams[$this->filterKey]);
	}

	public function fillQueryOrderByPart(&$queryParams, &$query)
	{
		if (empty($queryParams[$this->orderByKey]))
			return;

		$orders = explode(',', $queryParams[$this->orderByKey]);

		foreach ($orders as $order) {
			if (str_starts_with($order, '-'))
				$query->addOrderBy([substr($order, 1) => SORT_DESC]);
			else
				$query->addOrderBy([$order => SORT_ASC]);
		}

		unset ($queryParams[$this->orderByKey]);
	}

}
