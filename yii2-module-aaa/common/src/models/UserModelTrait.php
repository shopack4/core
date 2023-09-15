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

/*
new: must be add to the model lables, index and view
      'usrFatherName',
      'usrFatherName_en',
      'usrBirthCertID',
      'usrBirthCityID',
      'usrPhones',
      'usrWorkAddress',
      'usrWorkPhones',
      'usrWebsite',
*/

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
  public $usrPassword;
  public $hasPassword = false;

  public static $primaryKey = ['usrID'];

	public function primaryKeyValue() {
		return $this->usrID;
	}

  public static function columnsInfo()
  {
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
      ],
      'usrFirstName_en' => [
        enuColumnInfo::type       => ['string', 'max' => 128],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
      ],
      'usrLastName' => [
        enuColumnInfo::type       => ['string', 'max' => 128],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
      ],
      'usrLastName_en' => [
        enuColumnInfo::type       => ['string', 'max' => 128],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
      ],
      'usrFatherName' => [
        enuColumnInfo::type       => ['string', 'max' => 128],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
      ],
      'usrFatherName_en' => [
        enuColumnInfo::type       => ['string', 'max' => 128],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
      ],
      'usrEmail' => [
        enuColumnInfo::type       => ['string', 'max' => 128],
        enuColumnInfo::validator  => 'email',
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
      ],
			'usrEmailApprovedAt' => [
        enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'usrMobile' => [
        enuColumnInfo::type       => ['string', 'max' => 32],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
      ],
      'usrMobileApprovedAt' => [
        enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'usrSSID' => [
        enuColumnInfo::type       => ['string', 'max' => 16],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
      ],
      'usrBirthCertID' => [
        enuColumnInfo::type       => ['string', 'max' => 16],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
      ],
      'usrRoleID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => enuRole::User,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'usrPrivs' => [
        enuColumnInfo::type       => JsonValidator::class,
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
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
      ],
      'usrPasswordHash' => [
        enuColumnInfo::type       => ['string', 'max' => 255],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => false,
      ],
      'usrPasswordCreatedAt' => [
        enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'usrMustChangePassword' => [
        enuColumnInfo::type       => 'boolean',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'usrBirthDate' => [
        enuColumnInfo::type       => 'safe',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'usrBirthCityID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'usrCountryID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'usrStateID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'usrCityOrVillageID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'usrTownID' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
      ],
      'usrHomeAddress' => [
        enuColumnInfo::type       => ['string', 'max' => 2048],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
      ],
      'usrZipCode' => [
        enuColumnInfo::type       => ['string', 'max' => 32],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
      ],
      'usrPhones' => [
        enuColumnInfo::type       => ['string', 'max' => 1024],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
      ],
      'usrWorkAddress' => [
        enuColumnInfo::type       => ['string', 'max' => 2048],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
      ],
      'usrWorkPhones' => [
        enuColumnInfo::type       => ['string', 'max' => 1024],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
      ],
      'usrWebsite' => [
        enuColumnInfo::type       => ['string', 'max' => 1024],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
      ],
      'usrImageFileID' => [
        enuColumnInfo::type       => 'safe', //'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => false, //true
      ],

      'usrEducationLevel' => [
        enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
      ],
      'usrFieldOfStudy' => [
        enuColumnInfo::type       => ['string', 'max' => 128],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::like,
      ],
      'usrYearOfGraduation' => [
        enuColumnInfo::type       => 'integer',
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
      ],
      'usrEducationPlace' => [
        enuColumnInfo::type       => ['string', 'max' => 128],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
      ],
      'usrMaritalStatus' => [
        enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
      ],
      'usrMilitaryStatus' => [
        enuColumnInfo::type       => ['string', 'max' => 1],
        enuColumnInfo::validator  => null,
        enuColumnInfo::default    => null,
        enuColumnInfo::required   => false,
        enuColumnInfo::selectable => true,
        enuColumnInfo::search     => enuColumnSearchType::exact,
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
        'message' => Yii::t('aaa', 'one of email or mobile is required'),
      ],
    ];
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

  public function getBirthCityOrVillage() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\GeoCityOrVillageModel';
		else
			$className = '\shopack\aaa\frontend\common\models\GeoCityOrVillageModel';

		return $this->hasOne($className, ['ctvID' => 'usrBirthCityID']);
	}

  public function getTown() {
		$className = get_called_class();

		if (str_contains($className, '\\backend\\'))
			$className = '\shopack\aaa\backend\models\GeoTownModel';
		else
			$className = '\shopack\aaa\frontend\common\models\GeoTownModel';

		return $this->hasOne($className, ['twnID' => 'usrTownID']);
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

  public function getActorName() {
    return $this->displayName('{id}- {em}');
  }

}
