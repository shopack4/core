<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\models;

use Yii;
use yii\base\Model;
use yii\web\UnauthorizedHttpException;
use yii\web\UnprocessableEntityHttpException;
use shopack\base\common\helpers\HttpHelper;

class OfflinePaymentRejectForm extends Model
{
	public $ofpID;
	public $ofpRejectReasonIDs;

  public function rules()
  {
    return [
      ['ofpID', 'integer'],
      ['ofpID', 'required'],

      ['ofpRejectReasonIDs', 'safe'],
    ];
  }

  public function attributeLabels()
	{
		return [
      'ofpRejectReasonIDs' => Yii::t('app', 'Reasons'),
		];
	}

  public function process()
  {
    if ($this->validate() == false)
      throw new UnauthorizedHttpException(implode("\n", $this->getFirstErrors()));

    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/offline-payment/reject',
      HttpHelper::METHOD_POST,
      [
        'id' => $this->ofpID,
      ],
      [
        'reasons' => json_encode($this->ofpRejectReasonIDs ?? []),
      ]
    );

    HttpHelper::throwResultIfFailed('aaa', $resultStatus, $resultData);

    return true; //[$resultStatus, $resultData['result']];
  }

}
