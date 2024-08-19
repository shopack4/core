<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\rest;

use Closure;
use Yii;
use Ramsey\Uuid\Uuid;
use shopack\base\common\helpers\ArrayHelper;
use shopack\base\common\helpers\JsonSchema;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\helpers\StringHelper;
use shopack\base\common\validators\JsonValidator;

trait ActiveRecordTrait
{
	public static $i18nDataFields = [];

	protected $_cachedColumnsInfo = null;
	public function getColumnsInfo()
	{
		if ($this->_cachedColumnsInfo === null) {
			$this->_cachedColumnsInfo = $this->columnsInfo();
		}

		return $this->_cachedColumnsInfo;
	}

  public function getStatusColumnName()
  {
    $columnsInfo = $this->getColumnsInfo();
    foreach ($columnsInfo as $column => $colInfo) {
      if ($colInfo[enuColumnInfo::isStatus] ?? false)
        return $column;
    }

		return null;
  }

	public function canViewColumn($column)
	{
		$columnsInfo = $this->getColumnsInfo();
		if (empty($columnsInfo[$column]))
			return false;

		return $this->_canViewColumn($column, $columnsInfo[$column]);
	}

	private function _canViewColumn($column, $columnInfo)
	{
		if (isset($columnInfo[enuColumnInfo::selectable])) {
			if (is_array($columnInfo[enuColumnInfo::selectable])) {
				foreach ($columnInfo[enuColumnInfo::selectable] as $perm) {
					$p = (array)$perm;
					if (Yii::$app->user->hasPriv($p[0], $p[1] ?? '1')) {
						return true;
					}
				}
			} else if (is_bool($columnInfo[enuColumnInfo::selectable])
					&& $columnInfo[enuColumnInfo::selectable]) {
				return true;
			}
		}
		return false;
	}

	protected static $_selectableColumns = null;
	public static function selectableColumns($prfix = null)
  {
		$_class = get_called_class();

		if (empty(self::$_selectableColumns[$_class])) {
			$columns = [];

			$model = new $_class;

			$columnsInfo = $model->getColumnsInfo();
			foreach ($columnsInfo as $column => $colInfo) {
				if ($model->_canViewColumn($column, $colInfo)) {
					$columns[] = $column;
				}
			}

			self::$_selectableColumns[$_class] = $columns;
		}

		if (empty($prfix))
			return self::$_selectableColumns[$_class];

		$columns = [];
		foreach (self::$_selectableColumns[$_class] as $column) {
			$columns[] = $prfix . '.' . $column;
		}
		return $columns;
  }

	// protected static $_globalSearchableColumns = null;
	// public static function globalSearchableColumns()
  // {
	// 	$_class = get_called_class();

	// 	if (empty(self::$_globalSearchableColumns[$_class])) {
	// 		$columns = [];

	// 		$columnsInfo = $this->getColumnsInfo();
	// 		foreach ($columnsInfo as $column => $colInfo) {
	// 			if (isset($colInfo[enuColumnInfo::globalSearch])) {
	// 				$columns[$column] = $colInfo;
	// 			}
	// 		}
	// 		self::$_globalSearchableColumns[$_class] = $columns;
	// 	}

	// 	return self::$_globalSearchableColumns[$_class];
  // }

