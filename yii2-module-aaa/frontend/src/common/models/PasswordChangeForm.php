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

class PasswordChangeForm extends Model
{
  public $hasPassword = null;
	public $curPassword;
	public $newPassword;
	public $retypePassword;

  public function rules()
  {
    return [
      [['curPassword', 'newPassword', 'retypePassword'], 'string'],

      ['curPassword',
        'required',
        'when' => function ($model) {
          return $model->hasPassword;
        }
      ],

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
      'curPassword'    => Yii::t('aaa', 'Current Password'),
      'newPassword'    => Yii::t('aaa', 'New Password'),
      'retypePassword' => Yii::t('aaa', 'Retype Password'),
		];
	}

  public function process()
  {
    if ($this->validate() == false)
      throw new UnauthorizedHttpException(implode("\n", $this->getFirstErrors()));

    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/auth/password-change',
      HttpHelper::METHOD_POST,
      [],
      [
        'curPassword' => $this->curPassword,
        'newPassword' => $this->newPassword,
      ]
    );

    if ($resultStatus < 200 || $resultStatus >= 300)
      throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));

    return true; //[$resultStatus, $resultData['result']];
  }

}
