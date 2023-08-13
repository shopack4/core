<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use yii\base\Model;
use yii\web\UnauthorizedHttpException;
use yii\web\UnprocessableEntityHttpException;
use shopack\base\common\helpers\HttpHelper;

class PasswordSetForm extends Model
{
	public $newPassword;
	public $retypePassword;

  public function rules()
  {
    return [
      [['newPassword', 'retypePassword'], 'string'],
      [['newPassword', 'retypePassword'], 'required'],

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
      throw new UnauthorizedHttpException(implode("\n", $this->getFirstErrors()));

    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/auth/password-set',
      HttpHelper::METHOD_POST,
      [],
      [
        'newPassword' => $this->newPassword,
      ]
    );

    if ($resultStatus < 200 || $resultStatus >= 300)
      throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));

    return true; //[$resultStatus, $resultData['result']];
  }

}
