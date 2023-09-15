<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\models;

use shopack\base\common\rest\ModelColumnHelper;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\rest\enuColumnSearchType;
use shopack\base\common\validators\JsonValidator;
use shopack\aaa\common\enums\enuUploadFileStatus;

/*
'uflID',
'uflUUID',
'uflOwnerUserID',
'uflPath',
'uflOriginalFileName',
'uflCounter',
'uflStoredFileName',
'uflSize',
'uflFileType',
'uflMimeType',
'uflLocalFullFileName',
'uflStatus',
'uflCreatedAt',
'uflCreatedBy',
'uflUpdatedAt',
'uflUpdatedBy',
'uflRemovedAt',
'uflRemovedBy',
*/
trait UploadFileModelTrait
{
  public $fullFileUrl;

  public static $primaryKey = ['uflID'];

	public function primaryKeyValue() {
		return $this->uflID;
	}

  public static function columnsInfo()
  {
    return [
      'uflID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'uflUUID' => ModelColumnHelper::UUID(),
      'uflOwnerUserID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
			'uflPath' => [
        enuColumnInfo::type       => ['string', 'max' => 64],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
      'uflOriginalFileName' => [
        enuColumnInfo::type       => ['string', 'max' => 256],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
      'uflCounter' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'uflStoredFileName' => [
        enuColumnInfo::type       => ['string', 'max' => 256],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'uflSize' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
      'uflFileType' => [
        enuColumnInfo::type       => ['string', 'max' => 64],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'uflMimeType' => [
        enuColumnInfo::type       => ['string', 'max' => 128],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'uflLocalFullFileName' => [
        enuColumnInfo::type       => 'string', //TEXT
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
      'uflStatus' => [
        enuColumnInfo::isStatus   => true,
        enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => enuUploadFileStatus::New,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
      ],

      'uflCreatedAt' => ModelColumnHelper::CreatedAt(),
      'uflCreatedBy' => ModelColumnHelper::CreatedBy(),
      'uflUpdatedAt' => ModelColumnHelper::UpdatedAt(),
      'uflUpdatedBy' => ModelColumnHelper::UpdatedBy(),
      'uflRemovedAt' => ModelColumnHelper::RemovedAt(),
      'uflRemovedBy' => ModelColumnHelper::RemovedBy(),
    ];
  }

  public function getCreatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'uflCreatedBy']);
	}

	public function getUpdatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'uflUpdatedBy']);
	}

	public function getRemovedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'uflRemovedBy']);
	}

  //ERROR: recursive join: file join user... join file...

	public function getOwner() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'uflOwnerUserID']);
	}

}
