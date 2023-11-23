<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\models;

use shopack\base\common\rest\ModelColumnHelper;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\rest\enuColumnSearchType;
use shopack\base\common\validators\JsonValidator;
use shopack\aaa\common\enums\enuMessageStatus;

/*
'msgID',
'msgUUID',
'msgUserID',
'msgApprovalRequestID',
'msgForgotPasswordRequestID',
'msgTypeKey',
'msgTarget',
'msgInfo', :JSON
'msgIssuer',
'msgLockedAt',
'msgLockedBy',
'msgLastTryAt',
'msgSentAt',
'msgResult', :JSON
'msgStatus',
'msgCreatedAt',
'msgCreatedBy',
'msgUpdatedAt',
'msgUpdatedBy',
'msgRemovedAt',
'msgRemovedBy',
*/
trait MessageModelTrait
{
  public static $primaryKey = ['msgID'];

	public function primaryKeyValue() {
		return $this->msgID;
	}

	public function columnsInfo()
  {
    return [
			'msgID' => [
				enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
			],
      'msgUUID' => ModelColumnHelper::UUID(),
			'msgUserID' => [
				enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
			],
			'msgApprovalRequestID' => [
				enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
			],
			'msgForgotPasswordRequestID' => [
				enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
			],
			'msgTypeKey' => [
				enuColumnInfo::type       => ['string', 'max' => 64],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
			],
			'msgTarget' => [
				enuColumnInfo::type       => ['string', 'max' => 255],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
			],
			'msgInfo' => [
				enuColumnInfo::type       => JsonValidator::class,
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
			],
      'msgIssuer' => [
				enuColumnInfo::type       => ['string', 'max' => 64],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
			],
			'msgLockedAt' => [
				enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
			],
			'msgLockedBy' => [
				enuColumnInfo::type       => ['string', 'max' => 64],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
			],
			'msgLastTryAt' => [
				enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
			],
			'msgSentAt' => [
				enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
			],
			'msgResult' => [
				enuColumnInfo::type       => JsonValidator::class,
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
			],
			'msgStatus' => [
        enuColumnInfo::isStatus   => true,
				enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => enuMessageStatus::New,
        enuColumnInfo::required   => true,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
			],

      'msgCreatedAt' => ModelColumnHelper::CreatedAt(),
      'msgCreatedBy' => ModelColumnHelper::CreatedBy(),
      'msgUpdatedAt' => ModelColumnHelper::UpdatedAt(),
      'msgUpdatedBy' => ModelColumnHelper::UpdatedBy(),
      'msgRemovedAt' => ModelColumnHelper::RemovedAt(),
      'msgRemovedBy' => ModelColumnHelper::RemovedBy(),
		];
  }

  public function getCreatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'msgCreatedBy']);
	}

  public function getUpdatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'msgUpdatedBy']);
	}

	public function getRemovedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'msgRemovedBy']);
	}

  public function getUser() {
    $className = get_called_class();

    if (str_contains($className, '\\backend\\'))
      $className = '\shopack\aaa\backend\models\UserModel';
    else
      $className = '\shopack\aaa\frontend\common\models\UserModel';

    return $this->hasOne($className, ['usrID' => 'msgUserID']);
  }

  public function getApprovalRequest() {
    $className = get_called_class();

    if (str_contains($className, '\\backend\\'))
      $className = '\shopack\aaa\backend\models\ApprovalRequestModel';
    else
      $className = '\shopack\aaa\frontend\common\models\ApprovalRequestModel';

    return $this->hasOne($className, ['aprID' => 'msgApprovalRequestID']);
  }

  public function getForgotPasswordRequest() {
    $className = get_called_class();

    if (str_contains($className, '\\backend\\'))
      $className = '\shopack\aaa\backend\models\ForgotPasswordRequestModel';
    else
      $className = '\shopack\aaa\frontend\common\models\ForgotPasswordRequestModel';

    return $this->hasOne($className, ['fprID' => 'msgForgotPasswordRequestID']);
  }

  public function getMessageTemplate() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\MessageTemplateModel';
		else
			$className = '\shopack\aaa\frontend\common\models\MessageTemplateModel';

		return $this->hasOne($className, ['mstKey' => 'msgTypeKey']);
	}

}
