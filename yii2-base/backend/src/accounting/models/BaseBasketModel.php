<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\accounting\models;

use Yii;
use yii\base\Model;
use yii\db\Expression;
use yii\web\ServerErrorHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\web\NotFoundHttpException;
use Ramsey\Uuid\Uuid;
use shopack\base\common\helpers\Json;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\common\enums\enuModelScenario;
use shopack\base\common\security\RsaPrivate;
use shopack\base\common\accounting\enums\enuAmountType;
use shopack\base\common\accounting\enums\enuUserAssetStatus;

/*
TAPI_DEFINE_STRUCT(stuPrize,
    SF_QString          (Desc),
    SF_QJsonObject      (PrizeInfo) //Interpreted by the module
);
//,  QJsonObject, QJsonObject(), v.size(), v, v.toObject()

TAPI_DEFINE_STRUCT(stuDiscountSaleableBasedMultiplier,
    SF_QString          (SaleableCode),
    SF_qreal            (Multiplier),
    SF_NULLABLE_qreal   (MinQty)
);

class stuPendingSystemDiscount
{
	public string        $key;
	public string        $desc;
	public float         $amount;
	public enuAmountType $amountType = enuAmountType::Percent;
	public float         $max; //MaxType is opposite of AmountType
}
*/

class stuSystemDiscount
{
	public int   $id;
	public float $amount;
	// public array $info = [];
}

class stuCouponDiscount
{
	public int    $id;
	public string $code;
	public float  $amount;

	public static function fromArray(array $values)
	{
		$obj = new self;

		foreach ($values as $k => $v) {
			$obj->$k = $v;
		}

		return $obj;
	}

}

/*
TAPI_DEFINE_STRUCT(stuPendingVoucher,
    SF_QString          (Name),
    SF_Enum             (Type, enuVoucherType, enuVoucherType::Credit),
    SF_quint64          (Amount),
    SF_QJsonObject      (Info)
);

TAPI_DEFINE_STRUCT(stuVoucherItemPrivate,
    SF_QListOfVarStruct (PendingVouchers, stuPendingVoucher)
);
*/

class stuBasketItem
{
	public $saleable; //saleable model with relations (unit, product)
	public $productQtyInHand; //real
	public $saleableQtyInHand; //real

	//-- input
	public $orderParams; //SF_QMapOfQString
	public $orderAdditives; //SF_QMapOfQString
	public $discountCode; //SF_QString
	public $referrer; //SF_QString
	public $referrerParams; //SF_JSON_t
	public $qty; //SF_qreal
	public $dependencies;

	public $apiTokenPayload; //SF_QJsonObject
	public $assetActorID; //SF_quint64 //$currentUserID or APIToken.Payload[uid]

	//-- compute
	public $unitPrice; //SF_qreal
	public $subTotal; //SF_qreal

	public ?array $systemDiscounts = null; //stuSystemDiscount
	public ?stuCouponDiscount $couponDiscount = null;
	public $discount; //SF_qreal
	public $afterDiscount; //SF_qreal
	public $vatPercent; //SF_quint8
	public $vat; //SF_qreal
	public $totalPrice; //SF_qreal

//    SF_Struct           (Digested, stuDigested, [](Q_DECL_UNUSED auto v) { return true; }(v)),

	public $additionalInfo; // SF_QJsonObject //per service

	public $private; //SF_Struct, stuVoucherItemPrivate
}

/*
MIGRATE:

-- CURRENT FIELDS:    vchItems    | uasVoucherItemInfo
-------------------  -------------|----------------------
   service         => //          | x
   key             => //          | -> uasUUID
   slbid           => slbID       | -> uasSaleableID
   desc            => //          | //
1) qty             => //          | -> uasQty
   unit            => //          | x
   prdtype         => prdType     | x
2) unitprice       => unitPrice   | unitPrice
   slbinfo         => params      | params
   maxqty          => maxQty      | x
   qtystep         => qtyStep     | x
3) discount        => //          | // (uasDiscountAmount will be removed from tbl)

-- NEW FIELDS:        vchItems    | uasVoucherItemInfo
-------------------  -------------|----------------------
   orderID         =>             |
4) subTotal        => 1*2         | 1*2
   systemDiscounts => //          | //
   couponDiscount  => //          | //
5) afterDiscount   => 4-3         | 4-3
6) totalPrice      => 5           | 5

*/

//Caution: Do not rename fields. Field names are used in vchItems (as json)
class stuVoucherItem
{
	public string  $service;
	public string  $key;
	public ?int    $orderID = null;
	public ?string $desc = null;
	public string  $prdType;
	public float   $qty;
	public ?float  $maxQty = null; //null: infinite
	public ?float  $qtyStep = null; //default = 1
	public ?string $unit = null;
	public float   $unitPrice;
	public float   $subTotal;
	public ?array  $systemDiscounts = null; //stuSystemDiscount, SystemDiscounts_t),
	public ?stuCouponDiscount $couponDiscount = null;  //stuCouponDiscount, v.ID),
	public ?float  $discount = null;
	public float   $afterDiscount;
	public ?float  $vatPercent = null;
	public ?float  $vat = null;
	public float   $totalPrice;

	public ?array  $params = null;
	public ?array  $additives = null;
	public ?string $referrer = null;
	public ?array  $referrerParams = null;
	public ?string $apiTokenID = null;

	public ?array  $dependencies = null;

	// public $private; // SF_QString                //encrypted + base64
	// public $subItems; // SF_QListOfVarStruct      stuVoucherItem),

	// public $sign; // SF_QString

	public static function fromArray(array $values)
	{
		$obj = new self;

		foreach ($values as $k => $v) {
			if ($k == 'couponDiscount') {
				$obj->$k = stuCouponDiscount::fromArray($v);
			} else {
				$obj->$k = $v;
			}
		}

		return $obj;
	}
}

class BaseBasketModel extends Model
{
	public $saleableCode;
	public $qty;
	public $maxQty;
	public $qtyStep;
	public $orderParams;
	public $orderAdditives;
	public $discountCode;
	public $referrer;
	public $referrerParams;
	public $apiTokenID;
	public $dependencies;
	public $itemKey;
	// public $lastPreVoucher;

	public function rules()
	{
		return [
			['saleableCode',			'safe'],
			['qty',								'integer', 'min' => 0], // >0 for CREATE, >=0 for UPDATE
			// ['maxQty',					'integer'], //not allowed to assign from ->load()
			// ['qtyStep',				'integer'], //not allowed to assign from ->load()
			['orderParams',				'safe'],
			['orderAdditives',		'safe'],
			['discountCode',			'safe'],
			['referrer',					'safe'],
			['referrerParams',		'safe'],
			['apiTokenID',				'safe'],
			['dependencies',			'safe'],
			['itemKey',						'safe'],
			// ['lastPreVoucher',		'safe'],

			['saleableCode',   'required', 'on' => [ enuModelScenario::CREATE ]],
			// ['orderParams', 'required', 'on' => [ enuModelScenario::CREATE ]],
			// ['orderAdditives', 'required', 'on' => [ enuModelScenario::CREATE ]],
			['qty',            'required', 'on' => [ enuModelScenario::CREATE, enuModelScenario::UPDATE ]],
			// ['discountCode',   'required', 'on' => [ enuModelScenario::CREATE ]],
			// ['referrer',       'required', 'on' => [ enuModelScenario::CREATE ]],
			// ['referrerParams', 'required', 'on' => [ enuModelScenario::CREATE ]],
			['itemKey',       'required', 'on' => [ enuModelScenario::UPDATE, enuModelScenario::DELETE ]],
			// ['lastPreVoucher',  'required', 'on' => [ enuModelScenario::CREATE ]],
		];
	}

