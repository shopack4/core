<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\models;

use Yii;
use shopack\base\common\rest\ModelColumnHelper;
use shopack\base\common\rest\enuColumnInfo;
use shopack\aaa\common\enums\enuOfflinePaymentStatus;
use shopack\base\common\validators\GroupRequiredValidator;

/*
'ofpID',
'ofpUUID',
'ofpOwnerUserID',
'ofpVoucherID',
'ofpBankOrCart',
'ofpTrackNumber',
'ofpReferenceNumber',
'ofpAmount',
'ofpPayDate',
'ofpPayer',
'ofpSourceCartNumber',
'ofpImageFileID',
'ofpWalletID',
'ofpComment',
'ofpStatus',
'ofpCreatedAt',
'ofpCreatedBy',
'ofpUpdatedAt',
'ofpUpdatedBy',
'ofpRemovedAt',
'ofpRemovedBy',
*/
trait OfflinePaymentModelTrait
{
	public function primaryKeyValue() {
		return $this->ofpID;
	}

	public static function columnsInfo()
	{
		return [
			'ofpID' => [
				enuColumnInfo::type       => 'integer',
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => true,
			],
			'ofpUUID' => ModelColumnHelper::UUID(),
			'ofpOwnerUserID' => [
				enuColumnInfo::type       => 'integer',
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => true,
			],
			'ofpVoucherID' => [
				enuColumnInfo::type       => 'integer',
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => true,
			],
			'ofpBankOrCart' => [
				enuColumnInfo::type       => ['string', 'max' => 64],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => 'like',
			],
			'ofpTrackNumber' => [
				enuColumnInfo::type       => ['string', 'max' => 64],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => 'like',
			],
			'ofpReferenceNumber' => [
				enuColumnInfo::type       => ['string', 'max' => 64],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => 'like',
			],
			'ofpAmount' => [
				enuColumnInfo::type       => 'integer',
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
				enuColumnInfo::search     => true,
			],
			'ofpPayDate' => [
				enuColumnInfo::type       => 'safe',
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
        // enuColumnInfo::search     => 'like',
			],
			'ofpPayer' => [
				enuColumnInfo::type       => ['string', 'max' => 64],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => 'like',
			],
			'ofpSourceCartNumber' => [
				enuColumnInfo::type       => ['string', 'max' => 20],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => 'like',
			],
			'ofpImageFileID' => [
				enuColumnInfo::type       => 'safe', //'integer',
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
        // enuColumnInfo::search     => 'like',
			],
			'ofpWalletID' => [
				enuColumnInfo::type       => 'integer',
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false, //true,
				enuColumnInfo::selectable => true,
				enuColumnInfo::search     => true,
			],
			'ofpComment' => [
				enuColumnInfo::type       => ['string', 'max' => 65530],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => 'like',
			],
			'ofpStatus' => [
				enuColumnInfo::isStatus   => true,
				enuColumnInfo::type       => ['string', 'max' => 1],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => enuOfflinePaymentStatus::WaitForApprove,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => true,
			],

      'ofpCreatedAt' => ModelColumnHelper::CreatedAt(),
      'ofpCreatedBy' => ModelColumnHelper::CreatedBy(),
      'ofpUpdatedAt' => ModelColumnHelper::UpdatedAt(),
      'ofpUpdatedBy' => ModelColumnHelper::UpdatedBy(),
			'ofpRemovedAt' => ModelColumnHelper::RemovedAt(),
			'ofpRemovedBy' => ModelColumnHelper::RemovedBy(),
		];
	}

	public function traitExtraRules()
  {
    return [
      [[
				'ofpTrackNumber',
				'ofpReferenceNumber',
      ], GroupRequiredValidator::class,
        'min' => 1,
        'in' => [
					'ofpTrackNumber',
					'ofpReferenceNumber',
				],
        'message' => Yii::t('aaa', 'one of TrackNumber or ReferenceNumber is required'),
      ],
    ];
  }

	public function getCreatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'ofpCreatedBy']);
	}

	public function getUpdatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'ofpUpdatedBy']);
	}

	public function getRemovedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'ofpRemovedBy']);
	}

	public function getOwner() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'ofpOwnerUserID']);
	}

	public function getVoucher() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\VoucherModel';
		else
			$className = '\shopack\aaa\frontend\common\models\VoucherModel';

		return $this->hasOne($className, ['vchID' => 'ofpVoucherID']);
	}

  public function getImageFile() {
		$className = get_called_class();

    if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UploadFileModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UploadFileModel';

    return $this->hasOne($className, ['uflID' => 'ofpImageFileID']);
  }

	public function getWallet() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\WalletModel';
		else
			$className = '\shopack\aaa\frontend\common\models\WalletModel';

		return $this->hasOne($className, ['walID' => 'ofpWalletID']);
	}

}
