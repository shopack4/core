<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\models;

use shopack\base\common\rest\ModelColumnHelper;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\rest\enuColumnSearchType;
use shopack\aaa\common\enums\enuUploadQueueStatus;

/*
'uquID',
'uquUUID',
'uquFileID',
'uquGatewayID',
'uquLockedAt',
'uquLockedBy',
'uquLastTryAt',
'uquStoredAt',
'uquResult',
'uquStatus',
'uquCreatedAt',
'uquCreatedBy',
'uquUpdatedAt',
'uquUpdatedBy',
'uquRemovedAt',
'uquRemovedBy',
*/
trait UploadQueueModelTrait
{
  public static $primaryKey = ['uquID'];

	public function primaryKeyValue() {
		return $this->uquID;
	}

  public function columnsInfo()
  {
    return [
      'uquID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'uquUUID' => ModelColumnHelper::UUID(),
			'uquFileID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
      'uquGatewayID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
      ],
			'uquLockedAt' => [
				enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
			],
			'uquLockedBy' => [
				enuColumnInfo::type       => ['string', 'max' => 64],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
			],
			'uquLastTryAt' => [
				enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
			],
			'uquStoredAt' => [
				enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
			],
			'uquResult' => [
				enuColumnInfo::type       => 'string', //TEXT
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
			],
      'uquStatus' => [
        enuColumnInfo::isStatus   => true,
        enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => enuUploadQueueStatus::New,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
      ],

      'uquCreatedAt' => ModelColumnHelper::CreatedAt(),
      'uquCreatedBy' => ModelColumnHelper::CreatedBy(),
      'uquUpdatedAt' => ModelColumnHelper::UpdatedAt(),
      'uquUpdatedBy' => ModelColumnHelper::UpdatedBy(),
      'uquRemovedAt' => ModelColumnHelper::RemovedAt(),
      'uquRemovedBy' => ModelColumnHelper::RemovedBy(),
    ];
  }

  public function getCreatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'uquCreatedBy']);
	}

	public function getUpdatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'uquUpdatedBy']);
	}

	public function getRemovedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'uquRemovedBy']);
	}

  public function getUploadFile() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UploadFileModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UploadFileModel';

		// return $this->hasOne($className, ['uflID' => 'uquFileID']);

    $query = $className::find(false);
    $query->primaryModel = $this;
    $query->link = ['uflID' => 'uquFileID'];
    $query->multiple = false;
    return $query;

	}

  public function getGateway() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\GatewayModel';
		else
			$className = '\shopack\aaa\frontend\common\models\GatewayModel';

		return $this->hasOne($className, ['gtwID' => 'uquGatewayID']);
	}

}