	// use \shopack\base\common\models\BasketModelTrait;

	// //convert to json and sign it
	// public function getPrevoucher()
	// {
	// }

	// private static $_parentModule = null;
	// public static function getParentModule()
	// {
	// 	if (self::$_parentModule == null) {
	// 		self::$_parentModule = Yii::$app->controller->module;
	// 		if (self::$_parentModule->id == 'accounting')
	// 			self::$_parentModule = self::$_parentModule->module;
	// 	}

	// 	return self::$_parentModule;
	// }

	private static $_accountingModule = null;
	public static function getAccountingModule()
	{
		if (self::$_accountingModule == null) {
			self::$_accountingModule = Yii::$app->controller->module;
			if (self::$_accountingModule->id != 'accounting')
				self::$_accountingModule = self::$_accountingModule->accounting;
		}

		return self::$_accountingModule;
	}

	private static ?array $_lastPreVoucher = null;
	public static function getCurrentBasket() //$userid = null)
	{
		if (self::$_lastPreVoucher == null) {
			// $parentModule = Yii::$app->topModule;
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
					// 'recheckItems' => true,
				],
				[
				// 	'service' => $serviceName,
				// 	'data' => $data,
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

	// return VoucherModel::find()
	//   ->andWhere(['vchOwnerUserID' => $userid ?? Yii::$app->user->id])
	//   ->andWhere(['vchType' => enuVoucherType::Basket])
	//   ->andWhere(['vchStatus' => enuVoucherStatus::New])
	//   ->andWhere(['vchRemovedAt' => 0])
	//   ->one();
	}

	public static function updateCurrentBasket(array $basketModel)
	{
		self::$_lastPreVoucher = $basketModel;

		$parentModule = Yii::$app->topModule;
		$serviceName = $parentModule->id;

		if (empty($parentModule->servicePrivateKey))
			throw new ServerErrorHttpException('INVALID.SERVICE.PRIVATE.KEY');

		$data = Json::encode([
			'service' => $serviceName,
			'voucher' => $basketModel,
		]);
		$data = RsaPrivate::model($parentModule->servicePrivateKey)->encrypt($data);

		list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/basket/set-current',
			HttpHelper::METHOD_POST,
			[],
			[
				'service' => $serviceName,
				'data' => $data,
			]
		);

		if ($resultStatus < 200 || $resultStatus >= 300)
			throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));

		return $resultData;
	}

	//[$infoAsArray, $model]
	public static function loadModelFromQuery($query)
	{
		$local_query = clone $query;

		$infoAsArray = $local_query->asArray()->one();
		if (empty($infoAsArray))
			return [null, null];

		$models = $local_query->asArray(false)->populate([$infoAsArray]);
		$model = reset($models) ?? null;

		return [$infoAsArray, $model];
	}

	public function addToBasketInquiry()
	{
	}

	/**
	 * return (itemKey, lastPreVoucher)
	 */
	public function addToBasket()
	{
		/*
			1: validate preVoucher and owner
			2: find duplicates
			3: fetch SLB & PRD
			4: processItemForBasket
			5: create new user asset (+ custom user asset fields)
			6: compute preVoucherItem prices and sign
			7: compute preVoucher prices and sign
		*/

		$this->scenario = enuModelScenario::CREATE;
		if ($this->validate() == false)
			return false;

		if ($this->qty <= 0)
			throw new UnprocessableEntityHttpException("invalid qty");

		$parentModule = Yii::$app->topModule;
		$serviceName = $parentModule->id;

		$lastPreVoucher = self::getCurrentBasket();

		//-- validate preVoucher and owner --------------------------------
		// checkPreVoucherSanity($lastPreVoucher);

		// quint64 $currentUserID = _apiCallContext.getActorID();
		$currentUserID = Yii::$app->user->id;

		$basketItem = new stuBasketItem;

		//temp:
		$basketItem->assetActorID = $currentUserID;
		// $basketItem->assetActorID = this->IsTokenBase()
		// 							? NULLABLE_VALUE($this->apiTokenID)
		// 							: $currentUserID;

		//-- --------------------------------
		if (empty($lastPreVoucher['vchItems']))
			$lastPreVoucher['vchOwnerUserID'] = $currentUserID;
		else if ($lastPreVoucher['vchOwnerUserID'] != $currentUserID)
			throw new ForbiddenHttpException("invalid pre-Voucher owner");

		$accountingModule = self::getAccountingModule();

		$unitModelClass = $accountingModule->unitModelClass;
		$productModelClass = $accountingModule->productModelClass;
		$saleableModelClass = $accountingModule->saleableModelClass;
		$userAssetModelClass = $accountingModule->userAssetModelClass;

		//-- find duplicates --------------------------------
		if (empty($lastPreVoucher['vchItems']) == false) {
			foreach ($lastPreVoucher['vchItems'] as $voucherItemIndex => $vItem) {

				$voucherItem = stuVoucherItem::fromArray($vItem);

				if (($voucherItem->service ?? null) != $serviceName)
					continue;

				//todo: compare json arrays
				if (($voucherItem->params ?? null) != $this->orderParams)
					continue;

				//todo: compare json arrays
				if (($voucherItem->additives ?? null) != $this->orderAdditives)
					continue;

				if (($voucherItem->referrer ?? null) != $this->referrer)
					continue;

				if (($voucherItem->apiTokenID ?? null) != $this->apiTokenID)
					continue;

				/**
					* discount code:
					* C | old | new | result
					* ---------------------------------
					* 1 |  -  |  -  |  OK (-)
					* 2 |  -  |  x  |  OK (x)
					* 3 |  x  |  -  |  OK (x)
					* 4 |  x  |  x  |  OK (x)
					* 5 |  x  |  y  | NOK (don't update. will be added as a new item in basket)
					*/

				$newDiscountCode = $this->discountCode;

				//C3,C4,C5:
				if (($voucherItem->couponDiscount->id ?? null) > 0) {
					//C3:
					if (empty($newDiscountCode))
						$newDiscountCode = $voucherItem->couponDiscount->code;
					//C5:
					else if ($voucherItem->couponDiscount->code != $newDiscountCode)
						continue;
				}

				$userAssetInfo = $userAssetModelClass::find()
					->innerJoinWith('saleable')
					->andWhere(['uasID' => $voucherItem->orderID])
					->one();

				if (($userAssetInfo->saleable->slbCode != $this->saleableCode)
					|| ($userAssetInfo->uasActorID != $basketItem->assetActorID)
					|| ($userAssetInfo->uasVoucherItemInfo['key'] != $voucherItem->key)
				)
					continue;

				return $this->internalUpdateBasketItem(
					$lastPreVoucher,
					$voucherItemIndex,
					$voucherItem,
					$voucherItem->qty + $this->qty,
					$newDiscountCode
				);
			} //find duplicates
		}

		if ((empty($this->maxQty) == false) && ($this->qty > $this->maxQty)) {
			throw new UnprocessableEntityHttpException("Max Qty Reached");
		}

		//-- fetch SLB & PRD --------------------------------
		$query = $saleableModelClass::find()
			->select($saleableModelClass::selectableColumns())
			->addSelect(new \yii\db\Expression("IF(slbInStockQty IS NULL, NULL, slbInStockQty - IFNULL(slbOrderedQty,0) + IFNULL(slbReturnedQty,0)) AS _saleableQtyInHand"))

			->innerJoinWith('product')
			->addSelect($productModelClass::selectableColumns())
			->addSelect(new \yii\db\Expression("IF(prdInStockQty IS NULL, NULL, prdInStockQty - IFNULL(prdOrderedQty,0) + IFNULL(prdReturnedQty,0)) AS _productQtyInHand"))

			->innerJoinWith('product.unit')
			->addSelect($unitModelClass::selectableColumns())

			->andWhere(['slbCode' => $this->saleableCode])
			->andWhere(['<=', 'slbAvailableFromDate', new Expression('NOW()')])
			->andWhere(['OR',
				'slbAvailableToDate IS NULL',
				['>=', 'slbAvailableToDate', new Expression('DATE_ADD(NOW(), INTERVAL 15 MINUTE)')],
			])
		;

		[$saleableInfo, $basketItem->saleable] = self::loadModelFromQuery($query);
		if ($basketItem->saleable == null)
			throw new NotFoundHttpException('NOT.FOUND.SALEABLE');

		$basketItem->productQtyInHand = $saleableInfo['_productQtyInHand'];
		$basketItem->saleableQtyInHand = $saleableInfo['_saleableQtyInHand'];

		//-- --------------------------------
		$basketItem->discountCode      = $this->discountCode;
		$basketItem->orderParams       = $this->orderParams;
		$basketItem->orderAdditives    = $this->orderAdditives;
		$basketItem->referrer          = $this->referrer;
		$basketItem->referrerParams    = $this->referrerParams;
		$basketItem->dependencies      = $this->dependencies;

		$basketItem->qty               = $this->qty;
		$basketItem->unitPrice         = $basketItem->saleable->slbBasePrice;
		$basketItem->discount          = 0;

		//-- --------------------------------
		//    UsageLimits_t SaleableUsageLimits;
		//    for (auto Iter = this->AssetUsageLimitsCols.begin();
		//        Iter != this->AssetUsageLimitsCols.end();
		//        Iter++
		//    ) {
		//        SaleableUsageLimits.insert(Iter.key(), {
		//            NULLABLE_INSTANTIATE_FROM_QVARIANT(quint32, SaleableInfo.value(Iter->PerDay)),
		//            NULLABLE_INSTANTIATE_FROM_QVARIANT(quint32, SaleableInfo.value(Iter->PerWeek)),
		//            NULLABLE_INSTANTIATE_FROM_QVARIANT(quint32, SaleableInfo.value(Iter->PerMonth)),
		//            NULLABLE_INSTANTIATE_FROM_QVARIANT(quint64, SaleableInfo.value(Iter->Total))
		//        });
		//    }
		//    $basketItem->digested.Limits = SaleableUsageLimits;

		try {
			//start transaction
			$transaction = Yii::$app->db->beginTransaction();

			//-- --------------------------------
			$this->processItemForBasket($basketItem);

			//-- --------------------------------
			$preVoucherItem = new stuVoucherItem;

			// QJsonDocument JSDPendingVouchers = QJsonDocument();
			// JSDPendingVouchers.setObject($basketItem->private.toJson());
			// $preVoucherItem->private = simpleCryptInstance()->encryptToString(JSDPendingVouchers.toJson(QJsonDocument::Compact));

			$preVoucherItem->service          = $serviceName;
			$preVoucherItem->key              = Uuid::uuid4()->toString();
			$preVoucherItem->desc             = $this->makeDesc($basketItem);
			$preVoucherItem->prdType					= $basketItem->saleable->product->prdType;
			$preVoucherItem->qty              = $basketItem->qty; //$this->qty;
			$preVoucherItem->maxQty           = $this->maxQty;
			$preVoucherItem->qtyStep          = $this->qtyStep;
			$preVoucherItem->unit             = $basketItem->saleable->product->unit->untName;
			$preVoucherItem->unitPrice        = $basketItem->unitPrice;
			$preVoucherItem->subTotal         = $basketItem->subTotal;

			//store multiple discounts (system (multi) + coupon (one))
			$preVoucherItem->systemDiscounts  = $basketItem->systemDiscounts;
			$preVoucherItem->couponDiscount   = $basketItem->couponDiscount;

			$preVoucherItem->discount        	= $basketItem->discount;
			$preVoucherItem->afterDiscount    = $basketItem->afterDiscount;

			$preVoucherItem->vatPercent       = $basketItem->vatPercent;
			$preVoucherItem->vat        			= $basketItem->vat;

			$preVoucherItem->totalPrice       = $basketItem->totalPrice;

			$preVoucherItem->params           = $basketItem->orderParams;
			$preVoucherItem->additives        = $basketItem->orderAdditives;
			$preVoucherItem->referrer         = $basketItem->referrer;
			$preVoucherItem->referrerParams   = $basketItem->referrerParams;
			$preVoucherItem->dependencies     = $basketItem->dependencies;

			$preVoucherItem->apiTokenID       = $this->apiTokenID;

			$userAssetModel = new $userAssetModelClass;

			$userAssetModel->uasUUID            = $preVoucherItem->key;
			$userAssetModel->uasActorID         = $basketItem->assetActorID;
			$userAssetModel->uasSaleableID      = $basketItem->saleable->slbID;
			$userAssetModel->uasQty             = $this->qty;
			$userAssetModel->uasVoucherID       = $lastPreVoucher['vchID'];
			$userAssetModel->uasVoucherItemInfo = array_filter(Json::decode(Json::encode($preVoucherItem), true));

			//-- discount
			// if (empty($basketItem->couponDiscount->id) == false) {
			// 	$userAssetModel->uasDiscountID     = $basketItem->couponDiscount->id;
			// 	$userAssetModel->uasDiscountAmount = $basketItem->discount; //CouponDiscount.Amount);
			// }

			//-- duration
			if (empty($basketItem->saleable->product->prdDurationMinutes) == false) {
				$userAssetModel->uasDurationMinutes = $basketItem->saleable->product->prdDurationMinutes;

				if ($basketItem->saleable->product->prdStartAtFirstUse == false) {
					$userAssetModel->uasValidFromDate = new \yii\db\Expression('NOW()');
					$userAssetModel->uasValidToDate   = new \yii\db\Expression("DATE_ADD(NOW(), INTERVAL {$basketItem->saleable->product->prdDurationMinutes} MINUTE");
				}
			}

			if (empty($basketItem->saleable->product->prdValidFromHour) == false) {
				$userAssetModel->uasValidFromHour = $basketItem->saleable->product->prdValidFromHour;
			}

			if (empty($basketItem->saleable->product->prdValidToHour) == false) {
				$userAssetModel->uasValidToHour = $basketItem->saleable->product->prdValidToHour;
			}

			//-- CustomUserAssetFields
			$customFields = $this->getCustomUserAssetFieldsForQuery($basketItem);
			if (($customFields != null) && (empty($customFields) == false)) {
				foreach ($customFields as $k => $v) {
					$userAssetModel->$k = $v;
				}
			}

			//-- --------------------------------
			$userAssetModel->uasStatus = enuUserAssetStatus::Draft;
			if ($userAssetModel->save() == false) {
				//************ ERROR ************
			}
			$preVoucherItem->orderID = $userAssetModel->uasID;

			//-- --------------------------------
			// $preVoucherItem->sign = sign(PreVoucherItem);

			//-- add to the last pre voucher --------------------------------
			if (empty($lastPreVoucher['vchItems']))
				$lastPreVoucher['vchItems'] = [];

			$lastPreVoucher['vchItems'][] = array_filter(Json::decode(Json::encode($preVoucherItem), true));
			// $lastPreVoucher['vchSummary'] = count($lastPreVoucher['vchItems']) > 1
				// ? count($lastPreVoucher['vchItems']) . ' items'
				// : $preVoucherItem->qty . ' of ' . $preVoucherItem->desc;

			$finalPrice = /*$lastPreVoucher['vchRound'] + */$lastPreVoucher['vchTotalAmount'] + $preVoucherItem->totalPrice;

			if ($finalPrice < 0) {
/*
not needed: reverted in rollback

				$userAssetTableName = $userAssetModelClass::tableName();
				//uasUUID in where is just for make condition safe and strong:
				$qry =<<<SQL
  DELETE
    FROM {$userAssetTableName}
   WHERE uasID = {$preVoucherItem->orderID}
	   AND uasUUID = '{$preVoucherItem->key}'
SQL;
				Yii::$app->db->createCommand($qry)->execute();

				$saleableModelClass::unreserve(
					$currentUserID,
					$basketItem->saleable->slbID,
					$preVoucherItem->qty,
					$productModelClass
				);
*/
				throw new ServerErrorHttpException("Final amount computed negative!");
			}

			$lastPreVoucher['vchAmount'] = $lastPreVoucher['vchAmount'] + $preVoucherItem->subTotal;

			if (empty($preVoucherItem->discount) == false) {
				$lastPreVoucher['vchItemsDiscounts'] = ($lastPreVoucher['vchItemsDiscounts'] ?? 0) + $preVoucherItem->discount;
			}

			if (empty($preVoucherItem->vat) == false) {
				$lastPreVoucher['vchItemsVATs'] = ($lastPreVoucher['vchItemsVATs'] ?? 0) + $preVoucherItem->vat;
			}

			$lastPreVoucher['vchDeliveryMethodID'] = null;
			$lastPreVoucher['vchDeliveryAmount'] = null;

			// $lastPreVoucher['vchRound'] = 0; //$finalPrice % 1000;
			$lastPreVoucher['vchTotalAmount'] = $finalPrice /*- $lastPreVoucher['vchRound']*/;
			// $lastPreVoucher->sign.clear();
			// $lastPreVoucher->sign = sign($lastPreVoucher);

			self::updateCurrentBasket($lastPreVoucher);

			//commit
			if (isset($transaction))
				$transaction->commit();

		} catch (\Exception $e) {
			if (isset($transaction))
				$transaction->rollBack();
			throw $e;
		} catch (\Throwable $e) {
			if (isset($transaction))
				$transaction->rollBack();
			throw $e;
		}

		return [
			$preVoucherItem->key,
			$lastPreVoucher,
		];
	}

	public function updateBasketItem()
	{
		if ($this->scenario == enuModelScenario::DELETE) {
			$this->qty = 0;
		} else {
			$this->scenario = enuModelScenario::UPDATE;
		}

		if ($this->validate() == false)
			return false;

		if ($this->qty < 0) //==0 is valid for remove item
			throw new UnprocessableEntityHttpException("invalid qty");

		$lastPreVoucher = self::getCurrentBasket();

		if (empty($lastPreVoucher['vchItems'])) {
			throw new UnprocessableEntityHttpException("no items");
		}

		foreach ($lastPreVoucher['vchItems'] as $voucherItemIndex => $vItem) {
			$voucherItem = stuVoucherItem::fromArray($vItem);

			if ($vItem['key'] == $this->itemKey) {
				return $this->internalUpdateBasketItem(
					$lastPreVoucher,
					$voucherItemIndex,
					$voucherItem,
					$this->qty,
					$this->discountCode
				);
			}
		}

		throw new NotFoundHttpException("item not found");
	}

	public function removeBasketItem()
	{
		$this->scenario = enuModelScenario::DELETE;
		$this->qty = 0;

		return $this->updateBasketItem();
	}

	/**
	 * return (itemKey, lastPreVoucher)
	 */
	public function internalUpdateBasketItem(
										$_lastPreVoucher,
    								$_voucherItemIndex,
    stuVoucherItem  $_voucherItem,
    float           $_newQty,
                    $_newDiscountCode
	) {
    /*
      1: check prev and new coupon code
        check available instock (minus $_voucherItem->qty)
    */

    //no change?
    if (($_newQty == $_voucherItem->qty)
			&& (($_newDiscountCode == null)
				|| empty($_voucherItem->couponDiscount->code)
				|| ($_newDiscountCode == $_voucherItem->couponDiscount->code)
			)
		) {
			return [
				$_voucherItem->key,
				$_lastPreVoucher
			];
		}

		if ((empty($_voucherItem->maxQty) == false) && ($_newQty > $_voucherItem->maxQty)) {
			throw new UnprocessableEntityHttpException("Max Qty Reached");
		}

    //-- validate preVoucher and owner --------------------------------
    // checkPreVoucherSanity(_lastPreVoucher);

    // quint64 $currentUserID = _apiCallContext.getActorID();
		$currentUserID = Yii::$app->user->id;

    if (empty($_lastPreVoucher['vchItems']))
			throw new UnprocessableEntityHttpException("Pre-Voucher is empty");

    if ($_lastPreVoucher['vchOwnerUserID'] != $currentUserID)
			throw new UnprocessableEntityHttpException("invalid pre-Voucher owner");

    //-- find item --------------------------------
    $found = false;
		foreach ($_lastPreVoucher['vchItems'] as $index => $v) {
			if ($v['key'] == $_voucherItem->key) {
				$found = true;
				break;
			}
    }
		if ($found == false)
			throw new UnprocessableEntityHttpException("Item not found in pre-Voucher");

		$accountingModule = self::getAccountingModule();

		$unitModelClass = $accountingModule->unitModelClass;
		$productModelClass = $accountingModule->productModelClass;
		$saleableModelClass = $accountingModule->saleableModelClass;
		$userAssetModelClass = $accountingModule->userAssetModelClass;

		// $unitTableName = $unitModelClass::tableName();
		// $saleableTableName = $saleableModelClass::tableName();
		$userAssetTableName = $userAssetModelClass::tableName();

		//-- fetch SLB & PRD --------------------------------
		$query = $userAssetModelClass::find()
			->select($userAssetModelClass::selectableColumns())

			->innerJoinWith('saleable')
			->addSelect($saleableModelClass::selectableColumns())
			->addSelect(new \yii\db\Expression("IF(slbInStockQty IS NULL, NULL, slbInStockQty - IFNULL(slbOrderedQty,0) + IFNULL(slbReturnedQty,0)) AS _saleableQtyInHand"))

			->innerJoinWith('saleable.product')
			->addSelect($productModelClass::selectableColumns())
			->addSelect(new \yii\db\Expression("IF(prdInStockQty IS NULL, NULL, prdInStockQty - IFNULL(prdOrderedQty,0) + IFNULL(prdReturnedQty,0)) AS _productQtyInHand"))

			->innerJoinWith('saleable.product.unit')
			->addSelect($unitModelClass::selectableColumns())

			->andWhere(['uasID' => $_voucherItem->orderID])

			->andWhere(['<=', 'slbAvailableFromDate', new Expression('NOW()')])
			->andWhere(['OR',
				'slbAvailableToDate IS NULL',
				['>=', 'slbAvailableToDate', new Expression('DATE_ADD(NOW(), INTERVAL 15 MINUTE)')],
			])
		;

		[$userAssetInfo, $userAssetModel] = self::loadModelFromQuery($query);
		if ($userAssetModel == null)
			throw new NotFoundHttpException('NOT.FOUND.SALEABLE');

		$basketItem = new stuBasketItem;
		$basketItem->saleable = $userAssetModel->saleable;

		$basketItem->productQtyInHand  = $userAssetInfo['_productQtyInHand'];
		$basketItem->saleableQtyInHand = $userAssetInfo['_saleableQtyInHand'];

		//-- --------------------------------
    // QString StrPrivate = simpleCryptInstance()->decryptToString($_voucherItem->private);
    // $basketItem->private.fromJson(QJsonDocument().fromJson(StrPrivate.toLatin1()).object());

    //--  --------------------------------
    $basketItem->discountCode      = ($_newDiscountCode ?? $_voucherItem->couponDiscount->code ?? null);
		$basketItem->orderParams       = $_voucherItem->params;
    $basketItem->orderAdditives    = $_voucherItem->additives;
    $basketItem->referrer          = $_voucherItem->referrer;
    $basketItem->referrerParams    = $_voucherItem->referrerParams;
		$basketItem->dependencies      = $_voucherItem->dependencies;
//    $basketItem->apiToken          = $_voucherItem->apiToken;

    $basketItem->qty               = $_newQty;
    // $basketItem->unit.untName      = $_voucherItem->unit;
    $basketItem->unitPrice         = $basketItem->saleable->slbBasePrice;
    $basketItem->discount          = $_voucherItem->discount;

    //-- --------------------------------
//    UsageLimits_t SaleableUsageLimits;
//    for (auto Iter = this->AssetUsageLimitsCols.begin();
//        Iter != this->AssetUsageLimitsCols.end();
//        Iter++
//    ) {
//        SaleableUsageLimits.insert(Iter.key(), {
//            NULLABLE_INSTANTIATE_FROM_QVARIANT(quint32, UserAssetInfo.value(Iter->PerDay)),
//            NULLABLE_INSTANTIATE_FROM_QVARIANT(quint32, UserAssetInfo.value(Iter->PerWeek)),
//            NULLABLE_INSTANTIATE_FROM_QVARIANT(quint32, UserAssetInfo.value(Iter->PerMonth)),
//            NULLABLE_INSTANTIATE_FROM_QVARIANT(quint64, UserAssetInfo.value(Iter->Total))
//        });
//    }
//    $basketItem->digested.Limits = SaleableUsageLimits;

		try {
			//start transaction
			$transaction = Yii::$app->db->beginTransaction();

			//-- --------------------------------
			$this->processItemForBasket($basketItem, $_voucherItem);

			//-- --------------------------------
			// qint64 FinalPrice = $_lastPreVoucher.ToPay + $_lastPreVoucher.Round;
			$finalPrice = /*$_lastPreVoucher['vchRound'] + */ $_lastPreVoucher['vchTotalAmount'];
			$finalPrice -= $_voucherItem->totalPrice;

			if ($_newQty == 0) { //remove
				//uasUUID in where is just for make condition safe and strong:
				$qry =<<<SQL
  DELETE
    FROM {$userAssetTableName}
   WHERE uasID = {$_voucherItem->orderID}
	   AND uasUUID = '{$_voucherItem->key}'
SQL;
				Yii::$app->db->createCommand($qry)->execute();

				//moved to processItemForBasket
				// $saleableModelClass::unreserve(
				// 	$currentUserID,
				// 	$basketItem->saleable->slbID,
				// 	$_voucherItem->qty,
				// 	$productModelClass
				// );

				$vchItems = $_lastPreVoucher['vchItems'];
				unset($vchItems[$_voucherItemIndex]);
				$_lastPreVoucher['vchItems'] = $vchItems;

			} else { //update
				$finalPrice += $basketItem->totalPrice;

				// QJsonDocument JSDPendingVouchers = QJsonDocument();
				// JSDPendingVouchers.setObject($basketItem->private.toJson());
				// $_voucherItem->private = simpleCryptInstance()->encryptToString(JSDPendingVouchers.toJson(QJsonDocument::Compact));

				$parentModule = Yii::$app->topModule;
				$serviceName = $parentModule->id;

				$_voucherItem->service            = $serviceName;
	//        $_voucherItem->key = $_voucherItem->key;
				$_voucherItem->desc               = $basketItem->saleable->slbName;
				// $_voucherItem->prdType						= $basketItem->saleable->product->prdType;
				$_voucherItem->qty                = $basketItem->qty;
				// $_voucherItem->maxQty             = //no change
				// $_voucherItem->qtyStep            = //no change
				$_voucherItem->unit               = $basketItem->saleable->product->unit->untName;
				$_voucherItem->unitPrice          = $basketItem->unitPrice;
				$_voucherItem->subTotal           = $basketItem->subTotal;

				//store multiple discounts (system (multi) + coupon (one))
				$_voucherItem->systemDiscounts    = $basketItem->systemDiscounts;
				$_voucherItem->couponDiscount     = $basketItem->couponDiscount;

				$_voucherItem->discount           = $basketItem->discount;
				$_voucherItem->afterDiscount      = $basketItem->afterDiscount;

				$_voucherItem->vatPercent         = $basketItem->vatPercent;
				$_voucherItem->vat			          = $basketItem->vat;

				$_voucherItem->totalPrice         = $basketItem->totalPrice;

				$_voucherItem->params             = $basketItem->orderParams;
				$_voucherItem->additives          = $basketItem->orderAdditives;
				$_voucherItem->referrer           = $basketItem->referrer;
				$_voucherItem->referrerParams     = $basketItem->referrerParams;
	//        $_voucherItem->apiToken           = $basketItem->apiToken;

				$voucherItemArray = array_filter(Json::decode(Json::encode($_voucherItem), true));
				$uasVoucherItemInfo = Json::encode($voucherItemArray);
				// $uasDiscountID = $basketItem->couponDiscount->id ?? 'NULL';

				$qry =<<<SQL
	UPDATE	{$userAssetTableName}
		 SET	uasVoucherItemInfo = '{$uasVoucherItemInfo}'
			 ,	uasQty = {$_newQty}
	 WHERE	uasID = {$_voucherItem->orderID}
SQL;
							//  ,	uasDiscountAmount = {$basketItem->discount}
							//  ,	uasDiscountID = {$uasDiscountID}

				///@TODO: change tblAccountUserAssetsBase::Fields::uasRelatedAPITokenID ?

				//-- CustomUserAssetFields
				// QVariantMap CustomFields = this->getCustomUserAssetFieldsForQuery(_apiCallContext, AssetItem, &_voucherItem);
				// for (QVariantMap::const_iterator it = CustomFields.constBegin();
				//      it != CustomFields.constEnd();
				//      it++
				// ) {
				//     qry.set(it.key(), *it);
				// }

				//-- --------------------------------
				Yii::$app->db->createCommand($qry)->execute();

				//-- --------------------------------
				// $_voucherItem->sign.clear();
				// $_voucherItem->sign = QString(sign(_voucherItem));

				$_lastPreVoucher['vchItems'][$_voucherItemIndex] = $voucherItemArray;
			}

			// if (empty($_lastPreVoucher['vchItems'])) {
			//     $_lastPreVoucher.Summary = "";
			// } else if ($_lastPreVoucher['vchItems'].size() > 1)
			//     $_lastPreVoucher.Summary = QString("%1 items").arg($_lastPreVoucher['vchItems'].size());
			// else {
			//     auto item = $_lastPreVoucher['vchItems'].first();
			//     $_lastPreVoucher.Summary = QString("%1 of %2").arg(item.Qty).arg(item.Desc);
			// }

			// $_lastPreVoucher['vchRound'] = 0; //$finalPrice % 1000;
			$_lastPreVoucher['vchTotalAmount'] = $finalPrice; // - $_lastPreVoucher['vchRound'];
			// $_lastPreVoucher.Sign.clear();
			// $_lastPreVoucher.Sign = QString(sign(_lastPreVoucher));

			self::updateCurrentBasket($_lastPreVoucher);

			//commit
			if (isset($transaction))
				$transaction->commit();

		} catch (\Exception $e) {
			if (isset($transaction))
				$transaction->rollBack();
			throw $e;
		} catch (\Throwable $e) {
			if (isset($transaction))
				$transaction->rollBack();
			throw $e;
		}

		return [
			$_voucherItem->key,
			$_lastPreVoucher,
		];
	}

	/**
		* called by:
		*     internalUpdateBasketItem:
		*        addToBasket
		*        updateBasketItem
		*        removeBasketItem
		*/
	protected function processItemForBasket(
		stuBasketItem   $_basketItem,
		?stuVoucherItem $_oldVoucherItem = null
	) {
		/*
			1: check available SLB & PRD in stock qty

			check buy 2, take 3

			2: apply additives and compute unit price
			3: apply referrer -> prize
			4: apply system discount
			5: apply coupon discount
			6: digest privs
			7: reserve SLB
		*/

		//-- --------------------------------
		// quint64 $currentUserID = _apiCallContext.getActorID();
		$currentUserID = Yii::$app->user->id;

		//-- --------------------------------
		if (($_oldVoucherItem == null) && ($_basketItem->qty == 0))
			throw new UnprocessableEntityHttpException("qty is zero and old item not specified.");

		$deltaQty = $_basketItem->qty;
		if ($_oldVoucherItem != null)
			$deltaQty -= $_oldVoucherItem->qty;

		//-- check available count --------------------------------
		if ($deltaQty > 0) {
			if (($_basketItem->productQtyInHand !== null)
				&& ($_basketItem->saleableQtyInHand !== null)
			) {
				if (($_basketItem->saleableQtyInHand < 0) || ($_basketItem->productQtyInHand < 0))
					throw new UnprocessableEntityHttpException("Available Saleable Qty({$_basketItem->saleableQtyInHand}) or Available Product Qty({$_basketItem->productQtyInHand}) < 0");

				if ($_basketItem->saleableQtyInHand > $_basketItem->productQtyInHand)
					throw new UnprocessableEntityHttpException("Available Saleable Qty({$_basketItem->saleableQtyInHand}) > Available Product Qty({$_basketItem->productQtyInHand})");
			}

			if (($_basketItem->saleableQtyInHand !== null)
				&& ($_basketItem->saleableQtyInHand < $deltaQty)
			) {
				throw new UnprocessableEntityHttpException("Not enough {$_basketItem->saleable->slbCode} available in store. Available Qty({$_basketItem->saleableQtyInHand}) Requested Qty({$deltaQty})");
			}
		}

		//-- --------------------------------
		$fnComputeTotalPrice = function($_label) use (&$_basketItem) {
			$_basketItem->subTotal = $_basketItem->unitPrice * $_basketItem->qty;
			$_basketItem->afterDiscount = $_basketItem->subTotal - $_basketItem->discount;

			$_basketItem->vatPercent = ($_basketItem->saleable->product->prdVAT ?? 0);
			$_basketItem->vat = $_basketItem->afterDiscount * $_basketItem->vatPercent / 100.0;

			$_basketItem->totalPrice = $_basketItem->afterDiscount + $_basketItem->vat;

			/*
			print_r([
				$_label,
				'Qty'           => $_basketItem->qty,
				'UnitPrice'     => $_basketItem->unitPrice,
				'SubTotal'      => $_basketItem->subTotal,
				'Discount'      => $_basketItem->discount,
				'AfterDiscount' => $_basketItem->afterDiscount,
				'VAT'           => $_basketItem->vat,
				'TotalPrice'    => $_basketItem->totalPrice,
			]);
			*/
		};

		//-- --------------------------------
		$fnComputeTotalPrice("start");

		//-- check buy 2, take 3 --------------------------------
		/**
		 * example:
		 *      2 of slb A -> 1 will be added as prize (sub item with 100% discount)
		 */
		///@TODO: 2 ta bekhar 3 ta bebar:

		//-- --------------------------------
		$this->computeAdditives($_basketItem, $_oldVoucherItem);
		$fnComputeTotalPrice("after computeAdditives");

		//-- --------------------------------
		$this->computeReferrer($_basketItem, $_oldVoucherItem);
		$fnComputeTotalPrice("after computeReferrer");

		//-- --------------------------------
		//    this->parsePrize(...); -> $basketItem->pendingVouchers

		//-- discount --------------------------------
		///@TODO: what if some one uses discount code and at the same time will pay by prize credit

		$this->computeSystemDiscounts($_basketItem, null, $_oldVoucherItem);
		$fnComputeTotalPrice("after computeSystemDiscounts");

		$this->computeCouponDiscount($_basketItem, $_oldVoucherItem);
		$fnComputeTotalPrice("after applyCouponBasedDiscount");

		//    //-- --------------------------------
		//    this->digestPrivs(_apiCallContext, $_basketItem, $_oldVoucherItem);

		//-- reserve and un-reserve saleable and product ------------------------------------
		///@TODO: call spSaleable_unReserve by cron

		$accountingModule = self::getAccountingModule();

		$productModelClass = $accountingModule->productModelClass;
		$saleableModelClass = $accountingModule->saleableModelClass;

		if ($deltaQty > 0) {
			$saleableModelClass::reserve(
				$currentUserID,
				$_basketItem->saleable->slbID,
				$deltaQty,
				$productModelClass
			);
		} else if ($deltaQty < 0) {
			$saleableModelClass::unreserve(
				$currentUserID,
				$_basketItem->saleable->slbID,
				abs($deltaQty),
				$productModelClass
			);
		}

		//-- new pre voucher item --------------------------------
		///@TODO: add ttl for order item

		$fnComputeTotalPrice("finish");
	}

	protected function getCustomUserAssetFieldsForQuery(stuBasketItem $basketItem)
	{
		return null;
	}

	protected function computeAdditives(
		/*IO*/ stuBasketItem	&$_basketItem,
		?stuVoucherItem				$_oldVoucherItem = null
	) { }

	protected function computeReferrer(
		/*IO*/ stuBasketItem	&$_basketItem,
		?stuVoucherItem				$_oldVoucherItem = null
	) { }

	protected function computeSystemDiscounts(
		/*IO*/ stuBasketItem	&$_basketItem,
		?stuVoucherItem       $_oldVoucherItem = null
	) {
		//1: clear System Discounts from (basket|old voucher) and revert olds
		if (isset($_oldVoucherItem->systemDiscounts)) {
			$oldSystemDiscount = 0;
			foreach ($_oldVoucherItem->systemDiscounts as $discount) {
				$oldSystemDiscount += ($discount['applied-amount'] ?? $discount['amount']);
			}
			$_basketItem->systemDiscounts = [];
			$_basketItem->discount -= $oldSystemDiscount;

			if ($_basketItem->qty == 0)
				return;
		}

		//2: fetch effective system discounts
		$accountingModule = self::getAccountingModule();

		$saleableModelClass = $accountingModule->saleableModelClass;

		$query = $saleableModelClass::find()
			->select($saleableModelClass::selectableColumns())
			->andWhere(['slbID' => $_basketItem->saleable->slbID]);

		$currentUserID = (Yii::$app->user->isGuest ? 0 : Yii::$app->user->id);

		$saleableModelClass::appendDiscountQuery(
			$query,
			$currentUserID,
			$_basketItem->qty,
			$_basketItem->referrer,
			$_basketItem->referrerParams
		);

		$row = $query->asArray()->one();
		if (empty($row))
			return false;

		//3: applySystemDiscounts
		// discountsInfo
		// discountAmount
		// discountedBasePrice

		$items = Json::decode($row['discountsInfo'] ?? '', true);
		if (empty($items))
			return false;

		$discounts = [];
		foreach ($items as $item) {
			$discounts[] = [
				'id' => $item['id'],
				'amount' => $item['amount'],
			];
		}

		$_basketItem->systemDiscounts = $discounts;
		$_basketItem->discount += $row['discountAmount'];
	}

