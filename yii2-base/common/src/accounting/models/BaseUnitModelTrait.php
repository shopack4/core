<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\accounting\models;

use shopack\base\common\rest\ModelColumnHelper;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\rest\enuColumnSearchType;
use shopack\base\common\validators\JsonValidator;

/*
'untID',
'untUUID',
'untName',
'untI18NData',
*/
trait BaseUnitModelTrait
{
  public static $primaryKey = ['untID'];

	public function primaryKeyValue() {
		return $this->untID;
	}

  public function columnsInfo()
  {
    return array_merge([
      'untID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'untUUID' => ModelColumnHelper::UUID(),
			'untName' => [
        enuColumnInfo::type       => ['string', 'max' => 64],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
    ],
		ModelColumnHelper::I18NData($this, 'untI18NData', ['untName'])
    );
  }

}
