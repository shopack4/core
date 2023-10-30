<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\db;

use shopack\base\common\rest\enuColumnInfo;

trait SearchModelTrait
{
  public function applySearchValuesInQuery($query, $params = null)
  {
    $columnsInfo = static::columnsInfo();
    foreach ($columnsInfo as $column => $info) {
      // if (empty($info[enuColumnInfo::search]))
      //   continue;

      if (isset($params[$column]))
        $value = $params[$column];
      else
        $value = $this->$column;

      if ($value !== null) {
        if (isset($info[enuColumnInfo::search])) {
          if (is_bool($info[enuColumnInfo::search])) {
            $query->andFilterWhere([$column => $value]);
          } else {
            $query->andFilterWhere([$info[enuColumnInfo::search], $column, $value]);
          }
        } else {
          $query->andFilterWhere([$column => $value]);
        }
      }
    }
  }

}
