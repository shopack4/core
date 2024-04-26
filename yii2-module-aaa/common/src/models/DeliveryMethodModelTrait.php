<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\models;

use shopack\base\common\rest\ModelColumnHelper;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\rest\enuColumnSearchType;
use shopack\base\common\validators\JsonValidator;
use shopack\aaa\common\enums\enuDeliveryMethodStatus;
use shopack\aaa\common\enums\enuDeliveryMethodType;

/*
'dlvID',
'dlvUUID',
'dlvName',
'dlvType',
'dlvAmount',
'dlvTotalUsedCount',
'dlvTotalUsedAmount',
'dlvI18NData',
'dlvStatus',
'dlvCreatedAt',
'dlvCreatedBy',
'dlvUpdatedAt',
'dlvUpdatedBy',
'dlvRemovedAt',
'dlvRemovedBy',
*/
trait DeliveryMethodModelTrait
{
  public static $primaryKey = ['dlvID'];

	public function primaryKeyValue() {
		return $this->dlvID;
	}

  public function columnsInfo()
  {
    return [
      'dlvID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
      ],
      'dlvUUID' => ModelColumnHelper::UUID(),
			'dlvName' => [
        enuColumnInfo::type       => ['string', 'max' => 128],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
      ],
			'dlvType' => [
        enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => enuDeliveryMethodType::SendToCustomer,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
      'dlvAmount' => [
        enuColumnInfo::type       => 'double',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => true,
      ],
      'dlvTotalUsedCount' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => true,
      ],
      'dlvTotalUsedAmount' => [
        enuColumnInfo::type       => 'double',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => true,
      ],
			'dlvI18NData' => ModelColumnHelper::I18NData(['dlvName']),
      'dlvStatus' => [
        enuColumnInfo::isStatus   => true,
        enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => enuDeliveryMethodStatus::Active,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
      ],

      'dlvCreatedAt' => ModelColumnHelper::CreatedAt(),
      'dlvCreatedBy' => ModelColumnHelper::CreatedBy(),
      'dlvUpdatedAt' => ModelColumnHelper::UpdatedAt(),
      'dlvUpdatedBy' => ModelColumnHelper::UpdatedBy(),
      'dlvRemovedAt' => ModelColumnHelper::RemovedAt(),
      'dlvRemovedBy' => ModelColumnHelper::RemovedBy(),
    ];
  }

  public function getCreatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'dlvCreatedBy']);
	}

	public function getUpdatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'dlvUpdatedBy']);
	}

	public function getRemovedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'dlvRemovedBy']);
	}

}
