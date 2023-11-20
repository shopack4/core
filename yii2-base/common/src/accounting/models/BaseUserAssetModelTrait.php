<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\accounting\models;

use shopack\base\common\rest\ModelColumnHelper;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\rest\enuColumnSearchType;
use shopack\base\common\validators\JsonValidator;
use shopack\base\common\accounting\enums\enuUserAssetStatus;

/*
'uasID',
'uasUUID',
'uasActorID',
'uasSaleableID',
'uasQty',
'uasVoucherID',
'uasVoucherItemInfo',
'uasDiscountID',
'uasDiscountAmount',
'uasPrefered',
'uasValidFromDate',
'uasValidToDate',
'uasValidFromHour',
'uasValidToHour',
'uasDurationMinutes',
'uasBreakedAt',
'uasStatus',
'uasCreatedAt',
'uasCreatedBy',
'uasUpdatedAt',
'uasUpdatedBy',
'uasRemovedAt',
'uasRemovedBy',
*/
trait BaseUserAssetModelTrait
{
  public static $primaryKey = ['uasID'];

	public function primaryKeyValue() {
		return $this->uasID;
	}

  public static function columnsInfo()
  {
    return [
      'uasID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'uasUUID' => ModelColumnHelper::UUID(),

      'uasActorID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
      'uasSaleableID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
      'uasQty' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
      'uasVoucherID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'uasVoucherItemInfo' => [
				enuColumnInfo::type       => JsonValidator::class,
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false,
				enuColumnInfo::selectable => true,
			],
      'uasDiscountID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'uasDiscountAmount' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'uasPrefered' => [
        enuColumnInfo::type       => 'boolean',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => false,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
      'uasValidFromDate' => [
        enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'uasValidToDate' => [
        enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'uasValidFromHour' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'uasValidToHour' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'uasDurationMinutes' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'uasBreakedAt' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'uasStatus' => [
        enuColumnInfo::isStatus   => true,
        enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => enuUserAssetStatus::Pending,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
      ],

      'uasCreatedAt' => ModelColumnHelper::CreatedAt(),
      'uasCreatedBy' => ModelColumnHelper::CreatedBy(),
      'uasUpdatedAt' => ModelColumnHelper::UpdatedAt(),
      'uasUpdatedBy' => ModelColumnHelper::UpdatedBy(),
      'uasRemovedAt' => ModelColumnHelper::RemovedAt(),
      'uasRemovedBy' => ModelColumnHelper::RemovedBy(),
    ];
  }

  public function getCreatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'uasCreatedBy']);
	}

	public function getUpdatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'uasUpdatedBy']);
	}

	public function getRemovedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'uasRemovedBy']);
	}

  abstract public static function getActorModelClassInfo();
	public function getActor() {
    list ($class, $pk) = $this->getActorModelClassInfo();
		return $this->hasOne($class, [$pk => 'uasActorID']);
	}

  abstract public static function getSaleableModelClass();
	public function getSaleable() {
		return $this->hasOne($this->getSaleableModelClass(), ['slbID' => 'uasSaleableID']);
	}

  abstract public static function getDiscountModelClass();
	public function getDiscount() {
		return $this->hasOne($this->getDiscountModelClass(), ['dscID' => 'uasDiscountID']);
	}

}
