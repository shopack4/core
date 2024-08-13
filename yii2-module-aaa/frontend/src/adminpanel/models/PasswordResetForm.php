<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\models;

use Yii;
use yii\base\Model;
use yii\web\UnprocessableEntityHttpException;
use shopack\base\common\helpers\HttpHelper;

class PasswordResetForm extends Model
{
	public $userID;
	public $newPassword;
	public $retypePassword;

  public function rules()
  {
    return [
      ['userID', 'integer'],
      [['newPassword', 'retypePassword'], 'string'],
      [['userID', 'newPassword', 'retypePassword'], 'required'],

      ['retypePassword', 'compare',
        'compareAttribute' => 'newPassword',
        'message' => Yii::t('aaa', "Passwords don't match"),
      ],
    ];
  }

  public function attributeLabels()
	{
		return [
      'newPassword'    => Yii::t('aaa', 'New Password'),
      'retypePassword' => Yii::t('aaa', 'Retype Password'),
		];
	}

  public function process()
  {
    if ($this->validate() == false)
      throw new UnprocessableEntityHttpException(implode("\n", $this->getFirstErrors()));

    $apiResponse = HttpHelper::callApi('aaa/user/password-reset',
      HttpHelper::METHOD_POST,
      [
        'id' => $this->userID,
      ],
      [
        'newPassword' => $this->newPassword,
      ]
    );

    HttpHelper::throwApiResponseIfFailed($apiResponse, 'aaa');

    return true;
  }

}
