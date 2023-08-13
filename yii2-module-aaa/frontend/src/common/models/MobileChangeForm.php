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

class MobileChangeForm extends Model
{
  public $phase; //1:get mobile, 2:get code
  public $mobile;

  public function rules()
  {
    return [
      ['mobile', 'required'],
    ];
  }

  public function attributeLabels()
	{
		return [
			'mobile' => Yii::t('aaa', 'Mobile'),
		];
	}

  public function process()
  {
    if (Yii::$app->user->isGuest)
      throw new UnauthorizedHttpException("This process is not for guest.");

    if ($this->validate() == false)
      throw new UnauthorizedHttpException(implode("\n", $this->getFirstErrors()));

    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/user/mobile-change',
      HttpHelper::METHOD_POST,
      [],
      [
        'mobile' => $this->mobile,
      ]
    );

    if ($resultStatus < 200 || $resultStatus >= 300)
      throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));

    return true; //[$resultStatus, $resultData['result']];
  }

}
