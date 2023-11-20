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
    $columnsInfo = static::columnsInfo();
    foreach ($columnsInfo as $column => $info) {
      if ($info[enuColumnInfo::isStatus] ?? false)
        return $column;
    }

		return null;
  }

	public static function canViewColumn($column)
	{
		$columnsInfo = static::columnsInfo();
		if (empty($columnsInfo[$column]))
			return false;

		return self::_canViewColumn($column, $columnsInfo[$column]);
	}

	private static function _canViewColumn($column, $columnInfo)
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

			$columnsInfo = static::columnsInfo();
			foreach ($columnsInfo as $column => $info) {
				if (self::_canViewColumn($column, $info)) {
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

	// 		$columnsInfo = static::columnsInfo();
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
			$rules = [];

			$columnsInfo = static::columnsInfo();
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
							$rules[] = $rule;
						}
					}
				} else {
					if (isset($info[enuColumnInfo::type])) {
						$rule = array_merge([$column], (array)$info[enuColumnInfo::type]);
						$rules[] = $rule;
					}

					if (isset($info[enuColumnInfo::validator])) {
						$rule = array_merge([$column], (array)$info[enuColumnInfo::validator]);
						$rules[] = $rule;
					}

					if (isset($info[enuColumnInfo::default])) {
						$rule = [
							$column,
							'default',
							'value' => $info[enuColumnInfo::default]
						];
						$rules[] = $rule;
					}

					if (isset($info[enuColumnInfo::required]) && $info[enuColumnInfo::required]) {
						$rule = [
							$column,
							'required'
						];
						$rules[] = $rule;
					}
				}
			}

			$fnAddRules = function($newRules) use (&$rules, $isSearchModel) {
				foreach ($newRules as $k => $newRule) {
					if ($isSearchModel && ($newRule[1] == 'required')) {
						unset($newRules[$k]);
					}
				}

				if (empty($newRules) == false)
					$rules = array_merge_recursive($rules, $newRules);
			};

			if ($isSearchModel == false) {
				if (method_exists($this, 'traitExtraRules'))
					$rules = array_merge_recursive($rules, $this->traitExtraRules());
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
		$columnsInfo = static::columnsInfo();
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
					(empty($info[enuColumnInfo::required]) || !$info[enuColumnInfo::required])
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
		$columnsInfo = static::columnsInfo();
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
