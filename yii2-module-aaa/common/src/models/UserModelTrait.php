<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\common\models;

use Yii;
use shopack\base\common\rest\ModelColumnHelper;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\rest\enuColumnSearchType;
use shopack\base\common\validators\JsonValidator;
use shopack\base\common\validators\GroupRequiredValidator;
use shopack\aaa\common\enums\enuRole;
use shopack\base\backend\helpers\PrivHelper;
use shopack\base\common\helpers\GeneralHelper;

/*
'usrID',
'usrUUID',
'usrGender',
'usrFirstName',
'usrFirstName_en',
'usrLastName',
'usrLastName_en',
'usrFatherName',
'usrFatherName_en',
'usrEmail',
'usrEmailApprovedAt',
'usrMobile',
'usrMobileApprovedAt',
'usrSSID',
'usrBirthCertID',
'usrRoleID',
'usrPrivs',
'usrPassword',
'usrPasswordHash',
'usrPasswordCreatedAt',
'usrMustChangePassword',
'usr2FA',
'usrBirthDate',
'usrBirthCityID',
'usrCountryID',
'usrStateID',
'usrCityOrVillageID',
'usrTownID',
'usrHomeAddress',
'usrZipCode',
'usrPhones',
'usrWorkAddress',
'usrWorkPhones',
'usrWebsite',
'usrImageFileID',
'usrEducationLevel',
'usrFieldOfStudy',
'usrYearOfGraduation',
'usrEducationPlace',
'usrMaritalStatus',
'usrMilitaryStatus',
'usrStatus',
'usrCreatedAt',
'usrCreatedBy',
'usrUpdatedAt',
'usrUpdatedBy',
'usrRemovedAt',
'usrRemovedBy',
*/
trait UserModelTrait
{
  //input:
  public $usrPassword;

  //output:
  public $hasPassword = false;
	// public $sessionCount;
	// public $lastActivity;
  //just used for export to client
  public function adhocColumnsInfo()
  {
    return [
      'hasPassword' => ModelColumnHelper::adhoc(),
      // 'sessionCount' => ModelColumnHelper::adhoc(),
      // 'lastActivity' => ModelColumnHelper::adhoc(),
    ];
  }

  public static $primaryKey = ['usrID'];

	public function primaryKeyValue() {
		return $this->usrID;
	}

