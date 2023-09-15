<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\accounting\models;

use shopack\base\common\rest\ModelColumnHelper;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\rest\enuColumnSearchType;
use shopack\base\common\validators\JsonValidator;
use shopack\base\common\accounting\enums\enuCouponStatus;
use shopack\base\common\accounting\enums\enuAmountType;

/*
'cpnID',
'cpnUUID',
'cpnCode',
'cpnName',
'cpnPrimaryCount',
'cpnTotalMaxAmount',
'cpnPerUserMaxCount',
'cpnPerUserMaxAmount',
'cpnValidFrom',
'cpnValidTo',
'cpnAmount',
'cpnAmountType',
'cpnMaxAmount',
'cpnSaleableBasedMultiplier',
'cpnTotalUsedCount',
'cpnTotalUsedAmount',
'cpnI18NData',
'cpnStatus',
'cpnCreatedAt',
'cpnCreatedBy',
'cpnUpdatedAt',
'cpnUpdatedBy',
'cpnRemovedAt',
'cpnRemovedBy',
*/
trait BaseCouponModelTrait
{
  public static $primaryKey = ['cpnID'];

	public function primaryKeyValue() {
		return $this->cpnID;
	}

  public static function columnsInfo()
  {
    return [
      'cpnID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'cpnUUID' => ModelColumnHelper::UUID(),
			'cpnCode' => [
				enuColumnInfo::type       => ['string', 'max' => 32],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
				enuColumnInfo::search     => enuColumnSearchType::like,
			],
			'cpnName' => [
        enuColumnInfo::type       => ['string', 'max' => 64],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
      ],
			'cpnPrimaryCount' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
			'cpnTotalMaxAmount' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
			'cpnPerUserMaxCount' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
			'cpnPerUserMaxAmount' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
			'cpnValidFrom' => [
        enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
			'cpnValidTo' => [
        enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
			'cpnAmount' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
			'cpnAmountType' => [
        enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => enuAmountType::Percent,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
			'cpnMaxAmount' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
			'cpnSaleableBasedMultiplier' => [
        enuColumnInfo::type       => JsonValidator::class,
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
			'cpnTotalUsedCount' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
			'cpnTotalUsedAmount' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
			'cpnI18NData' => ModelColumnHelper::I18NData(['cpnName']),
			'cpnStatus' => [
				enuColumnInfo::isStatus   => true,
				enuColumnInfo::type       => ['string', 'max' => 1],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => enuCouponStatus::Active,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
			],

      'cpnCreatedAt' => ModelColumnHelper::CreatedAt(),
      'cpnCreatedBy' => ModelColumnHelper::CreatedBy(),
      'cpnUpdatedAt' => ModelColumnHelper::UpdatedAt(),
      'cpnUpdatedBy' => ModelColumnHelper::UpdatedBy(),
			'cpnRemovedAt' => ModelColumnHelper::RemovedAt(),
			'cpnRemovedBy' => ModelColumnHelper::RemovedBy(),

    ];
  }

  public function getCreatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'cpnCreatedBy']);
	}

	public function getUpdatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'cpnUpdatedBy']);
	}

	public function getRemovedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'cpnRemovedBy']);
	}

}
