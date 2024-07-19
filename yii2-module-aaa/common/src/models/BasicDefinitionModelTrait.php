<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\models;

use shopack\base\common\rest\ModelColumnHelper;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\rest\enuColumnSearchType;
use shopack\aaa\common\enums\enuBasicDefinitionType;
use shopack\aaa\common\enums\enuBasicDefinitionStatus;

/*
'bdfID',
'bdfUUID',
'bdfType',
'bdfName',
'bdfI18NData',
'bdfStatus',
'bdfCreatedAt',
'bdfCreatedBy',
'bdfUpdatedAt',
'bdfUpdatedBy',
'bdfRemovedAt',
'bdfRemovedBy',
*/
trait BasicDefinitionModelTrait
{
  public static $primaryKey = ['bdfID'];

	public function primaryKeyValue() {
		return $this->bdfID;
	}

	public function columnsInfo()
	{
		return array_merge([
			'bdfID' => [
				enuColumnInfo::type       => 'integer',
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
			],
      'bdfUUID' => ModelColumnHelper::UUID(),
			'bdfType' => [
				enuColumnInfo::type       => ['string', 'max' => 1],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null, //enuBasicDefinitionType
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
				enuColumnInfo::search     => enuColumnSearchType::exact,
			],
			'bdfName' => [
				enuColumnInfo::type       => ['string', 'max' => 64],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
			],
		],
		ModelColumnHelper::I18NData($this, 'bdfI18NData', ['bdfName']),
		[
			'bdfStatus' => [
				enuColumnInfo::isStatus   => true,
				enuColumnInfo::type       => ['string', 'max' => 1],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => enuBasicDefinitionStatus::Active,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
			],

			'bdfCreatedAt' => ModelColumnHelper::CreatedAt(),
      'bdfCreatedBy' => ModelColumnHelper::CreatedBy(),
      'bdfUpdatedAt' => ModelColumnHelper::UpdatedAt(),
      'bdfUpdatedBy' => ModelColumnHelper::UpdatedBy(),
			'bdfRemovedAt' => ModelColumnHelper::RemovedAt(),
			'bdfRemovedBy' => ModelColumnHelper::RemovedBy(),
		]);
	}

	public function getCreatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'bdfCreatedBy']);
	}

	public function getUpdatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'bdfUpdatedBy']);
	}

	public function getRemovedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'bdfRemovedBy']);
	}

}
