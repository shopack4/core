<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\rest;

use Yii;
use Ramsey\Uuid\Uuid;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\helpers\StringHelper;

trait ActiveRecordTrait
{
  public function getStatusColumnName()
  {
    $columnsInfo = $this->columnsInfo();
    foreach ($columnsInfo as $column => $info) {
      if ($info[enuColumnInfo::isStatus] ?? false)
        return $column;
    }

		return null;
  }

	public function canViewColumn($column)
	{
		$columnsInfo = $this->columnsInfo();
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

			$columnsInfo = $model->columnsInfo();
			foreach ($columnsInfo as $column => $info) {
				if ($model->_canViewColumn($column, $info)) {
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

	// 		$columnsInfo = $this->columnsInfo();
	// 		foreach ($columnsInfo as $column => $info) {
	// 			if (isset($info[enuColumnInfo::globalSearch])) {
	// 				$columns[$column] = $info;
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

			$columnsInfo = $this->columnsInfo();
			foreach ($columnsInfo as $column => $info) {
				// if (isset($info[enuColumnInfo::virtual]) && $info[enuColumnInfo::virtual])
				// 	continue;

				if ($isSearchModel) {
					if (isset($info[enuColumnInfo::search])) {
						if ($info[enuColumnInfo::search] !== false) {
							// if (is_bool($info[enuColumnInfo::search])) {
								if (isset($info[enuColumnInfo::type]))
									$rule = array_merge([$column], (array)$info[enuColumnInfo::type]);
								else
									$rule = [$column, 'safe'];
							// } else {
							// 	$rule = array_merge([$column], (array)$info[enuColumnInfo::search]);
							// }
							$baseRules[] = $rule;
						}
					}
				} else {
					if (isset($info[enuColumnInfo::type])) {
						$rule = array_merge([$column], (array)$info[enuColumnInfo::type]);
						$baseRules[] = $rule;
					}

					if (isset($info[enuColumnInfo::validator])) {
						$rule = array_merge([$column], (array)$info[enuColumnInfo::validator]);
						$baseRules[] = $rule;
					}

					if (isset($info[enuColumnInfo::default])) {
						$rule = [
							$column,
							'default',
							'value' => $info[enuColumnInfo::default]
						];
						$baseRules[] = $rule;
					}

					if (isset($info[enuColumnInfo::required])
							&& ($info[enuColumnInfo::required] !== false)
					) {
						$rule = [
							$column,
							'required'
						];

						if (is_array($info[enuColumnInfo::required])) {
							$rule = array_merge($rule, $info[enuColumnInfo::required]);
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
		$columnsInfo = $this->columnsInfo();
		foreach ($columnsInfo as $column => $info) {

			//uuid
			if ($insert
				&& isset($info[enuColumnInfo::default])
				&& ($info[enuColumnInfo::default] == 'uuid')
				&& (empty($this->$column) || $this->$column == 'uuid')
			) {
				$this->$column = strtolower(Uuid::uuid4()->toString());
			}

			//string

			// if (empty($info[enuColumnInfo::type]))
			// 	continue;

			// if (((array)$info[enuColumnInfo::type])[0] != 'string')
			// 	continue;

			if (is_string($this->$column))
				$this->$column = trim($this->$column);

			if (($this->$column === '') &&
					(empty($info[enuColumnInfo::required]) || ($info[enuColumnInfo::required] === false))
			) {
				$this->$column = null;
			}

			if (is_string($this->$column) && (empty($this->$column) == false)) {
				$this->$column = StringHelper::fixPersianCharacters($this->$column);
			}

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
		$columnsInfo = $this->columnsInfo();
		foreach ($columnsInfo as $column => $info) {
			if (empty($this->$column) && isset($info[enuColumnInfo::default])) {

				$def = $info[enuColumnInfo::default];

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

}