  public function columnsInfo()
  {
    $fnFilterByOwner = function($model, $fieldName, $isInRelation) {
      return (Yii::$app->user->isGuest
        || (($model->usrID != Yii::$app->user->id)
          && (PrivHelper::hasPriv('aaa/user/crud', '0100') == false)
        )
      );
    };

    return [
      'usrID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
      ],
      'usrUUID' => ModelColumnHelper::UUID(),
      'usrGender' => [
        enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'usrFirstName' => [
        enuColumnInfo::type       => ['string', 'max' => 128],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrFirstName_en' => [
        enuColumnInfo::type       => ['string', 'max' => 128],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrLastName' => [
        enuColumnInfo::type       => ['string', 'max' => 128],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrLastName_en' => [
        enuColumnInfo::type       => ['string', 'max' => 128],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrFatherName' => [
        enuColumnInfo::type       => ['string', 'max' => 128],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrFatherName_en' => [
        enuColumnInfo::type       => ['string', 'max' => 128],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrEmail' => [
        enuColumnInfo::type       => ['string', 'max' => 128],
        enuColumnInfo::validator  => 'email',
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
			'usrEmailApprovedAt' => [
        enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrMobile' => [
        enuColumnInfo::type       => ['string', 'max' => 32],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrMobileApprovedAt' => [
        enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrSSID' => [
        enuColumnInfo::type       => ['string', 'min' => 10, 'max' => 10],
        enuColumnInfo::validator  => 'validateSSID',
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrBirthCertID' => [
        enuColumnInfo::type       => ['string', 'max' => 16],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrRoleID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => enuRole::User,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrPrivs' => [
        enuColumnInfo::type       => JsonValidator::class,
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::filter     => function($model, $fieldName, $isInRelation)
        use (&$fnFilterByOwner) {
          return ($isInRelation || $fnFilterByOwner($model, $fieldName, $isInRelation));
        },
      ],
      // 'hasPassword' => [
      //   enuColumnInfo::type       => 'boolean',
      //   enuColumnInfo::validator  => null,
      //   enuColumnInfo::default    => null,
      //   enuColumnInfo::required   => false,
      //   enuColumnInfo::selectable => false,
      //   enuColumnInfo::virtual    => true,
      // ],
      'usrPassword' => [
        enuColumnInfo::type       => 'string',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => false,
        enuColumnInfo::virtual    => true,
        enuColumnInfo::filter     => true,
      ],
      'usrPasswordHash' => [
        enuColumnInfo::type       => ['string', 'max' => 255],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => false,
        enuColumnInfo::filter     => true,
      ],
      'usrPasswordCreatedAt' => [
        enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrMustChangePassword' => [
        enuColumnInfo::type       => 'boolean',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usr2FA' => [
        enuColumnInfo::type       => JsonValidator::class,
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrBirthDate' => [
        enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrBirthCityID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrCountryID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrStateID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrCityOrVillageID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrTownID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrHomeAddress' => [
        enuColumnInfo::type       => ['string', 'max' => 2048],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrZipCode' => [
        enuColumnInfo::type       => ['string', 'max' => 32],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrPhones' => [
        enuColumnInfo::type       => ['string', 'max' => 1024],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrWorkAddress' => [
        enuColumnInfo::type       => ['string', 'max' => 2048],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrWorkPhones' => [
        enuColumnInfo::type       => ['string', 'max' => 1024],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrWebsite' => [
        enuColumnInfo::type       => ['string', 'max' => 1024],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrImageFileID' => [
        enuColumnInfo::type       => 'safe', //'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => false, //true
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],

      'usrEducationLevel' => [
        enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrFieldOfStudy' => [
        enuColumnInfo::type       => ['string', 'max' => 128],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrYearOfGraduation' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrEducationPlace' => [
        enuColumnInfo::type       => ['string', 'max' => 128],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrMaritalStatus' => [
        enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],
      'usrMilitaryStatus' => [
        enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
        enuColumnInfo::filter     => $fnFilterByOwner,
      ],

      'usrStatus' => [
        enuColumnInfo::isStatus   => true,
        enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
      ],

      'usrCreatedAt' => ModelColumnHelper::CreatedAt(),
      'usrCreatedBy' => ModelColumnHelper::CreatedBy(),
      'usrUpdatedAt' => ModelColumnHelper::UpdatedAt(),
      'usrUpdatedBy' => ModelColumnHelper::UpdatedBy(),
      'usrRemovedAt' => ModelColumnHelper::RemovedAt(),
      'usrRemovedBy' => ModelColumnHelper::RemovedBy(),
    ];
  }

  public function traitExtraRules()
  {
    return [
      [[
        'usrEmail',
        'usrMobile'
      ], GroupRequiredValidator::class,
        'min' => 1,
        'in' => [
          'usrEmail',
          'usrMobile'
        ],
        'message' => Yii::t('aaa', 'One of the email or mobile is required'),
      ],
    ];
  }

  public function validateSSID($attribute, $params)
  {
    if ((empty($this[$attribute]) == false) && (GeneralHelper::isValidIranSSID($this[$attribute]) == false)) {
      $this->addError($attribute, Yii::t('aaa', 'Invalid SSID'));
    }
  }

  public function getCreatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'usrCreatedBy']);
	}

	public function getUpdatedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'usrUpdatedBy']);
	}

	public function getRemovedByUser() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UserModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UserModel';

		return $this->hasOne($className, ['usrID' => 'usrRemovedBy']);
	}

	public function getCountry() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\GeoCountryModel';
		else
			$className = '\shopack\aaa\frontend\common\models\GeoCountryModel';

		return $this->hasOne($className, ['cntrID' => 'usrCountryID']);
	}

  public function getState() {
    $className = get_called_class();

    if (str_contains($className, '\\backend\\'))
      $className = '\shopack\aaa\backend\models\GeoStateModel';
    else
      $className = '\shopack\aaa\frontend\common\models\GeoStateModel';

    return $this->hasOne($className, ['sttID' => 'usrStateID']);
  }

  public function getCityOrVillage() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\GeoCityOrVillageModel';
		else
			$className = '\shopack\aaa\frontend\common\models\GeoCityOrVillageModel';

		return $this->hasOne($className, ['ctvID' => 'usrCityOrVillageID']);
	}

  public function getTown() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\GeoTownModel';
		else
			$className = '\shopack\aaa\frontend\common\models\GeoTownModel';

		return $this->hasOne($className, ['twnID' => 'usrTownID']);
	}

  public function getBirthCityOrVillage() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\GeoCityOrVillageModel';
		else
			$className = '\shopack\aaa\frontend\common\models\GeoCityOrVillageModel';

		return $this->hasOne($className, ['ctvID' => 'usrBirthCityID']);
	}

  public function getRole() {
		$className = get_called_class();

    if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\RoleModel';
		else
			$className = '\shopack\aaa\frontend\common\models\RoleModel';

    return $this->hasOne($className, ['rolID' => 'usrRoleID']);
  }

  public function getImageFile() {
		$className = get_called_class();

    if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\UploadFileModel';
		else
			$className = '\shopack\aaa\frontend\common\models\UploadFileModel';

    return $this->hasOne($className, ['uflID' => 'usrImageFileID']);
  }

  //moved to frontend
  /*
  public function displayName($format = null)
  {
    if (empty($format))
      $format = '[' . Yii::t('app', 'ID') . ': {id}] {fn} {ln} {em} {mob}';

    if ($this->usrEmail)
      $email = "<span class='d-inline-block dir-ltr'>" . $this->usrEmail . "</span>";

    if ($this->usrMobile)
      $mobile = Yii::$app->formatter->asPhone($this->usrMobile);
      // $mobile = "<span class='d-inline-block dir-ltr'>" . $this->usrMobile . "</span>";

    return str_replace('  ', ' ', strtr($format, [
      '{id}' => $this->usrID,
      '{fn}' => $this->usrFirstName ?? '',
      '{ln}' => $this->usrLastName ?? '',
      '{em}' => $email ?? '',
      '{mob}' => $mobile ?? '',
    ]));
	}
  */

  public function getActorName() {
    return $this->displayName('{id}- {em}');
  }

}
