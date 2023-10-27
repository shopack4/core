<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use shopack\base\common\helpers\Json;
use shopack\base\frontend\common\rest\RestClientActiveRecord;
use shopack\aaa\common\enums\enuUserStatus;

class UserModel extends RestClientActiveRecord
  implements \yii\web\IdentityInterface
{
  use \shopack\aaa\common\models\UserModelTrait;

  public static $resourceName = 'aaa/user';

  // public $usrID;
  public $username;
  // public $password;
  // public $firstName;
  // public $lastName;
  // public $usrEmail;
  // public $usrMobile;
  // public $authKey;
  public $accessToken;
  public $jwtPayload;

	public $usrRetypePassword;

  public function extraRules()
  {
    return [
      ['usrRetypePassword', 'string'],
      ['usrRetypePassword', 'compare',
        'compareAttribute' => 'usrPassword',
        'message' => Yii::t('aaa', "Passwords don't match"),
      ],
    ];
  }

  public function attributeLabels()
	{
		return [
      'usrID'                 => Yii::t('app', 'ID'),
      'usrGender'             => Yii::t('aaa', 'Gender'),
      'usrFirstName'          => Yii::t('aaa', 'First Name'),
      'usrFirstName_en'       => Yii::t('aaa', 'First Name (en)'),
      'usrLastName'           => Yii::t('aaa', 'Last Name'),
      'usrLastName_en'        => Yii::t('aaa', 'Last Name (en)'),
      'usrFatherName'         => Yii::t('aaa', 'Father Name'),
      'usrFatherName_en'      => Yii::t('aaa', 'Father Name (en)'),
      'usrEmail'              => Yii::t('aaa', 'Email'),
      'usrEmailApprovedAt'    => Yii::t('aaa', 'Email Approved At'),
      'usrMobile'             => Yii::t('aaa', 'Mobile'),
      'usrMobileApprovedAt'   => Yii::t('aaa', 'Mobile Approved At'),
      'usrSSID'               => Yii::t('aaa', 'SSID'),
      'usrRoleID'             => Yii::t('aaa', 'Role'),
      'usrPrivs'              => Yii::t('aaa', 'Exclusive Privs'),
      'usrPassword'           => Yii::t('aaa', 'Password'),
      'usrRetypePassword'     => Yii::t('aaa', 'Retype Password'),
      'usrPasswordHash'       => Yii::t('aaa', 'Password Hash'),
      'usrPasswordCreatedAt'  => Yii::t('aaa', 'Password Created At'),
      'usrMustChangePassword' => Yii::t('aaa', 'Must Change Password'),
			'usrBirthDate'          => Yii::t('aaa', 'Birth Date'),
      'usrBirthCityID'        => Yii::t('aaa', 'Birth Location'),
			'usrCountryID'          => Yii::t('aaa', 'Country'),
			'usrStateID'            => Yii::t('aaa', 'State'),
			'usrCityOrVillageID'    => Yii::t('aaa', 'City Or Village'),
			'usrTownID'             => Yii::t('aaa', 'Town'),
			'usrHomeAddress'        => Yii::t('aaa', 'Home Address'),
			'usrZipCode'            => Yii::t('aaa', 'Zip Code'),
			'usrImage'              => Yii::t('aaa', 'Image'),
			'usrImageFileID'        => Yii::t('aaa', 'Image'),

      'usrEducationLevel'     => Yii::t('aaa', 'Education Level'),
      'usrFieldOfStudy'       => Yii::t('aaa', 'Field Of Study'),
      'usrYearOfGraduation'   => Yii::t('aaa', 'Year Of Graduation'),
      'usrEducationPlace'     => Yii::t('aaa', 'Education Place'),
      'usrMaritalStatus'      => Yii::t('aaa', 'Marital Status'),
      'usrMilitaryStatus'     => Yii::t('aaa', 'Military Status'),

      'usrStatus'             => Yii::t('app', 'Status'),
      'usrCreatedAt'          => Yii::t('app', 'Created At'),
      'usrCreatedBy'          => Yii::t('app', 'Created By'),
      'usrCreatedBy_User'     => Yii::t('app', 'Created By'),
      'usrUpdatedAt'          => Yii::t('app', 'Updated At'),
      'usrUpdatedBy'          => Yii::t('app', 'Updated By'),
      'usrUpdatedBy_User'     => Yii::t('app', 'Updated By'),
      'usrRemovedAt'          => Yii::t('app', 'Removed At'),
      'usrRemovedBy'          => Yii::t('app', 'Removed By'),
      'usrRemovedBy_User'     => Yii::t('app', 'Removed By'),

      'hasPassword'           => Yii::t('aaa', 'Has Password'),
    ];
	}

 	public function isSoftDeleted()
  {
    return ($this->usrStatus == enuUserStatus::Removed);
  }

  public static function findIdentity($id)
  {
    $authCookie = Yii::$app->user->getJwtByCookie();
    if ($authCookie !== null) {
      $user = self::findIdentityByAccessToken($authCookie);
      if ($user == null)
        return null; //throw new \yii\web\ForbiddenHttpException('Invalid token');

      if ($user->usrID == $id)
        return $user;
    }

    return null;
    // $user = new self;
    // $user->id = $id;
    // return $user;
  }

  public static function findIdentityByAccessToken($token, $type = null)
  {
    // $parts = explode('.', $token);
    // if (count($parts) != 3)
    //   return null;
    // $jwtPayload = base64_decode($parts[1]);
    // $jwtPayload = Json::decode($jwtPayload);

    $parsedToken = Yii::$app->jwt->parser->parse($token);
    $jwtPayload = $parsedToken->claims()->all();

    //validate
    if (Yii::$app->user->validateJwtPayload($jwtPayload) == false)
      return null;

    $user = new self;
    // $user->authKey = $token;
    $user->accessToken = $token;
    $user->jwtPayload = $jwtPayload;

    $user->usrID = $user->jwtPayload['uid'];
    $user->usrEmail = $user->jwtPayload['email'] ?? null;
    $user->usrMobile = $user->jwtPayload['mobile'] ?? null;

    $user->username = $user->jwtPayload['username'] ?? null;
    $user->usrFirstName = $user->jwtPayload['firstName'] ?? null;
    $user->usrLastName = $user->jwtPayload['lastName'] ?? null;

    return $user;
  }

  /**
   * Finds user by username
   *
   * @param string $username
   * @return static|null
   */
  public static function findByUsername($username)
  {
    foreach (self::$users as $user) {
      if (strcasecmp($user['username'], $username) === 0) {
        return new static($user);
      }
    }

    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function getId()
  {
    return $this->usrID;
  }

  /**
   * {@inheritdoc}
   */
  public function getAuthKey()
  {
    return $this->accessToken; //$this->authKey;
  }

  /**
   * {@inheritdoc}
   */
  public function validateAuthKey($authKey)
  {
    return true; //$this->authKey === $authKey;
  }

  /**
   * Validates password
   *
   * @param string $password password to validate
   * @return bool if password provided is valid for current user
   */
  public function validatePassword($password)
  {
    return $this->usrPassword === $password;
  }

  public function getPublicIdentity()
  {
    if ($this->usrEmail) {
      return $this->usrEmail;
    }

    return $this->usrMobile;
  }

  public static function canCreate() {
		return true;
	}

	public function canUpdate() {
		return ($this->usrStatus != enuUserStatus::Removed);
	}

	public function canDelete() {
		return ($this->usrStatus != enuUserStatus::Removed);
	}

	public function canUndelete() {
		return ($this->usrStatus == enuUserStatus::Removed);
	}

}
