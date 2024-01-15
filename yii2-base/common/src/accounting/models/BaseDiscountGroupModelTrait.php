<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\accounting\models;

use shopack\base\common\rest\ModelColumnHelper;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\rest\enuColumnSearchType;
use shopack\base\common\validators\JsonValidator;
use shopack\base\common\accounting\enums\enuDiscountGroupComputeType;

/*
'dscgrpID',
'dscgrpUUID',
'dscgrpName',
'dscgrpComputeType',
'dscgrpMaxAmount',
'dscgrpMaxType',
'dscgrpCreatedAt',
'dscgrpCreatedBy',
'dscgrpUpdatedAt',
'dscgrpUpdatedBy',
'dscgrpRemovedAt',
'dscgrpRemovedBy',
*/
trait BaseDiscountGroupModelTrait
{
  public static $primaryKey = ['dscgrpID'];

	public function primaryKeyValue() {
		return $this->dscgrpID;
	}

  public function columnsInfo()
  {
    return [
      'dscgrpID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'dscgrpUUID' => ModelColumnHelper::UUID(),
			'dscgrpName' => [
        enuColumnInfo::type       => ['string', 'max' => 128],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
      ],
			'dscgrpComputeType' => [
        enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => enuDiscountGroupComputeType::Max,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => true,
      ],
			'dscgrpMaxAmount' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
			'dscgrpMaxType' => [
        enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],

      'dscgrpCreatedAt' => ModelColumnHelper::CreatedAt(),
      'dscgrpCreatedBy' => ModelColumnHelper::CreatedBy(),
      'dscgrpUpdatedAt' => ModelColumnHelper::UpdatedAt(),
      'dscgrpUpdatedBy' => ModelColumnHelper::UpdatedBy(),
			'dscgrpRemovedAt' => ModelColumnHelper::RemovedAt(),
			'dscgrpRemovedBy' => ModelColumnHelper::RemovedBy(),
    ];
  }

  public function getCreatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'dscgrpCreatedBy']);
	}

	public function getUpdatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'dscgrpUpdatedBy']);
	}

	public function getRemovedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'dscgrpRemovedBy']);
	}

}
