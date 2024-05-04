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

class Active2FAForm extends Model
{
  public $type;
  public $code;
  public $userModel;

  public function rules()
  {
    return [
      ['type', 'required'],
      ['code', 'required'],
    ];
  }

  public function attributeLabels()
	{
		return [
			'code' => Yii::t('aaa', 'Code'),
		];
	}

  public function process()
  {
    if (Yii::$app->user->isGuest)
      throw new UnauthorizedHttpException("This process is not for guest.");

    if ($this->validate() == false)
      throw new UnauthorizedHttpException(implode("\n", $this->getFirstErrors()));

    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/user/active-2fa',
      HttpHelper::METHOD_POST,
      [],
      [
        'type' => $this->type,
        'code' => $this->code,
      ]
    );

    if ($resultStatus < 200 || $resultStatus >= 300)
      throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));

    return true; //[$resultStatus, $resultData['result']];
  }

}
