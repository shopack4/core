<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\models;

use shopack\base\common\rest\ModelColumnHelper;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\rest\enuColumnSearchType;
use shopack\base\common\validators\JsonValidator;
use shopack\aaa\common\enums\enuGatewayStatus;

/*
'gtwID',
'gtwUUID',
'gtwName',
'gtwPluginType',
'gtwPluginName',
'gtwPluginParameters',
'gtwRestrictions',
'gtwUsages',
'gtwStatus',
'gtwCreatedAt',
'gtwCreatedBy',
'gtwUpdatedAt',
'gtwUpdatedBy',
'gtwRemovedAt',
'gtwRemovedBy',
*/
trait GatewayModelTrait
{
	public static $primaryKey = ['gtwID'];

	public function primaryKeyValue() {
		return $this->gtwID;
	}

	public static function columnsInfo()
	{
		return [
			'gtwID' => [
				enuColumnInfo::type       => 'integer',
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
			],
			'gtwUUID' => ModelColumnHelper::UUID(),
			'gtwName' => [
				enuColumnInfo::type       => ['string', 'max' => 64],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
			],
			'gtwPluginType' => [
				enuColumnInfo::type       => ['string', 'max' => 48],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
			],
			'gtwPluginName' => [
				enuColumnInfo::type       => ['string', 'max' => 48],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
			],
			'gtwPluginParameters' => [
				enuColumnInfo::type       => JsonValidator::class,
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => [
					['aaa/gateway/crud', '1000'],
					['aaa/gateway/crud', '0010']
				], //only for admins with create OR update permission
			],
			'gtwRestrictions' => [
				enuColumnInfo::type       => JsonValidator::class,
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false,
				enuColumnInfo::selectable => true,
			],
			'gtwUsages' => [
				enuColumnInfo::type       => JsonValidator::class,
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false,
				enuColumnInfo::selectable => true,
			],
			'gtwStatus' => [
				enuColumnInfo::isStatus   => true,
				enuColumnInfo::type       => ['string', 'max' => 1],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => enuGatewayStatus::Active,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
			],

      'gtwCreatedAt' => ModelColumnHelper::CreatedAt(),
      'gtwCreatedBy' => ModelColumnHelper::CreatedBy(),
      'gtwUpdatedAt' => ModelColumnHelper::UpdatedAt(),
      'gtwUpdatedBy' => ModelColumnHelper::UpdatedBy(),
			'gtwRemovedAt' => ModelColumnHelper::RemovedAt(),
			'gtwRemovedBy' => ModelColumnHelper::RemovedBy(),
		];
	}

	public function getCreatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'gtwCreatedBy']);
	}

	public function getUpdatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'gtwUpdatedBy']);
	}

	public function getRemovedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'gtwRemovedBy']);
	}

}