	protected static $_rules = null;
  public function rules()
  {
		$_class = get_called_class();
		$isSearchModel = str_ends_with($_class, 'SearchModel');

		if (empty(self::$_rules[$_class])) {
			$baseRules = [];

			$columnsInfo = $this->getColumnsInfo();
			foreach ($columnsInfo as $column => $colInfo) {
				// if (isset($colInfo[enuColumnInfo::virtual]) && $colInfo[enuColumnInfo::virtual])
				// 	continue;

				if ($isSearchModel) {
					if (isset($colInfo[enuColumnInfo::search])) {
						if ($colInfo[enuColumnInfo::search] !== false) {
							// if (is_bool($colInfo[enuColumnInfo::search])) {
								if (isset($colInfo[enuColumnInfo::type]))
									$rule = array_merge([$column], (array)$colInfo[enuColumnInfo::type]);
								else
									$rule = [$column, 'safe'];
							// } else {
							// 	$rule = array_merge([$column], (array)$colInfo[enuColumnInfo::search]);
							// }
							$baseRules[] = $rule;
						}
					}
				} else {
					if (isset($colInfo[enuColumnInfo::type])) {
						$rule = array_merge([$column], (array)$colInfo[enuColumnInfo::type]);
						$baseRules[] = $rule;
					}

					if (isset($colInfo[enuColumnInfo::validator])) {
						$rule = array_merge([$column], (array)$colInfo[enuColumnInfo::validator]);
						$baseRules[] = $rule;
					}

					if (isset($colInfo[enuColumnInfo::default])) {
						$rule = [
							$column,
							'default',
							'value' => $colInfo[enuColumnInfo::default]
						];
						$baseRules[] = $rule;
					}

					if (isset($colInfo[enuColumnInfo::required])
							&& ($colInfo[enuColumnInfo::required] !== false)
					) {
						$rule = [
							$column,
							'required'
						];

						if (is_array($colInfo[enuColumnInfo::required])) {
							$rule = array_merge($rule, $colInfo[enuColumnInfo::required]);
						}

						$baseRules[] = $rule;
					}
				}
			}

			//-------------
			$rules = [];

			$fnAddRule = function($newRule) use (&$rules, $isSearchModel) {
				if ($isSearchModel && ($newRule[1] == 'required')) {
					return;
				}

				//merge same attr and same validator
				foreach ($rules as $rk => $rv) {
					if (is_array($rv) && ($rv[0] == $newRule[0]) && ($rv[1] == $newRule[1])) {
						$rules[$rk] = array_replace_recursive($rules[$rk], $newRule);
						$newRule = null;
						break;
					}
				}

				if ($newRule !== null) {
					$rules[] = $newRule;
				}
			};

			$fnAddRules = function($newRules) use (&$rules, $isSearchModel, $fnAddRule) {
				if (empty($newRules))
					return;

				foreach ($newRules as $k => $newRule) {
					$fnAddRule($newRule);
				}
			};

			$fnAddRules($baseRules);

			if ($isSearchModel == false) {
				if (method_exists($this, 'traitExtraRules'))
					// $rules = array_merge_recursive($rules, $this->traitExtraRules());
					$fnAddRules($this->traitExtraRules());
			}

			if (method_exists($this, 'extraRules')) {
				$fnAddRules($this->extraRules());
			}

			self::$_rules[$_class] = $rules;
		}

		return self::$_rules[$_class];
	}

