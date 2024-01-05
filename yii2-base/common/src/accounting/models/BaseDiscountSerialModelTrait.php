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
'dscsnID',
'dscsnDiscountID',
'dscsnSN',
*/
trait BaseDiscountSerialModelTrait
{
  public static $primaryKey = ['dscsnID'];

	public function primaryKeyValue() {
		return $this->dscsnID;
	}

  public function columnsInfo()
  {
    return [
      'dscsnID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'dscsnDiscountID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
			'dscsnSN' => [
        enuColumnInfo::type       => ['string', 'max' => 64],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
      ],
    ];
  }

  abstract public static function getDiscountModelClass();
	public function getDiscount() {
		return $this->hasOne($this->getDiscountModelClass(), ['dscID' => 'dscsnDiscountID']);
	}

}
