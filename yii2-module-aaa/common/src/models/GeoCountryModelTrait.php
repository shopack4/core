<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\models;

use shopack\base\common\rest\ModelColumnHelper;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\validators\JsonValidator;

/*
'cntrID',
'cntrUUID',
'cntrName',
'cntrCreatedAt',
'cntrCreatedBy',
'cntrUpdatedAt',
'cntrUpdatedBy',
'cntrRemovedAt',
'cntrRemovedBy',
*/
trait GeoCountryModelTrait
{
  public function primaryKeyValue() {
		return $this->cntrID;
	}

  public static function columnsInfo()
  {
    return [
      'cntrID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'cntrUUID' => ModelColumnHelper::UUID(),
			'cntrName' => [
        enuColumnInfo::type       => ['string', 'max' => 64],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],

			'cntrCreatedAt' => ModelColumnHelper::CreatedAt(),
      'cntrCreatedBy' => ModelColumnHelper::CreatedBy(),
      'cntrUpdatedAt' => ModelColumnHelper::UpdatedAt(),
      'cntrUpdatedBy' => ModelColumnHelper::UpdatedBy(),
      'cntrRemovedAt' => ModelColumnHelper::RemovedAt(),
      'cntrRemovedBy' => ModelColumnHelper::RemovedBy(),
    ];
  }

  public function getCreatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'cntrCreatedBy']);
	}

	public function getUpdatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'cntrUpdatedBy']);
	}

	public function getRemovedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'cntrRemovedBy']);
	}

}
