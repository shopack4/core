<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\userpanel\models;

use Yii;
use yii\base\Model;
use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\Json;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\common\accounting\enums\enuProductType;
use shopack\base\frontend\common\helpers\Html;
use shopack\aaa\frontend\common\models\DeliveryMethodModel;
use shopack\aaa\frontend\common\models\WalletModel;

class BasketCheckoutForm extends Model //RestClientActiveRecord
{
	const STEP_DELIVERY = 'delivery';
	const STEP_PAYMENT = 'payment';
	const STEP_FIN = 'fin';

	const DELIVERY_ReceiveByCustomer = 'C';

	// public static $resourceName = 'aaa/basket';

	public $voucher;
	// public $totalPrices = 0;
	// public $totalDiscounts = 0;
	// public $totalTaxes = 0;
	// public $vchtotal = 0;

	public $physicalCount = 0;
	public $deliveryMethod = null;
	public $deliveryAmount = 0;

	public $currentStep;
	public $walletID = null;
	public $gatewayType;

	// public $subTotal = 0; //=$this->voucher['vchTotalAmount']
	// public $paid = 0; //=vchTotalPaid
	public $walletAmount = 0;
	public $total = 0;

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
			'vchAmount'           => Yii::t('aaa', 'Amount'),
			'vchItemsDiscounts'   => Yii::t('aaa', 'Discount Amount'),
			'vchItemsVATs'   			=> Yii::t('aaa', 'VAT Amount'),
			'vchDeliveryAmount'   => Yii::t('aaa', 'Delivery Amount'),
			'vchTotalAmount'      => Yii::t('aaa', 'Total Price'),
			'vchPaidByWallet'     => Yii::t('aaa', 'Paid By Wallet'),
			'vchOnlinePaid'       => Yii::t('aaa', 'Online Paid'),
			'vchOfflinePaid'      => Yii::t('aaa', 'Offline Paid'),
			'vchTotalPaid'        => Yii::t('aaa', 'Total Paid'),

      'walletAmount' => 'برداشت از کیف پول',
			'total'        => 'قابل پرداخت',

			'deliveryMethod'			=> Yii::t('aaa', 'Delivery Method'),
			'walletID'						=> Yii::t('aaa', 'Wallet'),
			'gatewayType'					=> Yii::t('aaa', 'Payment Method'),
		];
	}

	public function __construct()
	{
		$this->setCurrentVoucher();
	}

	private static ?array $_lastPreVoucher = null;
	public static function getCurrentBasket()
	{
		if (self::$_lastPreVoucher == null) {
			// $parentModule = self::getParentModule();
			// $serviceName = $parentModule->id;

			// if (empty($parentModule->servicePrivateKey))
			// 	throw new ServerErrorHttpException('INVALID.SERVICE.PRIVATE.KEY');

			// $data = Json::encode([
			// 	'service' => $serviceName,
			// 	'userid' => Yii::$app->user->id,
			// ]);
			// $data = RsaPrivate::model($parentModule->servicePrivateKey)->encrypt($data);

			list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/basket/get-current',
				HttpHelper::METHOD_POST,
				[
					'recheckItems' => true,
				],
				[
					// 'service' => $serviceName,
					// 'data' => $data,
				]
			);

			if ($resultStatus < 200 || $resultStatus >= 300) {
				throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));
			}

			if ((empty($resultData['vchItems']) == false) && (is_array($resultData['vchItems']) == false)) {
				$resultData['vchItems'] = Json::decode($resultData['vchItems'], true);
			}

			self::$_lastPreVoucher = $resultData;
		}

		return self::$_lastPreVoucher;
	}

	private function setCurrentVoucher()
	{
		//get current basket for finalize (and recheck items / price / discount / ...)
		$this->voucher = self::getCurrentBasket();

		$this->physicalCount = 0;

		$vchItems = $this->voucher['vchItems'] ?? [];

		foreach ($vchItems as $item) {
			if (isset($item['prdType']) && ($item['prdType'] == enuProductType::Physical)) {
				++$this->physicalCount;
			}
		}

		$this->total = $this->voucher['vchTotalAmount'] - ($this->voucher['vchTotalPaid'] ?? 0);
	}

	private $_deliveryMethodModel = null;
	public function deliveryMethodModel()
	{
		if ($this->deliveryMethod == null) {
			$this->_deliveryMethodModel = null;
		} else if ($this->_deliveryMethodModel == null) {
			$this->_deliveryMethodModel = DeliveryMethodModel::find()->andWhere(['dlvID' => $this->deliveryMethod])->one();
		}

		return $this->_deliveryMethodModel;
	}

	private $_walletModel = null;
	public function walletModel()
	{
		if ($this->walletID == null) {
			$this->_walletModel = null;
		} else if ($this->_walletModel == null) {
			$this->_walletModel = WalletModel::find()->andWhere(['walID' => $this->walletID])->one();
		}

		return $this->_walletModel;
	}

	public function load($data, $formName = null)
	{
		$ret = parent::load($data, $formName);

		$deliveryMethodModel = $this->deliveryMethodModel();
		if (empty($deliveryMethodModel->dlvAmount) == false) {
			$this->deliveryAmount = $deliveryMethodModel->dlvAmount;
			$this->total += $this->deliveryAmount;
		}

		$walletModel = $this->walletModel();
		if (empty($walletModel->walRemainedAmount) == false) {
			$this->walletAmount = min($walletModel->walRemainedAmount, $this->total);
			$this->total -= $this->walletAmount;
		}

		//-------------------------
		$this->steps = [];

		if ($this->physicalCount > 0)
			$this->steps[] = BasketCheckoutForm::STEP_DELIVERY;

		if (($this->total > 0) || ($this->walletAmount > 0))
			$this->steps[] = BasketCheckoutForm::STEP_PAYMENT;

		// if (empty($steps))
			$this->steps[] = BasketCheckoutForm::STEP_FIN;

		if (empty($this->currentStep))
			$this->currentStep = $this->steps[0];

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

	public function checkout()
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
