<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\userpanel\models;

use Yii;
use yii\base\Model;
use yii\web\HttpException;
use shopack\base\common\helpers\HttpHelper;
use shopack\aaa\frontend\common\models\VoucherModel;

class OrderChangeDeliveryMethodForm extends Model
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

	private $_voucherModel = null;
	public function getVoucher()
	{
		if ($this->_voucherModel == null)
			$this->_voucherModel = VoucherModel::find()->andWhere(['vchID' => $this->vchID])->one();

		return $this->_voucherModel;
	}

	public function load($data, $formName = null)
	{
		if (parent::load($data, $formName) == false) {
      $this->deliveryMethod = $this->voucher->vchDeliveryMethodID;

			return false;
		}

		return true;
	}

	public function process()
	{
    if ($this->validate() == false)
      throw new HttpException(400, implode("\n", $this->getFirstErrors()));

    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/voucher/order-change-delivery-method',
      HttpHelper::METHOD_POST,
      [
				'id' => $this->vchID,
			],
      [
				'deliveryMethod'	=> $this->deliveryMethod,
			]
    );

    if ($resultStatus < 200 || $resultStatus >= 300)
      throw new HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));

    return true; //[$resultStatus, $resultData['result']];
	}

}
