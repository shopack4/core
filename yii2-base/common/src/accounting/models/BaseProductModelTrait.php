<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\accounting\models;

use shopack\base\common\rest\ModelColumnHelper;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\rest\enuColumnSearchType;
use shopack\base\common\validators\JsonValidator;
use shopack\base\common\accounting\enums\enuProductStatus;
use shopack\base\common\accounting\enums\enuProductType;

/*
'prdID',
'prdUUID',
'prdCode',
'prdName',
'prdDesc',
'prdType',
'prdValidFromDate',
'prdValidToDate',
'prdValidFromHour',
'prdValidToHour',
'prdDurationMinutes',
'prdStartAtFirstUse',
'prdPrivs',
'prdVAT',
'prdUnitID',
'prdQtyIsDecimal',
'prdInStockQty',
'prdOrderedQty',
'prdReturnedQty',
'prdI18NData',
'prdStatus',
'prdCreatedAt',
'prdCreatedBy',
'prdUpdatedAt',
'prdUpdatedBy',
'prdRemovedAt',
'prdRemovedBy',
*/
trait BaseProductModelTrait
{
  public static $primaryKey = ['prdID'];

	public function primaryKeyValue() {
		return $this->prdID;
	}

  public function columnsInfo()
  {
    return [
      'prdID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
      ],
      'prdUUID' => ModelColumnHelper::UUID(),
      'prdCode' => [
        enuColumnInfo::type       => ['string', 'max' => 38], //same as uuid length
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
      ],
			'prdName' => [
        enuColumnInfo::type       => ['string', 'max' => 64],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
      ],
      'prdDesc' => [
        enuColumnInfo::type       => ['string', 'max' => 128],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
      ],
			'prdType' => [
        enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => enuProductType::Physical,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
      'prdValidFromDate' => [
        enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => true,
      ],
      'prdValidToDate' => [
        enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => true,
      ],
      'prdValidFromHour' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => true,
      ],
      'prdValidToHour' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => true,
      ],
      'prdDurationMinutes' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => true,
      ],
      'prdStartAtFirstUse' => [
        enuColumnInfo::type       => 'boolean',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => false,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
      'prdPrivs' => [
        enuColumnInfo::type       => JsonValidator::class,
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'prdVAT' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'prdUnitID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
      'prdQtyIsDecimal' => [
        enuColumnInfo::type       => 'boolean',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => false,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
      'prdInStockQty' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'prdOrderedQty' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'prdReturnedQty' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
			'prdI18NData' => ModelColumnHelper::I18NData(['prdName', 'prdDesc']),
      'prdStatus' => [
        enuColumnInfo::isStatus   => true,
        enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => enuProductStatus::Active,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
      ],

      'prdCreatedAt' => ModelColumnHelper::CreatedAt(),
      'prdCreatedBy' => ModelColumnHelper::CreatedBy(),
      'prdUpdatedAt' => ModelColumnHelper::UpdatedAt(),
      'prdUpdatedBy' => ModelColumnHelper::UpdatedBy(),
      'prdRemovedAt' => ModelColumnHelper::RemovedAt(),
      'prdRemovedBy' => ModelColumnHelper::RemovedBy(),
    ];
  }

  public function getCreatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'prdCreatedBy']);
	}

	public function getUpdatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'prdUpdatedBy']);
	}

	public function getRemovedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'prdRemovedBy']);
	}

  abstract public static function getUnitModelClass();
	public function getUnit() {
		return $this->hasOne($this->getUnitModelClass(), ['untID' => 'prdUnitID']);
	}

}
