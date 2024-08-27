<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\models;

use Yii;
use shopack\base\common\rest\ModelColumnHelper;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\rest\enuColumnSearchType;
use shopack\aaa\common\enums\enuOfflinePaymentStatus;
use shopack\aaa\common\enums\enuOfflinePaymentType;
// use shopack\base\common\validators\GroupRequiredValidator;
use shopack\base\common\validators\JsonValidator;

/*
'ofpID',
'ofpUUID',
'ofpOwnerUserID',
'ofpVoucherID',
'ofpType',
'ofpDestCartNumber',
'ofpDestAccountNumber',
'ofpDestISBN',
'ofpDestBankID',
'ofpDestName',
'ofpDueDate,
'ofpTrackNumber',
'ofpReferenceNumber',
'ofpAmount',
'ofpPayDate',
'ofpPayer',
'ofpSourceCartNumber',
'ofpImageFileID',
'ofpWalletID',
'ofpComment',
'ofpRejectReasonIDs',
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
	public static $primaryKey = ['ofpID'];

	public function primaryKeyValue() {
		return $this->ofpID;
	}

	public function columnsInfo()
	{
		return [
			'ofpID' => [
				enuColumnInfo::type       => 'integer',
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
			],
			'ofpUUID' => ModelColumnHelper::UUID(),
			'ofpOwnerUserID' => [
				enuColumnInfo::type       => 'integer',
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
			],
			'ofpVoucherID' => [
				enuColumnInfo::type       => 'integer',
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
			],
			'ofpType' => [
				enuColumnInfo::type       => ['string', 'max' => 1],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
				enuColumnInfo::search     => enuColumnSearchType::exact,
			],
			'ofpDestCartNumber' => [
				enuColumnInfo::type       => ['string', 'max' => 64],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => [
          'when' => function ($model) {
            return ($model->ofpType == enuOfflinePaymentType::ToCart);
          },
					'conditions' => [
						'ofpType:checked' => enuOfflinePaymentType::ToCart,
					],
				],
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
			],
			'ofpDestAccountNumber' => [
				enuColumnInfo::type       => ['string', 'max' => 64],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => [
          'when' => function ($model) {
            return ($model->ofpType == enuOfflinePaymentType::ToAccountNumber);
          },
					'conditions' => [
						'ofpType:checked' => enuOfflinePaymentType::ToAccountNumber,
					],
				],
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
			],
			'ofpDestISBN' => [
				enuColumnInfo::type       => ['string', 'max' => 64],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => [
          'when' => function ($model) {
            return ($model->ofpType == enuOfflinePaymentType::ToISBN);
          },
					'conditions' => [
						'ofpType:checked' => enuOfflinePaymentType::ToISBN,
					],
				],
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
			],
			'ofpDestBankID' => [
				enuColumnInfo::type       => 'integer',
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => [
          'when' => function ($model) {
            return (in_array($model->ofpType, [
							enuOfflinePaymentType::Pos,
							enuOfflinePaymentType::ToCart,
							enuOfflinePaymentType::ToAccountNumber,
							enuOfflinePaymentType::ToISBN,
						]));
          },
					'conditions' => [
						'ofpType:checked' => [
							enuOfflinePaymentType::Pos,
							enuOfflinePaymentType::ToCart,
							enuOfflinePaymentType::ToAccountNumber,
							enuOfflinePaymentType::ToISBN,
						],
					],
				],
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
			],
			'ofpDestName' => [
				enuColumnInfo::type       => ['string', 'max' => 64],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => [
          'when' => function ($model) {
            return (in_array($model->ofpType, [
							enuOfflinePaymentType::Cash,
							enuOfflinePaymentType::ToAccountNumber,
							enuOfflinePaymentType::ToISBN,
							enuOfflinePaymentType::Cheque,
						]));
          },
					'conditions' => [
						'ofpType:checked' => [
							enuOfflinePaymentType::Cash,
							enuOfflinePaymentType::ToAccountNumber,
							enuOfflinePaymentType::ToISBN,
							enuOfflinePaymentType::Cheque,
						],
					],
				],
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
			],
			'ofpDueDate' => [
				enuColumnInfo::type       => 'safe',
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => [
          'when' => function ($model) {
            return ($model->ofpType == enuOfflinePaymentType::Cheque);
          },
					'conditions' => [
						'ofpType:checked' => enuOfflinePaymentType::Cheque,
					],
				],
				enuColumnInfo::selectable => true,
        // enuColumnInfo::search     => enuColumnSearchType::like,
			],
			'ofpTrackNumber' => [
				enuColumnInfo::type       => ['string', 'max' => 64],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => [
          'when' => function ($model) {
            return (in_array($model->ofpType, [
							enuOfflinePaymentType::Pos,
							enuOfflinePaymentType::ToCart,
							enuOfflinePaymentType::ToAccountNumber,
							enuOfflinePaymentType::ToISBN,
						]));
          },
					'conditions' => [
						'ofpType:checked' => [
							enuOfflinePaymentType::Pos,
							enuOfflinePaymentType::ToCart,
							enuOfflinePaymentType::ToAccountNumber,
							enuOfflinePaymentType::ToISBN,
						],
					],
				],
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
			],
			'ofpReferenceNumber' => [
				enuColumnInfo::type       => ['string', 'max' => 64],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => [
          'when' => function ($model) {
            return (in_array($model->ofpType, [
							enuOfflinePaymentType::Pos,
							enuOfflinePaymentType::ToCart,
							enuOfflinePaymentType::ToAccountNumber,
							enuOfflinePaymentType::ToISBN,
						]));
          },
					'conditions' => [
						'ofpType:checked' => [
							enuOfflinePaymentType::Pos,
							enuOfflinePaymentType::ToCart,
							enuOfflinePaymentType::ToAccountNumber,
							enuOfflinePaymentType::ToISBN,
						],
					],
				],
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
			],
			'ofpAmount' => [
				enuColumnInfo::type       => 'double',
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
				enuColumnInfo::search     => enuColumnSearchType::exact,
			],
			'ofpPayDate' => [
				enuColumnInfo::type       => 'safe',
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
        // enuColumnInfo::search     => enuColumnSearchType::like,
			],
			'ofpPayer' => [
				enuColumnInfo::type       => ['string', 'max' => 64],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
			],
			'ofpSourceCartNumber' => [
				enuColumnInfo::type       => ['string', 'max' => 20],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
			],
			'ofpImageFileID' => [
				enuColumnInfo::type       => 'safe', //'integer',
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false, //true,
				enuColumnInfo::selectable => true,
        // enuColumnInfo::search     => enuColumnSearchType::like,
			],
			'ofpWalletID' => [
				enuColumnInfo::type       => 'integer',
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false, //true,
				enuColumnInfo::selectable => true,
				enuColumnInfo::search     => enuColumnSearchType::exact,
			],
			'ofpComment' => [
				enuColumnInfo::type       => ['string', 'max' => 65530],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
			],
			'ofpRejectReasonIDs' => [
				enuColumnInfo::type       => JsonValidator::class,
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => null,
				enuColumnInfo::required   => false,
				enuColumnInfo::selectable => true,
        // enuColumnInfo::search     => enuColumnSearchType::like,
			],
			'ofpStatus' => [
				enuColumnInfo::isStatus   => true,
				enuColumnInfo::type       => ['string', 'max' => 1],
				enuColumnInfo::validator  => null,
				enuColumnInfo::default    => enuOfflinePaymentStatus::WaitForApprove,
				enuColumnInfo::required   => true,
				enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
			],

      'ofpCreatedAt' => ModelColumnHelper::CreatedAt(),
      'ofpCreatedBy' => ModelColumnHelper::CreatedBy(),
      'ofpUpdatedAt' => ModelColumnHelper::UpdatedAt(),
      'ofpUpdatedBy' => ModelColumnHelper::UpdatedBy(),
			'ofpRemovedAt' => ModelColumnHelper::RemovedAt(),
			'ofpRemovedBy' => ModelColumnHelper::RemovedBy(),
		];
	}

	// public function traitExtraRules()
  // {
  //   return [
  //     [[
	// 			'ofpTrackNumber',
	// 			'ofpReferenceNumber',
  //     ], GroupRequiredValidator::class,
  //       'min' => 1,
  //       'in' => [
	// 				'ofpTrackNumber',
	// 				'ofpReferenceNumber',
	// 			],
  //       'message' => Yii::t('aaa', 'one of TrackNumber or ReferenceNumber is required'),
  //     ],
  //   ];
  // }

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

	public function getDestBank() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\BasicDefinitionModel';
		else
			$className = '\shopack\aaa\frontend\common\models\BasicDefinitionModel';

		return $this->hasOne($className, ['bdfID' => 'ofpDestBankID']);
	}

}
