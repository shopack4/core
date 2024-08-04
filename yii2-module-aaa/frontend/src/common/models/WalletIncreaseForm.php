<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use yii\base\Model;
use yii\web\ForbiddenHttpException;
use yii\web\UnprocessableEntityHttpException;
use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\HttpHelper;

class WalletIncreaseForm extends Model
{
	public $walletID;
  public $amount;
  public $gatewayType;

  public function rules()
  {
    return [
      ['amount', 'integer'],

			['walletID', 'required'],
      ['amount', 'required'],
      ['gatewayType', 'required'],
    ];
  }

  public function attributeLabels()
	{
		return [
			'amount'			=> Yii::t('aaa', 'Amount'),
			'gatewayType'	=> Yii::t('aaa', 'Payment Method'),
		];
	}

  public function process()
  {
    if (Yii::$app->user->isGuest)
      throw new ForbiddenHttpException("This process is not for guest.");

    if ($this->validate() == false)
      throw new UnprocessableEntityHttpException(implode("\n", $this->getFirstErrors()));

    //--
    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/wallet/increase',
      HttpHelper::METHOD_POST,
      [
        'id' => $this->walletID,
			],
			[
				'amount' => $this->amount,
				'gatewayType' => $this->gatewayType,
				'callbackUrl' => Url::to(['wallet/increase-done'], true),
			]
    );

    HttpHelper::throwResultIfFailed('aaa', $resultStatus, $resultData);

    return $resultData;
  }

}
