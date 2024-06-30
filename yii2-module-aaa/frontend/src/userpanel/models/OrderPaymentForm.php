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
use shopack\base\common\helpers\Url;

class OrderPaymentForm extends Model
{
	public $vchID;
	public $walletID;
  public $gatewayType;

	public function rules()
	{
		return [
			['vchID', 'required'],
			['walletID', 'safe'],
      ['gatewayType', 'safe'],
		];
	}

  public function attributeLabels()
	{
		return [
			'gatewayType'	=> Yii::t('aaa', 'Payment Method'),
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
      // $this->deliveryMethod = $this->voucher->vchDeliveryMethodID;

			return false;
		}

		return true;
	}

	public function process()
	{
    if ($this->validate() == false)
      throw new HttpException(400, implode("\n", $this->getFirstErrors()));

		$callbackUrl = Url::to(['/aaa/order/view', 'id' => $this->vchID, 'checkpaid' => 1], true);

    list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/voucher/order-payment',
      HttpHelper::METHOD_POST,
      [
				'id' => $this->vchID,
			],
      [
				'walletID'		=> $this->walletID[0] ?? null,
				'gatewayType'	=> $this->gatewayType,
				'callbackUrl'	=> $callbackUrl,
			]
    );

    if ($resultStatus < 200 || $resultStatus >= 300)
      throw new HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));

    return $resultData;
	}

}
