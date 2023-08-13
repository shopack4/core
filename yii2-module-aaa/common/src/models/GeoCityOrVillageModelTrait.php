<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\models;

use shopack\base\common\rest\ModelColumnHelper;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\validators\JsonValidator;

/*
'ctvID',
'ctvUUID',
'ctvName',
'ctvStateID',
'ctvCreatedAt',
'ctvCreatedBy',
'ctvUpdatedAt',
'ctvUpdatedBy',
'ctvRemovedAt',
'ctvRemovedBy',
*/
trait GeoCityOrVillageModelTrait
{
  public function primaryKeyValue() {
		return $this->ctvID;
	}

  public static function columnsInfo()
  {
    return [
      'ctvID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'ctvUUID' => ModelColumnHelper::UUID(),
			'ctvName' => [
        enuColumnInfo::type       => ['string', 'max' => 64],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'ctvStateID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],

			'ctvCreatedAt' => ModelColumnHelper::CreatedAt(),
      'ctvCreatedBy' => ModelColumnHelper::CreatedBy(),
      'ctvUpdatedAt' => ModelColumnHelper::UpdatedAt(),
      'ctvUpdatedBy' => ModelColumnHelper::UpdatedBy(),
      'ctvRemovedAt' => ModelColumnHelper::RemovedAt(),
      'ctvRemovedBy' => ModelColumnHelper::RemovedBy(),
    ];
  }

  public function getCreatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'ctvCreatedBy']);
	}

	public function getUpdatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'ctvUpdatedBy']);
	}

	public function getRemovedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'ctvRemovedBy']);
	}

	public function getState() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\GeoStateModel';
		else
			$className = '\shopack\aaa\frontend\common\models\GeoStateModel';

		return $this->hasOne($className, ['sttID' => 'ctvStateID']);
	}

}
