<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\userpanel\models;

use Yii;
use yii\base\Model;
use yii\web\HttpException;
use shopack\base\common\helpers\HttpHelper;

class ChangeOrderDeliveryMethodForm extends Model
{
	public $vchID;
	public $deliveryMethod;

	public function rules()
	{
		return [
			[[
				'vchID',
				'deliveryMethod',
			], 'required'],
		];
	}

  public function attributeLabels()
	{
		return [
			'deliveryMethod' => Yii::t('aaa', 'Delivery Method'),
		];
	}

	public function process()
	{
    if ($this->validate() == false)
      throw new HttpException(400, implode("\n", $this->getFirstErrors()));

    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/voucher/change-order-delivery-method',
      HttpHelper::METHOD_POST,
      [],
      [
				'vchID'						=> $this->vchID,
				'deliveryMethod'	=> $this->deliveryMethod,
			]
    );

    if ($resultStatus < 200 || $resultStatus >= 300)
      throw new HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));

    return true; //[$resultStatus, $resultData['result']];
	}

}
