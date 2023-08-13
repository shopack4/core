<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\userpanel\models;

use Yii;
use yii\base\Model;
use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\HttpHelper;

class BasketCheckoutForm extends Model //RestClientActiveRecord
{
	const STEP_PAYMENT = 'payment';

	// use \shopack\aaa\common\models\BasketModelTrait;

	// public static $resourceName = 'aaa/basket';
  // public static $primaryKey = ['vchID'];

	public $voucher;
	public $totalPrices = 0;
	public $totalDiscounts = 0;
	public $totalTaxes = 0;
	public $vchtotal = 0;
	public $paid = 0;
	public $total = 0;

	public $currentStep;
	public $walletID;
	public $gatewayType;

	public function rules()
	{
		return [
			[[
				'currentStep',
				'walletID',
				'gatewayType',
			], 'string'],

			[[
				'currentStep',
			], 'required'],

			[[
				'gatewayType',
			],
				'required',
				'when' => function ($model) {
					return ($model->total > 0);
				},
				'whenClient' => "function (attribute, value) {
					return (remainAfterWallet > 0);
				}"
			],
		];

	}

	public function attributeLabels()
	{
		return [
			'walletID'		=> Yii::t('aaa', 'Wallet'),
			'gatewayType'	=> Yii::t('aaa', 'Payment Method'),
		];
	}

	public function setVoucher($voucherModel)
	{
		$this->voucher = $voucherModel;

		if ($voucherModel == null)
			return;

		$vchItems = $voucherModel->vchItems;

		foreach ($vchItems as $item) {
			$this->totalPrices += $item['unitprice'] * $item['qty'];
			// $this->total += $this->totalPrices;
		}

		$this->vchtotal	= $voucherModel->vchAmount;
		$this->paid			= $voucherModel->vchTotalPaid;
		$this->total		= $voucherModel->vchAmount - ($voucherModel->vchTotalPaid ?? 0);
	}

	public function checkout()
	{
		list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/basket/checkout',
			HttpHelper::METHOD_POST,
			[],
			[
				'walletID' => $this->walletID,
				'gatewayType' => $this->gatewayType,
				'callbackUrl' => Url::to(['basket/checkout-done'], true),
			]
		);

		if ($resultStatus < 200 || $resultStatus >= 300)
			throw new \yii\web\HttpException($resultStatus, Yii::t('mha', $resultData['message'], $resultData));

		return $resultData;
	}

}
