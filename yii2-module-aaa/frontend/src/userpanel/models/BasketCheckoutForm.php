<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\userpanel\models;

use Yii;
use yii\base\Model;
use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\frontend\common\helpers\Html;
use shopack\aaa\frontend\common\models\VoucherModel;
use shopack\aaa\common\enums\enuVoucherType;
use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\base\common\accounting\enums\enuProductType;
use shopack\aaa\frontend\common\models\DeliveryMethodModel;
use shopack\aaa\frontend\common\models\WalletModel;

class BasketCheckoutForm extends Model //RestClientActiveRecord
{
	const STEP_DELIVERY = 'delivery';
	const STEP_PAYMENT = 'payment';
	const STEP_FIN = 'fin';

	const DELIVERY_ReceiveByCustomer = 'C';

	// use \shopack\aaa\common\models\BasketModelTrait;

	// public static $resourceName = 'aaa/basket';

	public $voucher;
	public $totalPrices = 0;
	public $totalDiscounts = 0;
	public $totalTaxes = 0;
	public $vchtotal = 0;

	public $physicalCount = 0;
	public $deliveryMethod = null;
	public $deliveryAmount = 0;

	public $paid = 0;
	public $total = 0;

	public $currentStep;
	public $walletID = null;
	public $gatewayType;

	public $steps;

	public function rules()
	{
    $fnGetConst = function($value) { return $value; };
    $fnGetFieldId = function($field) { return Html::getInputId($this, $field); };

		return [
			[[
				'currentStep',
				'deliveryMethod',
				'walletID',
				'gatewayType',
			], 'string'],

			[[
				'currentStep',
			], 'required'],

			['deliveryMethod',
				'required',
				'when' => function ($model) {
					return ($model->currentStep == self::STEP_DELIVERY);
				},
				'whenClient' => "function (attribute, value) {
					return ($('#{$fnGetFieldId('currentStep')}').val() == '{$fnGetConst(self::STEP_DELIVERY)}');
				}"
			],

			['gatewayType',
				'required',
				'when' => function ($model) {
					return ((empty($this->walletID)) && ($model->currentStep == self::STEP_PAYMENT));
				},
				'whenClient' => "function (attribute, value) {
					return (($('#{$fnGetFieldId('walletID')}').val() == '') && ($('#{$fnGetFieldId('currentStep')}').val() == '{$fnGetConst(self::STEP_PAYMENT)}'));
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

	public function __construct()
	{
		$this->setCurrentVoucher();
	}

	private function setCurrentVoucher()
	{
    $voucherModel = VoucherModel::find()
      ->andWhere(['vchOwnerUserID' => Yii::$app->user->id])
      ->andWhere(['vchType' => enuVoucherType::Basket])
      ->andWhere(['vchStatus' => enuVoucherStatus::New])
      ->andWhere(['vchRemovedAt' => 0])
      ->all();
		$voucherModel = ($voucherModel[0] ?? null);

		$this->voucher = $voucherModel;

		if ($voucherModel == null)
			return;

		$this->physicalCount = 0;

		$vchItems = $voucherModel->vchItems;

		foreach ($vchItems as $item) {
			$this->totalPrices += $item['unitprice'] * $item['qty'];
			// $this->total += $this->totalPrices;

			if (isset($item['prdtype']) && ($item['prdtype'] == enuProductType::Physical)) {
				++$this->physicalCount;
			}
		}

		//-------------------------
		$this->vchtotal	= $voucherModel->vchAmount;
		$this->paid			= $voucherModel->vchTotalPaid;
		$this->total		= $voucherModel->vchAmount - ($voucherModel->vchTotalPaid ?? 0);

		//-------------------------
		$this->steps = [];
		if ($this->physicalCount > 0)
			$this->steps[] = BasketCheckoutForm::STEP_DELIVERY;
		if ($this->total > 0)
			$this->steps[] = BasketCheckoutForm::STEP_PAYMENT;

		// if (empty($steps))
			$this->steps[] = BasketCheckoutForm::STEP_FIN;

		if (empty($this->currentStep))
			$this->currentStep = $this->steps[0];
	}

	public function deliveryMethodModel()
	{
		if ($this->deliveryMethod == null)
			return null;

		return DeliveryMethodModel::find()->andWhere(['dlvID' => $this->deliveryMethod])->one();
	}

	public function walletModel()
	{
		if ($this->walletID == null)
			return null;

		return WalletModel::find()->andWhere(['walID' => $this->walletID])->one();
	}

	public function load($data, $formName = null)
	{
		$ret = parent::load($data, $formName);

		$deliveryMethodModel = $this->deliveryMethodModel();

		if ($deliveryMethodModel == null) {
			$this->deliveryAmount = 0;
		} else {
			$this->deliveryAmount = $deliveryMethodModel->dlvAmount;
		}

		$this->total += $this->deliveryAmount;

		return $ret;
	}

	public function saveStep()
	{
		switch ($this->currentStep) {
			case self::STEP_DELIVERY:
				return true;

			case self::STEP_PAYMENT:
				return true;

			case self::STEP_FIN:
				return $this->checkout();
		}
	}

	private function checkout()
	{
		// list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/accounting/finalize-basket',
		list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/basket/checkout',
			HttpHelper::METHOD_POST,
			[],
			[
				'deliveryMethod' => $this->deliveryMethod,
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
