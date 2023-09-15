<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\models;

use shopack\base\common\rest\ModelColumnHelper;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\rest\enuColumnSearchType;
use shopack\base\common\validators\JsonValidator;
use shopack\aaa\common\enums\enuOnlinePaymentStatus;

/*
'onpID',
'onpUUID',
'onpGatewayID',
'onpVoucherID',
'onpAmount',
'onpCallbackUrl',
'onpWalletID',
'onpTrackNumber',
'onpRRN',
'onpResult',
'onpStatus',
'onpCreatedAt',
'onpCreatedBy',
'onpUpdatedAt',
'onpUpdatedBy',
'onpRemovedAt',
'onpRemovedBy',
*/
trait OnlinePaymentModelTrait
{
	public static $primaryKey = ['onpID'];

	public function primaryKeyValue() {
		return $this->onpID;
	}

	public static function columnsInfo()
	{
		return [
			'onpID' => [
				enuColumnInfo::type       => 'integer',
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
			],
			'onpUUID' => ModelColumnHelper::UUID(),
			'onpGatewayID' => [
				enuColumnInfo::type       => 'integer',
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
			],
			'onpVoucherID' => [
				enuColumnInfo::type       => 'integer',
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
			],
			'onpAmount' => [
				enuColumnInfo::type       => 'integer',
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,

			],
			'onpCallbackUrl' => [
				enuColumnInfo::type       => ['string', 'max' => 1024],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
        // enuColumnInfo::search     => enuColumnSearchType::like,
			],
			'onpWalletID' => [
				enuColumnInfo::type       => 'integer',
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
				enuColumnInfo::search     => enuColumnSearchType::exact,
			],
			'onpTrackNumber' => [
				enuColumnInfo::type       => ['string', 'max' => 64],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
			],
			'onpRRN' => [
				enuColumnInfo::type       => ['string', 'max' => 64],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
			],
			'onpResult' => [
				enuColumnInfo::type       => JsonValidator::class,
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false,
				enuColumnInfo::selectable => true,
			],
			'onpStatus' => [
				enuColumnInfo::isStatus   => true,
				enuColumnInfo::type       => ['string', 'max' => 1],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => enuOnlinePaymentStatus::New,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
			],

      'onpCreatedAt' => ModelColumnHelper::CreatedAt(),
      'onpCreatedBy' => ModelColumnHelper::CreatedBy(),
      'onpUpdatedAt' => ModelColumnHelper::UpdatedAt(),
      'onpUpdatedBy' => ModelColumnHelper::UpdatedBy(),
			'onpRemovedAt' => ModelColumnHelper::RemovedAt(),
			'onpRemovedBy' => ModelColumnHelper::RemovedBy(),
		];
	}

	public function getCreatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'onpCreatedBy']);
	}

	public function getUpdatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'onpUpdatedBy']);
	}

	public function getRemovedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'onpRemovedBy']);
	}

	public function getGateway() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\GatewayModel';
		else
			$className = '\shopack\aaa\frontend\common\models\GatewayModel';

		return $this->hasOne($className, ['gtwID' => 'onpGatewayID']);
	}

	public function getVoucher() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\VoucherModel';
		else
			$className = '\shopack\aaa\frontend\common\models\VoucherModel';

		return $this->hasOne($className, ['vchID' => 'onpVoucherID']);
	}

	public function getWallet() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\WalletModel';
		else
			$className = '\shopack\aaa\frontend\common\models\WalletModel';

		return $this->hasOne($className, ['walID' => 'onpWalletID']);
	}

}
