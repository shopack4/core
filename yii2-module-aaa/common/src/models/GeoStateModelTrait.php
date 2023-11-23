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
'sttID',
'sttUUID',
'sttName',
'sttCountryID',
'sttCreatedAt',
'sttCreatedBy',
'sttUpdatedAt',
'sttUpdatedBy',
'sttRemovedAt',
'sttRemovedBy',
*/
trait GeoStateModelTrait
{
  public static $primaryKey = ['sttID'];

	public function primaryKeyValue() {
		return $this->sttID;
	}

  public function columnsInfo()
  {
    return [
      'sttID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'sttUUID' => ModelColumnHelper::UUID(),
			'sttName' => [
        enuColumnInfo::type       => ['string', 'max' => 64],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'sttCountryID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],

			'sttCreatedAt' => ModelColumnHelper::CreatedAt(),
      'sttCreatedBy' => ModelColumnHelper::CreatedBy(),
      'sttUpdatedAt' => ModelColumnHelper::UpdatedAt(),
      'sttUpdatedBy' => ModelColumnHelper::UpdatedBy(),
      'sttRemovedAt' => ModelColumnHelper::RemovedAt(),
      'sttRemovedBy' => ModelColumnHelper::RemovedBy(),
    ];
  }

  public function getCreatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'sttCreatedBy']);
	}

	public function getUpdatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'sttUpdatedBy']);
	}

	public function getRemovedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'sttRemovedBy']);
	}

	public function getCountry() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\GeoCountryModel';
		else
			$className = '\shopack\aaa\frontend\common\models\GeoCountryModel';

		return $this->hasOne($className, ['cntrID' => 'sttCountryID']);
	}

}
