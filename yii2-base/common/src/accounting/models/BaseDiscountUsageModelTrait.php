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
'dscusgID',
'dscusgUserID',
'dscusgUserAssetID',
'dscusgDiscountID',
'dscusgDiscountSerialID',
'dscusgAmount',
'dscusgCreatedAt',
*/
trait BaseDiscountUsageModelTrait
{
  public static $primaryKey = ['dscusgID'];

	public function primaryKeyValue() {
		return $this->dscusgID;
	}

  public function columnsInfo()
  {
    return [
      'dscusgID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'dscusgUserID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
      'dscusgUserAssetID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
      'dscusgDiscountID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
      'dscusgDiscountSerialID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'dscusgAmount' => [
        enuColumnInfo::type       => 'double',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],

      'dscusgCreatedAt' => ModelColumnHelper::CreatedAt(),
    ];
  }

  public function getCreatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'dscusgCreatedAt']);
	}

  abstract public static function getDiscountModelClass();
	public function getDiscount() {
		return $this->hasOne($this->getDiscountModelClass(), ['dscID' => 'dscusgDiscountID']);
	}

  abstract public static function getDiscountSerialModelClass();
	public function getDiscountSerial() {
		return $this->hasOne($this->getDiscountSerialModelClass(), ['dscsnID' => 'dscusgDiscountSerialID']);
	}

}