/*	protected function applySystemDiscount(
		/ * IO * / stuBasketItem   &$_basketItem,
		?stuPendingSystemDiscount $_pendingSystemDiscount,
		?stuVoucherItem           $_oldVoucherItem = null
	) {
		if (empty($_pendingSystemDiscount->amount))
			return;

		if (empty($_pendingSystemDiscount->key))
			throw new UnprocessableEntityHttpException('Pending System Discount Key is empty.');

		//revert same key system discount
		if (isset($_oldVoucherItem->systemDiscounts[$_pendingSystemDiscount->key])) {
			$oldSystemDiscount = $_oldVoucherItem->systemDiscounts[$_pendingSystemDiscount->key];
			$_basketItem->discount -= $oldSystemDiscount->Amount;

			if ($_basketItem->qty == 0)
				return;
		}

		$systemDiscount = new stuSystemDiscount;

		$systemDiscount->info['desc'] = $_pendingSystemDiscount->desc;

		if ($_pendingSystemDiscount->amountType == enuAmountType::Percent) {
			$systemDiscount->info['amount'] = "{$_pendingSystemDiscount->amount}%";

			$systemDiscount->amount = $_basketItem->subTotal * $_pendingSystemDiscount->amount / 100.0;

			//Amount is %, Max is $
			if ($_pendingSystemDiscount->max > 0)
				$systemDiscount->amount = min($systemDiscount->amount, $_pendingSystemDiscount->max);

		} else {
			$systemDiscount->info['amount'] = $_pendingSystemDiscount->amount;

			$systemDiscount->amount = $_pendingSystemDiscount->amount;

			//Amount is $, Max is %
			if ($_pendingSystemDiscount->max > 0) {
				$max = $_basketItem->subTotal * $_pendingSystemDiscount->max / 100.0;
				$systemDiscount->amount = min($systemDiscount->amount, $max);
			}
		}

		if ($systemDiscount->amount != $_pendingSystemDiscount->amount)
			$systemDiscount->info['applied-amount'] = $systemDiscount->amount;

		$_basketItem->systemDiscounts[$_pendingSystemDiscount->key] = Json::decode(Json::encode($systemDiscount), true);

		$_basketItem->discount += $systemDiscount->amount;
	}
*/
	protected function computeCouponDiscount(
		/*IO*/ stuBasketItem	&$_basketItem,
		?stuVoucherItem				$_oldVoucherItem = null
	) {
		//    quint64 $currentUserID = _apiCallContext.getActorID();

		/**
			* discount code:
			*  C   | old | new | qty | result
			* ------------------------------------------
		  *  1   |  -  |  -  |     | nothing
		  *  2   |  -  |  x  |     | compute (x)
		  *  3   |  x  |  -  |     | remove (x)
		  *  4.1 |  x  |  x  | ==  | nothing
		  *  4.2 |  x  |  x  | !=  | re-compute (x)
		  *  5   |  x  |  y  |     | remove (x) + compute (y)
			*/

		if ($_basketItem->discountCode !== null)
			$_basketItem->discountCode = trim($_basketItem->discountCode);

		//C1:
		if ((($_oldVoucherItem == null) || empty($_oldVoucherItem->couponDiscount->code))
				&& empty($_basketItem->discountCode))
			return;

		//C4.1: qty not changed
		if (($_oldVoucherItem != null)
				&& (empty($_oldVoucherItem->couponDiscount->code) == false)
				&& (empty($_basketItem->discountCode) == false)
				&& ($_basketItem->discountCode == $_oldVoucherItem->couponDiscount->code)
				&& ($_basketItem->qty == $_oldVoucherItem->qty))
			return;

		//C3, 4.2, 5: remove
		if (($_oldVoucherItem != null)
				&& (empty($_oldVoucherItem->couponDiscount->code) == false)
				// && ($_basketItem->discountCode != $_oldVoucherItem->couponDiscount->code)
		) {
			$_basketItem->discount -= $_oldVoucherItem->couponDiscount->amount;

			//C3:
			if (empty($_basketItem->discountCode)) {
				$_basketItem->couponDiscount = null;
				return;
			}

			$_basketItem->couponDiscount = new stuCouponDiscount;
		}

		//C2, 4.2, 5:
		if ($_basketItem->qty == 0)
			return;

		$accountingModule = self::getAccountingModule();

		$discountModelClass = $accountingModule->discountModelClass;
		$userAssetModelClass = $accountingModule->userAssetModelClass;

		$ommitOldCondition = "1=1";
		if (($_oldVoucherItem != null)
			&& (empty($_oldVoucherItem->couponDiscount->code) == false)
		) {
			$ommitOldCondition = ['!=', 'uasID', $_oldVoucherItem->orderID];
		}

		$query = $discountModelClass::find()
			->select($discountModelClass::selectableColumns())

/*
			->leftJoin(['tmp_cpn_count' => $userAssetModelClass::find()
				->select([
					'uasDiscountID',
					'uasVoucherID',
					new \yii\db\Expression("COUNT(uasID) AS _discountUsedCount")
				])
				->where(['uasActorID' => $_basketItem->assetActorID]) //$currentUserID })
				->andWhere(['IN', 'uasStatus', [enuUserAssetStatus::Active, enuUserAssetStatus::Blocked]])
				->andWhere($ommitOldCondition)
				->groupBy(['uasDiscountID', 'uasVoucherID'])
			], "tmp_cpn_count.uasDiscountID = {$discountModelClass::tableName()}.dscID")
			->addSelect('tmp_cpn_count._discountUsedCount')
*/
			->leftJoin(['tmp_cpn_count' => $userAssetModelClass::find()
				->select([
					new \yii\db\Expression("JSON_UNQUOTE(JSON_EXTRACT(uasVoucherItemInfo, '$.couponDiscount[0].id')) AS discountID"),
					'uasVoucherID',
					new \yii\db\Expression("COUNT(uasID) AS _discountUsedCount")
				])
				->where(['uasActorID' => $_basketItem->assetActorID]) //$currentUserID })
				->andWhere(['IN', 'uasStatus', [enuUserAssetStatus::Active, enuUserAssetStatus::Blocked]])
				->andWhere($ommitOldCondition)
				->groupBy(['discountID', 'uasVoucherID'])
			], "tmp_cpn_count.discountID = {$discountModelClass::tableName()}.dscID")
			->addSelect('tmp_cpn_count._discountUsedCount')

/*
			->leftJoin(['tmp_cpn_amount' => $userAssetModelClass::find()
				->select([
					'uasDiscountID',
					new \yii\db\Expression("SUM(uasDiscountAmount) AS _discountUsedAmount")
				])
				->where(['uasActorID' => $_basketItem->assetActorID]) //$currentUserID })
				->andWhere(['IN', 'uasStatus', [enuUserAssetStatus::Active, enuUserAssetStatus::Blocked]])
				->andWhere($ommitOldCondition)
				->groupBy('uasDiscountID')
			], "tmp_cpn_amount.uasDiscountID = {$discountModelClass::tableName()}.dscID")
			->addSelect('tmp_cpn_amount._discountUsedAmount')
*/
			->leftJoin(['tmp_cpn_amount' => $userAssetModelClass::find()
				->select([
					new \yii\db\Expression("JSON_UNQUOTE(JSON_EXTRACT(uasVoucherItemInfo, '$.couponDiscount[0].id')) AS discountID"),
					new \yii\db\Expression("SUM(JSON_UNQUOTE(JSON_EXTRACT(uasVoucherItemInfo, '$.couponDiscount[0].amount'))) AS _discountUsedAmount")
				])
				->where(['uasActorID' => $_basketItem->assetActorID]) //$currentUserID })
				->andWhere(['IN', 'uasStatus', [enuUserAssetStatus::Active, enuUserAssetStatus::Blocked]])
				->andWhere($ommitOldCondition)
				->groupBy('discountID')
			], "tmp_cpn_amount.discountID = {$discountModelClass::tableName()}.dscID")
			->addSelect('tmp_cpn_amount._discountUsedAmount')

			->where(['dscCodeString' => $_basketItem->discountCode])
			->andWhere(['OR',
				'dscValidFrom IS NULL',
				['<=', 'dscValidFrom', new \yii\db\Expression('NOW()')],
			])
			->andWhere(['OR',
				'dscValidTo IS NULL',
				['>=', 'dscValidTo', new \yii\db\Expression('DATE_SUB(NOW(), INTERVAL 15 MINUTE)')],
			])
		;

		[$discountInfo, $discountModel] = self::loadModelFromQuery($query);
		if ($discountModel == null)
			throw new UnprocessableEntityHttpException("Discount code not found.");

		$discount = new stuCouponDiscount;
		$discount->id     = $discountModel->dscID;
		$discount->code   = $discountModel->dscCodeString;
		$discount->amount = $discountModel->dscAmount;

		$_discountUsedCount = $discountInfo['_discountUsedCount'] ?? 0;
		$_discountUsedAmount = $discountInfo['_discountUsedAmount'] ?? 0;

		//total
		if (($discountModel->dscTotalMaxCount > 0)
				&& ($discountModel->dscTotalUsedCount >= $discountModel->dscTotalMaxCount))
			throw new UnprocessableEntityHttpException("Discount code has been finished");

		if (($discountModel->dscTotalMaxPrice > 0)
				&& ($discountModel->dscTotalUsedPrice >= $discountModel->dscTotalMaxPrice))
			throw new UnprocessableEntityHttpException("Max discount usage amount has been reached");

		//per user
		if (($discountModel->dscPerUserMaxCount > 0)
				&& ($_discountUsedCount >= $discountModel->dscPerUserMaxCount))
			throw new UnprocessableEntityHttpException("Max discount usage per user has been reached");

		if (($discountModel->dscPerUserMaxPrice > 0)
				&& ($_discountUsedAmount >= $discountModel->dscPerUserMaxPrice))
			throw new UnprocessableEntityHttpException("Max discount usage amount per user has been reached");

		//-- SaleableBasedMultiplier ---------------------------
		/*QJsonArray arr = $discountModel->dscSaleableBasedMultiplier.array();
		if (arr.size()) {
			stuDiscountSaleableBasedMultiplier multiplier;

			for (QJsonArray::const_iterator itr = arr.constBegin();
				itr != arr.constEnd();
				itr++
			) {
				auto elm = *itr;

				stuDiscountSaleableBasedMultiplier cur;
				cur.fromJson(elm.toObject());

				qreal MinQty = NULLABLE_GET_OR_DEFAULT(cur.MinQty, -1);

				if ((cur.SaleableCode == $_basketItem->saleable.slbCode)
						&& (NULLABLE_GET_OR_DEFAULT(cur.MinQty, 0) <= $_basketItem->qty)
				) {
					if ((multiplier.Multiplier == 0)
							|| (NULLABLE_GET_OR_DEFAULT(multiplier.MinQty, 0) < MinQty))
						multiplier = cur;
				}
			}

	//            if (multiplier.Multiplier == 0) //not found
	//                throw new UnprocessableEntityHttpException("Discount code is not valid on selected package");

			if (multiplier.Multiplier > 0) { //found
				auto m = $discount->amount;
				$discount->amount = $discount->amount * multiplier.Multiplier;

				TargomanDebug(5) << "Discount Before Multiply(" << m << ")" << "multiplier (" << multiplier.Multiplier << ")" << "Discount After Multiply(" << $discount->amount << ")";
			}
		} //if (arr.size())
		*/

		//        $discount->code = _discountCode;

		Yii::info([
			"Discount" => 1,
			"id" => $discount->id,
			"code" => $discount->code,
			"amount" => $discount->amount,
		]);

		if ($discountModel->dscAmountType == enuAmountType::Percent)
			$discount->amount = $_basketItem->afterDiscount * $discount->amount / 100.0;

		Yii::info([
			"Discount" => 2,
			"id" => $discount->id,
			"code" => $discount->code,
			"amount" => $discount->amount,
		]);

		//check cpnMaxAmount
		if (empty($discountModel->dscMaxAmount) == false) {
			//note: cpnMaxAmount type is opposite to cpnAmountType
			if ($discountModel->dscAmountType == enuAmountType::Percent)
				$discount->amount = min($discount->amount, $discountModel->dscMaxAmount);
			else {
				$_max = /*ceil*/($_basketItem->afterDiscount * $discountModel->dscMaxAmount / 100.0);
				$discount->amount = min($discount->amount, $_max);
			}

			Yii::info([
				"Discount" => 3,
				"id" => $discount->id,
				"code" => $discount->code,
				"amount" => $discount->amount,
			]);
		}

		//check total - used amount
		if (empty($discountModel->dscTotalMaxPrice) == false) {
			$remainDiscountAmount = $discountModel->dscTotalMaxPrice - $discountModel->dscTotalUsedPrice;
			if ($remainDiscountAmount < $discount->amount) {
				$discount->amount = $remainDiscountAmount;

				Yii::info([
					"Discount" => 4,
					"id" => $discount->id,
					"code" => $discount->code,
					"amount" => $discount->amount,
				]);
			}
		}

		//check per user - used amount
		if (empty($discountModel->dscPerUserMaxPrice) == false) {
			$remainDiscountAmount = $discountModel->dscPerUserMaxPrice - $_discountUsedAmount;
			if ($remainDiscountAmount <= 0)
				$discount->amount = 0;
			else if ($remainDiscountAmount < $discount->amount)
				$discount->amount = $remainDiscountAmount;

			Yii::info([
				"Discount" => 5,
				"id" => $discount->id,
				"code" => $discount->code,
				"amount" => $discount->amount,
			]);
		}

		//----------
		//    $discount->amount = ceil($discount->amount);

		Yii::info([
			"Discount" => 'final',
			"id" => $discount->id,
			"code" => $discount->code,
			"amount" => $discount->amount,
		]);

		if ($discount->amount > 0) {
			$_basketItem->couponDiscount = $discount;
			$_basketItem->discount += $discount->amount;

			//@kambizzandi: Increase coupon statistics were moved to finalizeBasket,
			// because the customer may be angry about not being able to use the coupon again in same voucher
		}
	}

	protected function makeDesc($basketItem)
	{
		return $basketItem->saleable->slbName;
	}

	/**
	 * recheck basket item(s) before check out
	 * called by BaseAccountingController
	 * @return:
	 * 		array of changes (update, remove)
	 * 		and [old, new] voucher prices for $itemKey or this service items
	 */
	public static function recheckBasketItems($lastPrevoucher, $voucherItems)
	{
		$parentModule = Yii::$app->topModule;
		$serviceName = $parentModule->id;

		foreach ($voucherItems as $kItem => $vItem) {
			//is mine?
			if ($vItem['service'] != $serviceName) {
				throw new ForbiddenHttpException('INVALID:Item.Service');
			}






		}



	}

}
