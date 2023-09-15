<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\models;

use shopack\base\common\rest\ModelColumnHelper;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\rest\enuColumnSearchType;
use shopack\base\common\validators\JsonValidator;

/*
'twnID',
'twnUUID',
'twnName',
'twnCityID',
'twnCreatedAt',
'twnCreatedBy',
'twnUpdatedAt',
'twnUpdatedBy',
'twnRemovedAt',
'twnRemovedBy',
*/
trait GeoTownModelTrait
{
  public static $primaryKey = ['twnID'];

	public function primaryKeyValue() {
		return $this->twnID;
	}

  public static function columnsInfo()
  {
    return [
      'twnID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'twnUUID' => ModelColumnHelper::UUID(),
			'twnName' => [
        enuColumnInfo::type       => ['string', 'max' => 64],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'twnCityID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],

			'twnCreatedAt' => ModelColumnHelper::CreatedAt(),
      'twnCreatedBy' => ModelColumnHelper::CreatedBy(),
      'twnUpdatedAt' => ModelColumnHelper::UpdatedAt(),
      'twnUpdatedBy' => ModelColumnHelper::UpdatedBy(),
      'twnRemovedAt' => ModelColumnHelper::RemovedAt(),
      'twnRemovedBy' => ModelColumnHelper::RemovedBy(),
    ];
  }

  public function getCreatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'twnCreatedBy']);
	}

	public function getUpdatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'twnUpdatedBy']);
	}

	public function getRemovedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'twnRemovedBy']);
	}

	public function getCityOrVillage() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\GeoCityOrVillageModel';
		else
			$className = '\shopack\aaa\frontend\common\models\GeoCityOrVillageModel';

		return $this->hasOne($className, ['ctvID' => 'twnCityID']);
	}

}