	protected function checkColumnsBeforeSave($insert)
	{
		$JsonValidator_class = JsonValidator::class;

		$dirtyAttributes = $this->getDirtyAttributes();
		$columnsInfo = $this->getColumnsInfo();

		foreach ($columnsInfo as $column => $colInfo) {
			$columnValue = $this->$column;

			//uuid
			if ($insert
				&& isset($colInfo[enuColumnInfo::default])
				&& ($colInfo[enuColumnInfo::default] == 'uuid')
				&& (empty($columnValue) || $columnValue == 'uuid')
			) {
				$this->$column = strtolower(Uuid::uuid4()->toString());
				continue;
			}

			if (isset($dirtyAttributes[$column]) == false)
				continue;

			//json
			if (isset($colInfo[enuColumnInfo::type])
				&& ($colInfo[enuColumnInfo::type] === $JsonValidator_class)
			) {
				if (is_string($columnValue))
					$columnValue = json_decode($columnValue, true);

				if (empty($columnValue) == false)
					$columnValue = ArrayHelper::FilterRecursive($columnValue);

				if (empty($columnValue)) {
					if (($colInfo[enuColumnInfo::required] ?? false))
						$columnValue = [];
					else
						$columnValue = null;
				}

				//jsonSchema
				if (isset($colInfo[enuColumnInfo::jsonSchema])) {
					//normalize jsonTable
					if (isset($columnValue['rows']) == false) {
						$columnValue = [
							'rows' => $columnValue,
						];
					}

					$oldAttrValue = $this->oldAttributes[$column] ?? null;
					if (isset($oldAttrValue['lastid'])) {
						$columnValue['lastid'] = $oldAttrValue['lastid'];
					}

					if (empty($columnValue['rows']) == false) {

						$jsonSchema = $colInfo[enuColumnInfo::jsonSchema];
						foreach($jsonSchema['fields'] as $f) {
							if (empty($f['pk']) == false) {
								$pkField = $f;
								break;
							}
						}

						//find last id
						if (isset($pkField)) {
							$pkFieldName = $pkField[0];

							if ($pkField['type'] == JsonSchema::TYPE_uuid) {
								//apply id for new rows
								foreach ($columnValue['rows'] as $k => $row) {
									if (empty($row[$pkFieldName])) {
										$row[$pkFieldName] = str_replace('-', '', strtolower(Uuid::uuid4()->toString()));
										$columnValue['rows'][$k] = $row;
									}
								}
							} else { //if ($pkField['type'] == JsonSchema::TYPE_number)
								if (empty($columnValue['lastid'])) {
									// $oldAttrValue = $this->oldAttributes[$column] ?? null;

									// if (isset($oldAttrValue['lastid']))
									// 	$lastid = $oldAttrValue['lastid'];
									// else
										$lastid = 0;

									foreach ($columnValue['rows'] as $row) {
										if (($row[$pkFieldName] ?? 0) > $lastid)
											$lastid = $row[$pkFieldName];
									}
									// $columnValue['lastid'] = $lastid;
								} else
									$lastid = $columnValue['lastid'];

								//apply id for new rows
								foreach ($columnValue['rows'] as $k => $row) {
									if (empty($row[$pkFieldName])) {
										++$lastid;

										$row[$pkFieldName] = $lastid;
										$columnValue['rows'][$k] = $row;
									}
								}

								$columnValue['lastid'] = $lastid;
							} //pk:num
						}

						//remove array key
						$rows = [];
						foreach ($columnValue['rows'] as $k => $row) {
							$rows[] = $row;
						}
						$columnValue['rows'] = $rows;
					}

					$columnValue = ArrayHelper::FilterRecursive($columnValue);
				}
			} //json

			//
			if (is_string($columnValue)) {
				$columnValue = trim($columnValue);
			}

			if (($columnValue === '')
				&& (empty($colInfo[enuColumnInfo::required]) || ($colInfo[enuColumnInfo::required] !== true))
			) {
				$columnValue = null;
			}

			if (is_string($columnValue) && (empty($columnValue) == false)) {
				$columnValue = StringHelper::fixPersianCharacters($columnValue);
			}

			$this->$column = $columnValue;
		}
	}

	// public function beforeSave($insert)
  // {
	// 	$this->checkColumnsBeforeSave($insert);
	// 	return parent::beforeSave($insert);
  // }

	public function save($runValidation = true, $attributeNames = null)
	{
		$this->checkColumnsBeforeSave($this->isNewRecord);

		return parent::save($runValidation, $attributeNames);
	}

	public function applyDefaultValuesFromColumnsInfo()
	{
		$columnsInfo = $this->getColumnsInfo();
		foreach ($columnsInfo as $column => $colInfo) {
			if (empty($this->$column) && isset($colInfo[enuColumnInfo::default])) {

				$def = $colInfo[enuColumnInfo::default];

				if ($def == 'uuid') {
					continue; //->will be filled in checkColumnsBeforeSave

					$uuid = Uuid::uuid4();

					// $def = $uuid->getBytes();
					// $def = '0x' . $uuid->getHex()->toString();
					$def = strtolower($uuid->toString());
				}

				$this->$column = $def;
			}
		}
	}

	public function exposeAttributes($isInRelation = false)
	{
		$result = [];

		//columns
		$columnsInfo = $this->getColumnsInfo();
		foreach ($columnsInfo as $column => $columnInfo) {
			if ($this->hasAttribute($column) == false)
				continue;

			$value = $this->$column;

			if ($value === null)
				continue;

			if (array_key_exists(enuColumnInfo::filter, $columnInfo)) {
				$filter = $columnInfo[enuColumnInfo::filter];

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
					$result[$k] = (empty($v) ? $v : $v->exposeAttributes(true));
			}
		}

		return $result;
	}

}
