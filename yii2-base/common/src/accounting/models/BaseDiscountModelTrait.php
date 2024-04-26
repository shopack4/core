<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\accounting\models;

use shopack\base\common\rest\ModelColumnHelper;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\rest\enuColumnSearchType;
use shopack\base\common\validators\JsonValidator;
use shopack\base\common\accounting\enums\enuDiscountStatus;
use shopack\base\common\accounting\enums\enuAmountType;
use shopack\base\common\accounting\enums\enuDiscountType;

/*
'dscID',
'dscUUID',
'dscName',
'dscType',
'dscCodeString',
'dscCodeHasSerial',
'dscCodeSerialCount',
'dscCodeSerialLength',
'dscValidFrom',
'dscValidTo',
'dscTotalMaxCount',
'dscTotalMaxPrice',
'dscPerUserMaxCount',
'dscPerUserMaxPrice',
'dscTargetUserIDs',
'dscTargetProductIDs',
'dscTargetSaleableIDs',
'dscReferrers',
'dscSaleableBasedMultiplier',
'dscAmount',
'dscAmountType',
'dscMaxAmount',
'dscTotalUsedCount',
'dscTotalUsedPrice',
'dscI18NData',
'dscStatus',
'dscCreatedAt',
'dscCreatedBy',
'dscUpdatedAt',
'dscUpdatedBy',
'dscRemovedAt',
'dscRemovedBy',
*/
trait BaseDiscountModelTrait
{
  public static $primaryKey = ['dscID'];

	public function primaryKeyValue() {
		return $this->dscID;
	}

  public function columnsInfo()
  {
    return [
      'dscID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'dscUUID' => ModelColumnHelper::UUID(),
			'dscName' => [
        enuColumnInfo::type       => ['string', 'max' => 64],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
      ],
      'dscType' => [
        enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => enuDiscountType::Coupon,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
      'dscCodeString' => [
        enuColumnInfo::type       => ['string', 'max' => 32],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => [
          'when' => function ($model) {
            return ($model->dscType == enuDiscountType::Coupon);
          },
        ],
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
      ],
			'dscCodeHasSerial' => [
        enuColumnInfo::type       => 'boolean',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
			'dscCodeSerialCount' => [
        enuColumnInfo::type       => ['number', 'min' => 100, 'max' => 10000],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => [
          'when' => function ($model) {
            return (($model->dscType == enuDiscountType::Coupon) && $model->dscCodeHasSerial);
          },
        ],
        enuColumnInfo::selectable => true,
      ],
			'dscCodeSerialLength' => [
        enuColumnInfo::type       => ['number', 'min' => 6, 'max' => 20],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => [
          'when' => function ($model) {
            return (($model->dscType == enuDiscountType::Coupon) && $model->dscCodeHasSerial);
          },
        ],
        enuColumnInfo::selectable => true,
      ],
			'dscValidFrom' => [
        enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
			'dscValidTo' => [
        enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
			'dscTotalMaxCount' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
			'dscTotalMaxPrice' => [
        enuColumnInfo::type       => 'double',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
			'dscPerUserMaxCount' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
			'dscPerUserMaxPrice' => [
        enuColumnInfo::type       => 'double',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'dscTargetUserIDs' => [
        enuColumnInfo::type       => JsonValidator::class,
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'dscTargetProductIDs' => [
        enuColumnInfo::type       => JsonValidator::class,
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'dscTargetSaleableIDs' => [
        enuColumnInfo::type       => JsonValidator::class,
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'dscReferrers' => [
        enuColumnInfo::type       => JsonValidator::class,
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
			'dscSaleableBasedMultiplier' => [
        enuColumnInfo::type       => JsonValidator::class,
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
			'dscAmount' => [
        enuColumnInfo::type       => ['double', 'min' => 0, 'max' => 100,
          'when' => function ($model) {
            return ($model->dscAmountType == enuAmountType::Percent);
          },
        ],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
			'dscAmountType' => [
        enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => enuAmountType::Percent,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
			'dscMaxAmount' => [
        enuColumnInfo::type       => 'double',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
			'dscTotalUsedCount' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
			'dscTotalUsedPrice' => [
        enuColumnInfo::type       => 'double',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],

      'dscI18NData' => ModelColumnHelper::I18NData(['dscName']),
			'dscStatus' => [
				enuColumnInfo::isStatus   => true,
				enuColumnInfo::type       => ['string', 'max' => 1],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => enuDiscountStatus::Active,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
			],

      'dscCreatedAt' => ModelColumnHelper::CreatedAt(),
      'dscCreatedBy' => ModelColumnHelper::CreatedBy(),
      'dscUpdatedAt' => ModelColumnHelper::UpdatedAt(),
      'dscUpdatedBy' => ModelColumnHelper::UpdatedBy(),
			'dscRemovedAt' => ModelColumnHelper::RemovedAt(),
			'dscRemovedBy' => ModelColumnHelper::RemovedBy(),
    ];
  }

  public function getCreatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'dscCreatedBy']);
	}

	public function getUpdatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'dscUpdatedBy']);
	}

	public function getRemovedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'dscRemovedBy']);
	}

}
