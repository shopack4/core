<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\rest;

use Closure;
use Yii;
use shopack\base\common\helpers\Json;
use shopack\base\backend\rest\RestServerQuery;
use shopack\base\common\helpers\ArrayHelper;
use shopack\base\common\db\DbExpression;
use shopack\base\common\rest\enuColumnInfo;

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

		return $query->select(self::selectableColumns());
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
			if ($this->hasAttribute($k)) {
				if (is_array($v) && array_key_exists('expression', $v)) {
					$v = new DbExpression($v['expression'], $v['params'] ?? []);
				}

				$query->andWhere([$k => $v]);
			} else if (property_exists($this, $k)) {
				$this->$k = $v;
			}
		}
	}

	private function _fillQueryFilterPart(&$queryParams, &$query)
	{
		$filters = ArrayHelper::getValue($queryParams, $this->filterKey, null);
		if (empty($filters))
			return;

		$query->where = [];

		$filters = Json::decode($filters);

		$fnCheckExpressions = function (&$item, $key) use (&$fnCheckExpressions) {
			if (is_array($item) == false)
				return;

			if (array_key_exists('expression', $item)) {
				$item = new DbExpression($item['expression'], $item['params'] ?? []);
			} else {
				array_walk($item, $fnCheckExpressions);
			}
		};

		array_walk($filters, $fnCheckExpressions);

		$query->where = $filters;
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

		unset($queryParams[$this->orderByKey]);
	}

	//just used for export to client
	public function adhocColumnsInfo()
	{
		return [];
	}

	public function exposeAttributes($isInRelation = false, $checkExposeFilter = true)
	{
		$result = [];

		//columns
		$columnsInfo = $this->getColumnsInfo();
		$adhocs = $this->adhocColumnsInfo();
		$columnsInfo = array_merge($columnsInfo, $adhocs);

		foreach ($columnsInfo as $column => $columnInfo) {
			// if ($this->hasAttribute($column) == false)
			// 	continue;

			$value = $this->$column;

			if ($value === null)
				continue;

			if ($checkExposeFilter && array_key_exists(enuColumnInfo::beFilter, $columnInfo)) {
				$filter = $columnInfo[enuColumnInfo::beFilter];

				if ($filter instanceof Closure || is_array($filter) && is_callable($filter))
					$filter = call_user_func($filter, $this, $column, $isInRelation);

				if ($filter)
					continue;
			}

			//store as array
			$result[$column] = $value;
		}

		//relations
		$relations = $this->getRelatedRecords();
		if (empty($relations) == false) {
			foreach ($relations as $k => $v) {
				if ($v !== null)
					$result[$k] = (empty($v) ? $v : $v->exposeAttributes(true, $checkExposeFilter));
			}
		}

		return $result;
	}
}
