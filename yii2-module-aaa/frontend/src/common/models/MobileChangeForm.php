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
  // public $phase; //1:get mobile, 2:get code
  // public $aprid = null;
  public $mobile;

  public function rules()
  {
    return [
      // ['aprid', 'integer'],
      ['mobile',
        'required',
        // 'when' => function ($model) {
        //   return (empty($model->aprid));
        // },
      ],
    ];
  }

  public function attributeLabels()
	{
		return [
			'mobile' => Yii::t('aaa', 'Mobile'),
			// 'code' => Yii::t('aaa', 'Code'),
		];
	}

  /**
   * return:
   *  1: phase 1 passed and aprid created
   *  true: code approved
   */
  public function process()
  {
    if (Yii::$app->user->isGuest)
      throw new UnauthorizedHttpException("This process is not for guest.");

    if ($this->validate() == false)
      throw new UnauthorizedHttpException(implode("\n", $this->getFirstErrors()));

    // if (empty($this->aprid)) {
      list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/user/mobile-change',
        HttpHelper::METHOD_POST,
        [],
        [
          'mobile' => $this->mobile,
        ]
      );

      if ($resultStatus < 200 || $resultStatus >= 300)
        throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));

      // $this->aprid = intval($resultData['aprid']);

      return true; //[$resultStatus, $resultData['result']];
    // }






    // return false;
  }

}
