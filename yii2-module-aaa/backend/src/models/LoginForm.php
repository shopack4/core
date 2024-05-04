<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\models;

use yii\base\Model;
use shopack\base\common\validators\GroupRequiredValidator;
use shopack\base\backend\helpers\AuthHelper;
use yii\web\UnprocessableEntityHttpException;
use yii\web\UnauthorizedHttpException;
use shopack\aaa\common\enums\enuUserStatus;
use shopack\base\common\helpers\GeneralHelper;

class LoginForm extends Model
{
  public $input;

  public $email;
  public $mobile;
  public $ssid;
  public $password;
  // public $salt;
  public $rememberMe = false;

	private $_inputName = '';
  private $_user = false;

  public function rules()
  {
    return [
      ['input', 'required'],

      [[
        'email',
        'mobile',
        'ssid',
      ], 'string'],

      [[
        'email',
        'mobile',
        'ssid',
      ], GroupRequiredValidator::class,
        'min' => 1,
        'in' => [
          'email',
          'mobile',
          'ssid',
          ],
        'message' => 'one of email or mobile or ssid is required',
      ],

      ['password', 'required'],
      // ['password', 'validatePassword'],

      // ['salt', 'required'],

      ['rememberMe', 'boolean'],
    ];
  }

  // public function validatePassword($attribute, $params)
  // {
  //   if (!$this->hasErrors()) {
  //     $user = $this->getUser();

  //     if (!$user)
  //       $this->addError($attribute, "Incorrect {$this->inputName} or password.");
  //     else if (!$user->validatePassword($this->password)) //, $this->salt))
  //       $this->addError($attribute, "Incorrect {$this->inputName} or Password.");
  //   }
  // }

  public function getUser()
  {
    if ($this->_user === false) {
      $query = UserModel::find()
        ->addSelect('usrPasswordHash')
        ->andWhere('usrStatus != \'' . enuUserStatus::Removed . '\'')
        // ->with('role')
        ;

      if (empty($this->email) == false)
        $query->andWhere(['usrEmail' => $this->email]);
      else if (empty($this->mobile) == false)
        $query->andWhere(['usrMobile' => $this->mobile]);
      else if (empty($this->ssid) == false)
        $query->andWhere(['usrSSID' => $this->ssid]);

      $this->_user = $query->one();
    }

    return $this->_user;
  }

  public function getInputName()
  {
    return $this->_inputName;
  }

  public function login()
  {
    if ($this->validate('input') == false)
      throw new UnauthorizedHttpException(implode("\n", $this->getFirstErrors()));

    list ($normalizedInput, $inputType) = GeneralHelper::recognizeLoginPhrase($this->input);

    if ($inputType == GeneralHelper::PHRASETYPE_EMAIL) {
      $this->_inputName = 'email';
      $this->email = $normalizedInput;
    } else if ($inputType == GeneralHelper::PHRASETYPE_MOBILE) {
      $this->_inputName = 'mobile';
      $this->mobile = $normalizedInput;
    } else if ($inputType == GeneralHelper::PHRASETYPE_SSID) {
      $this->_inputName = 'ssid';
      $this->ssid = $normalizedInput;
    } else
      throw new UnprocessableEntityHttpException('Invalid input');

    if ($this->validate()) {
      $user = $this->getUser();

      if (!$user) {
        $this->addError('', "Incorrect {$this->inputName} or password.");

        throw new UnauthorizedHttpException("could not login. \n" . implode("\n", $this->getFirstErrors()));
      }

      if ($user->validatePassword($this->password) == false) {
        $this->addError('', "Incorrect {$this->inputName} or Password.");

        throw new UnauthorizedHttpException("could not login. \n" . implode("\n", $this->getFirstErrors()));
      }

      list ($token, $mustApprove, $sessionModel, $challenge) = AuthHelper::doLogin($user, $this->rememberMe, $inputType);

      return [
        'token' => $token,
        'mustApprove' => $mustApprove,
				'challenge' => $challenge,
      ];
    }

    throw new UnauthorizedHttpException("could not login. \n" . implode("\n", $this->getFirstErrors()));
  }

}
