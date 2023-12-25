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
use shopack\base\common\enums\enuModelScenario;

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
*/

class stuPendingSystemDiscount
{
	public string          $key;
	public string          $desc;
	public float           $amount;
	public enuDiscountType $amountType = enuDiscountType::Percent;
	public float           $max; //MaxType is opposite of AmountType
}

class stuSystemDiscount
{
	public float $amount;
	public array $info = [];
}

class stuCouponDiscount
{
	public int    $id;
	public string $code;
	public float  $amount;
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
	public $prdQtyInHand; //real
	public $slbQtyInHand; //real

	//-- input
	public $orderAdditives; //SF_QMapOfQString
	public $discountCode; //SF_QString
	public $referrer; //SF_QString
	public $referrerParams; //SF_JSON_t
	public $qty; //SF_qreal

	public $apiTokenPayload; //SF_QJsonObject
	public $assetActorID; //SF_quint64 //CurrentUserID or APIToken.Payload[uid]

	//-- compute
	public $unitPrice; //SF_qreal
	public $subTotal; //SF_qreal

	public ?array $systemDiscounts; //stuSystemDiscount
	public ?stuCouponDiscount $couponDiscount;
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
3) discount        => //          | -> uasDiscountAmount

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
	public ?int    $orderID;
	public ?string $desc;
	public string  $prdType;
	public float   $qty;
	public ?string $unit;
	public float   $unitPrice;
	public float   $subTotal;
	public ?array  $systemDiscounts; //stuSystemDiscount, SystemDiscounts_t),
	public ?array  $couponDiscount;  //stuCouponDiscount, v.ID),
	public ?float  $discount;
	public float   $afterDiscount;
	public ?float  $vatPercent;
	public ?float  $vat;
	public float   $totalPrice;

	public ?array  $params;
	public ?array  $additives;
	public ?string $referrer;
	public ?array  $referrerParams;
	// public ?string $apiTokenID;

	// public $private; // SF_QString                //encrypted + base64
	// public $subItems; // SF_QListOfVarStruct      stuVoucherItem),

	// public $sign; // SF_QString
}

class BaseBasketModel extends Model
{
	public $unitModelClass;
	public $productModelClass;
	public $saleableModelClass;
	public $discountModelClass;
	public $userAssetModelClass;

	public function init()
	{
		parent::init();

		if ($this->unitModelClass === null)
			throw new InvalidConfigException('The "unitModelClass" property must be set.');

		if ($this->productModelClass === null)
			throw new InvalidConfigException('The "productModelClass" property must be set.');

		if ($this->saleableModelClass === null)
			throw new InvalidConfigException('The "saleableModelClass" property must be set.');

		if ($this->discountModelClass === null)
			throw new InvalidConfigException('The "discountModelClass" property must be set.');

		if ($this->userAssetModelClass === null)
			throw new InvalidConfigException('The "userAssetModelClass" property must be set.');
	}

	public $saleableCode;
	public $orderAdditives;
	public $qty;
	public $discountCode;
	public $referrer;
	public $referrerParams;
	public $itemUUID;
	public $lastPreVoucher;

	public function rules()
	{
		return [
			['saleableCode',   'safe'],
			['orderAdditives', 'safe'],
			['qty',            'integer', 'min' => 0], // >0 for CREATE, >=0 for UPDATE
			['discountCode',   'safe'],
			['referrer',       'safe'],
			['referrerParams', 'safe'],
			['itemUUID',       'safe'],
			// ['lastPreVoucher',  'safe'],

			['saleableCode',   'required', 'on' => [ enuModelScenario::CREATE ]],
			// ['orderAdditives', 'required', 'on' => [ enuModelScenario::CREATE ]],
			['qty',            'required', 'on' => [ enuModelScenario::CREATE, enuModelScenario::UPDATE ]],
			// ['discountCode',   'required', 'on' => [ enuModelScenario::CREATE ]],
			// ['referrer',       'required', 'on' => [ enuModelScenario::CREATE ]],
			// ['referrerParams', 'required', 'on' => [ enuModelScenario::CREATE ]],
			['itemUUID',       'required', 'on' => enuModelScenario::UPDATE],
			// ['lastPreVoucher',  'required', 'on' => [ enuModelScenario::CREATE ]],
		];
	}

	// use \shopack\base\common\models\BasketModelTrait;

	// //convert to json and sign it
	// public function getPrevoucher()
	// {

	// }
	private $_parentModule = null;
	public static function getParentModule()
	{
		if ($this->_parentModule == null) {
			$this->_parentModule = Yii::$app->controller->module;
			if ($this->_parentModule->id == 'accounting')
				$this->_parentModule = $parentModule->module;
		}

		return $this->_parentModule;
	}

	private $_lastPreVoucher = null;
	public static function getCurrentBasket() //$userid = null)
	{
		if ($this->_lastPreVoucher == null) {
			$parentModule = self::getParentModule();
			$serviceName = $parentModule->id;

			if (empty($parentModule->servicePrivateKey))
				throw new ServerErrorHttpException('INVALID.SERVICE.PRIVATE.KEY');

			$data = Json::encode([
				'service' => $serviceName,
				'userid' => Yii::$app->user->id,
			]);
			$data = RsaPrivate::model($parentModule->servicePrivateKey)->encrypt($data);

			list ($resultStatus, $resultData) = HttpHelper::callApi('aaa/basket/current',
				HttpHelper::METHOD_GET,
				[
					'service' => $serviceName,
					'data' => $data,
				]
			);

			if ($resultStatus < 200 || $resultStatus >= 300)
				throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));

			$this->_lastPreVoucher = $resultData;
		}

		return $this->_lastPreVoucher;

	// return VoucherModel::find()
	//   ->andWhere(['vchOwnerUserID' => $userid ?? Yii::$app->user->id])
	//   ->andWhere(['vchType' => enuVoucherType::Basket])
	//   ->andWhere(['vchStatus' => enuVoucherStatus::New])
	//   ->andWhere(['vchRemovedAt' => 0])
	//   ->one();
	}

	public static function updateCurrentBasket($basketModel)
	{







	}

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

		$parentModule = self::getParentModule();
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
		// 							? NULLABLE_VALUE(_apiTokenID)
		// 							: $currentUserID;

		//-- --------------------------------
		if (empty($lastPreVoucher['items']))
			$lastPreVoucher['vchOwnerUserID'] = $currentUserID;
		else if ($lastPreVoucher['vchOwnerUserID'] != $currentUserID)
			throw new ForbiddenHttpException("invalid pre-Voucher owner");

		$productModelClass = $this->productModelClass;
		$saleableModelClass = $this->saleableModelClass;
		$userAssetModelClass = $this->userAssetModelClass;

		//-- find duplicates --------------------------------
		if (empty($lastPreVoucher['items']) == false) {
			foreach ($lastPreVoucher['items'] as $voucherItemIndex => $vItem) {

				$voucherItem = Yii::createObject(stuVoucherItem::class, $vItem);

				if (($voucherItem->service ?? null) != $serviceName)
					continue;

				if (($voucherItem->additives ?? null) != $this->orderAdditives)
					continue;

				if (($voucherItem->referrer ?? null) != $this->referrer)
					continue;

				if (($voucherItem->apiTokenID ?? null) != _apiTokenID)
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
				if (($voucherItem->couponDiscount['id'] ?? null) > 0) {
					//C3:
					if (empty($newDiscountCode))
						$newDiscountCode = $voucherItem->couponDiscount['code'];
					//C5:
					else if ($voucherItem->couponDiscount['code'] != $newDiscountCode)
						continue;
				}

				/*
				$userAssetInfo = $userAssetModelClass::find()
					->innerJoinWith('saleable')
					->andWhere(['uasID' => $voucherItem->orderID])
					->one();

				if (($userAssetInfo->saleable->slbCode != $this->saleableCode)
					|| ($userAssetInfo->uasActorID != $basketItem->assetActorID)
					|| ($userAssetInfo->uasVoucherItemInfo['key'] != $voucherItem->key)
				)
					continue;
				*/

				return $this->internalUpdateBasketItem(
					$lastPreVoucher,
					$voucherItemIndex,
					$voucherItem,
					$voucherItem->qty + $this->qty,
					$newDiscountCode
				);
			} //find duplicates
		}

		//-- fetch SLB & PRD --------------------------------
		$saleableInfo = $saleableModelClass::find()
			->select($saleableModelClass::selectableColumns())
			->addSelect($productModelClass::selectableColumns())
			->addSelect($unitModelClass::selectableColumns())
			->addSelect(new \yii\db\Expression("IF(prdInStockQty IS NULL, NULL, prdInStockQty - IFNULL(prdOrderedQty,0) + IFNULL(prdReturnedQty,0)) AS ProductQtyInHand"))
			->addSelect(new \yii\db\Expression("IF(slbInStockQty IS NULL, NULL, slbInStockQty - IFNULL(slbOrderedQty,0) + IFNULL(slbReturnedQty,0)) AS SaleableQtyInHand"))
			->innerJoinWith('product')
			->innerJoinWith('product.unit')
			->andWhere(['slbCode' => $this->saleableCode])
			->andWhere(['OR',
				'slbAvailableFromDate IS NULL',
				['<=', 'slbAvailableFromDate', new Expression('NOW()')],
			])
			->andWhere(['OR',
				'slbAvailableToDate IS NULL',
				['>=', 'slbAvailableToDate', new Expression('DATE_ADD(NOW(), 15 MINUTE)')],
			])
			->asArray()
			->one();

		if ($saleableInfo == null)
			throw new NotFoundHttpException('NOT.FOUND.SALEABLE');

		$basketItem->saleable = new $saleableModelClass;
		$basketItem->saleable->load($saleableInfo, '');

		$basketItem->prdQtyInHand = $saleableInfo['ProductQtyInHand'];
		$basketItem->slbQtyInHand = $saleableInfo['SaleableQtyInHand'];

		//-- --------------------------------
		$basketItem->discountCode      = $this->discountCode;
		$basketItem->orderAdditives    = $this->orderAdditives;
		$basketItem->referrer          = $this->referrer;
		$basketItem->referrerParams    = $this->referrerParams;

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

		//-- --------------------------------
		$this->processItemForBasket($basketItem);

		//-- --------------------------------
		$preVoucherItem = new stuVoucherItem;

		// QJsonDocument JSDPendingVouchers = QJsonDocument();
		// JSDPendingVouchers.setObject($basketItem->private.toJson());
		// $preVoucherItem->private = simpleCryptInstance()->encryptToString(JSDPendingVouchers.toJson(QJsonDocument::Compact));

		$preVoucherItem->service          = $serviceName;
		$preVoucherItem->uuid             = SecurityHelper::UUIDtoMD5();
		$preVoucherItem->desc             = $basketItem->saleable->slbName;
		$preVoucherItem->qty              = $basketItem->qty; //$this->qty;
		$preVoucherItem->unit             = $basketItem->saleable->unit->untName;
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

		$preVoucherItem->additives        = $basketItem->orderAdditives;
		$preVoucherItem->referrer         = $basketItem->referrer;
		$preVoucherItem->referrerParams   = $basketItem->referrerParams;
		$preVoucherItem->apiTokenID       = _apiTokenID;

		ORMCreateQuery qry = this->AccountUserAssets->makeCreateQuery(_apiCallContext)
			.addCols({
						tblAccountUserAssetsBase::Fields::uasActorID,
						tblAccountUserAssetsBase::Fields::uas_slbID,
						tblAccountUserAssetsBase::Fields::uasQty,
	//                     tblAccountUserAssetsBase::Fields::uasVoucherID,
						tblAccountUserAssetsBase::Fields::uasVoucherItemUUID,
						tblAccountUserAssetsBase::Fields::uasVoucherItemInfo,
	//                     tblAccountUserAssetsBase::Fields::uasPrefered,
	//                     tblAccountUserAssetsBase::Fields::uasDurationMinutes,
	//                     tblAccountUserAssetsBase::Fields::uasValidFromDate,
	//                     tblAccountUserAssetsBase::Fields::uasValidToDate,
	//                     tblAccountUserAssetsBase::Fields::uasStatus,
	//                     tblAccountUserAssetsBase::Fields::uasCreationDateTime,
					})
		;

		QVariantMap values;
		values = {
			{ tblAccountUserAssetsBase::Fields::uasActorID, $basketItem->assetActorID },
			{ tblAccountUserAssetsBase::Fields::uas_slbID, $basketItem->saleable.slbID },
			{ tblAccountUserAssetsBase::Fields::uasQty, $this->qty },
	//        { tblAccountUserAssetsBase::Fields::uasVoucherID, ??? },
			{ tblAccountUserAssetsBase::Fields::uasVoucherItemUUID, $preVoucherItem->uuid },
			{ tblAccountUserAssetsBase::Fields::uasVoucherItemInfo, $preVoucherItem->toJson().toVariantMap() },
	//            { tblAccountUserAssetsBase::Fields::uasPrefered, ??? },
	//        { tblAccountUserAssetsBase::Fields::uasDurationMinutes, ??? },
	//                    { tblAccountUserAssetsBase::Fields::uasValidFromDate, ??? },
	//                    { tblAccountUserAssetsBase::Fields::uasValidToDate, ??? },
	//            { tblAccountUserAssetsBase::Fields::uasStatus, },
	//        { tblAccountUserAssetsBase::Fields::uasCreationDateTime, DBExpression::NOW() },
		};

	//    if (NULLABLE_HAS_VALUE(_tokenID)) {
	//        qry.addCol(tblAccountUserAssetsBase::Fields::uasRelatedAPITokenID);

	//        values.insert(tblAccountUserAssetsBase::Fields::uasRelatedAPITokenID, NULLABLE_VALUE(_tokenID));
	//    }

		//-- discount
		if ($basketItem->couponDiscount.ID > 0) {
			qry.addCols({
							tblAccountUserAssetsBase::Fields::uasDiscountID,
							tblAccountUserAssetsBase::Fields::uasDiscountAmount,
						})
			;

			values.insert(tblAccountUserAssetsBase::Fields::uasDiscountID, $basketItem->couponDiscount.ID);
			values.insert(tblAccountUserAssetsBase::Fields::uasDiscountAmount, $basketItem->discount); //CouponDiscount.Amount);
		}

		//-- duration
		if (NULLABLE_HAS_VALUE($basketItem->product->prdDurationMinutes)) {

			qry.addCol(tblAccountUserAssetsBase::Fields::uasDurationMinutes);
			values.insert(tblAccountUserAssetsBase::Fields::uasDurationMinutes, NULLABLE_VALUE($basketItem->product->prdDurationMinutes));

			if ($basketItem->product->prdStartAtFirstUse == false) {
				qry.addCols({
								tblAccountUserAssetsBase::Fields::uasValidFromDate,
								tblAccountUserAssetsBase::Fields::uasValidToDate,
							})
				;
				values.insert(tblAccountUserAssetsBase::Fields::uasValidFromDate, DBExpression::NOW());
				values.insert(tblAccountUserAssetsBase::Fields::uasValidToDate,
								DBExpression::DATE_ADD(DBExpression::NOW(),
													NULLABLE_VALUE($basketItem->product->prdDurationMinutes),
													enuDBExpressionIntervalUnit::MINUTE));
			}
		}

		if (NULLABLE_HAS_VALUE($basketItem->product->prdValidFromHour)) {
			qry.addCol(tblAccountUserAssetsBase::Fields::uasValidFromHour);
			values.insert(tblAccountUserAssetsBase::Fields::uasValidFromHour, NULLABLE_VALUE($basketItem->product->prdValidFromHour));
		}

		if (NULLABLE_HAS_VALUE($basketItem->product->prdValidToHour)) {
			qry.addCol(tblAccountUserAssetsBase::Fields::uasValidToHour);
			values.insert(tblAccountUserAssetsBase::Fields::uasValidToHour, NULLABLE_VALUE($basketItem->product->prdValidToHour));
		}

		//-- CustomUserAssetFields
		QVariantMap CustomFields = this->getCustomUserAssetFieldsForQuery(_apiCallContext, $basketItem);
		for (QVariantMap::const_iterator it = CustomFields.constBegin();
			it != CustomFields.constEnd();
			it++
		) {
			qry.addCol(it.key());
			values.insert(it.key(), *it);
		}

		//--
		qry.values(values);

		//-- --------------------------------
		$preVoucherItem->orderID = qry.execute($currentUserID);

		//-- --------------------------------
		$preVoucherItem->sign = QString(sign(PreVoucherItem));

		//-- --------------------------------
		///@TODO: $preVoucherItem->dmInfo : json {"type":"adver", "additives":[{"color":"red"}, {"size":"m"}, ...]}
		/// used for DMLogic::applyCoupon -> match item.DMInfo by coupon rules
		/// return: amount of using coupon

		//-- add to the last pre voucher --------------------------------
		$lastPreVoucher['items'].append(PreVoucherItem);
		$lastPreVoucher.Summary = $lastPreVoucher['items'].size() > 1 ?
										QString("%1 items").arg($lastPreVoucher['items'].size()) :
										QString("%1 of %2").arg($preVoucherItem->qty).arg($preVoucherItem->desc);

		qint64 FinalPrice = $lastPreVoucher.Round
							+ $lastPreVoucher.ToPay
							+ $preVoucherItem->totalPrice;

		if (FinalPrice < 0) {
			this->AccountUserAssets->DeleteByPks(
				_apiCallContext,
				/*PK*/ QString::number($preVoucherItem->orderID),
				{
					//this is just for make condition safe and strong:
					{ tblAccountUserAssetsBase::Fields::uasVoucherItemUUID, $preVoucherItem->uuid },
				},
				false
			);

			this->AccountSaleables->callSP(_apiCallContext,
											"spSaleable_unReserve", {
												{ "iSaleableID", $basketItem->saleable.slbID },
												{ "iUserID", $currentUserID },
												{ "iQty", $preVoucherItem->qty },
											});

			throw exHTTPInternalServerError("Final amount computed negative!");
		}

		$lastPreVoucher.Round = static_cast<quint16>(FinalPrice % 1000);
		$lastPreVoucher.ToPay = static_cast<quint32>(FinalPrice) - $lastPreVoucher.Round;
	//    $lastPreVoucher.Type = enuPreVoucherType::Invoice;
		$lastPreVoucher.Sign.clear();
		$lastPreVoucher.Sign = QString(sign($lastPreVoucher));




		self::updateCurrentBasket($lastPreVoucher);




		return [
			$preVoucherItem->uuid,
			$lastPreVoucher,
		];






	}

	public function updateBasketItem()
	{
		$this->scenario = enuModelScenario::UPDATE;
		if ($this->validate() == false)
			return false;

	}

	public function removeBasketItem()
	{
		$this->qty = 0;
		return $this->updateBasketItem();
	}

	public function internalUpdateBasketItem(
    stuPreVoucher   $_lastPreVoucher,
    								$_voucherItemIndex,
    stuVoucherItem  $_voucherItem,
    float           $_newQty,
                    $_newDiscountCode
	) {

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
			if (($_basketItem->slbQtyInHand < 0) || ($_basketItem->prdQtyInHand < 0))
				throw new UnprocessableEntityHttpException("Available Saleable Qty({$_basketItem->slbQtyInHand}) or Available Product Qty({$_basketItem->prdQtyInHand}) < 0");

			if ($_basketItem->slbQtyInHand > $_basketItem->prdQtyInHand)
				throw new UnprocessableEntityHttpException("Available Saleable Qty({$_basketItem->slbQtyInHand}) > Available Product Qty({$_basketItem->prdQtyInHand})");

			if ($_basketItem->slbQtyInHand < $deltaQty)
				throw new UnprocessableEntityHttpException("Not enough {$_basketItem->saleable->slbCode} available in store. Available Qty({$_basketItem->slbQtyInHand}) Requested Qty({$deltaQty})");
		}

		//-- --------------------------------
		$fnComputeTotalPrice = function($_label) use (&$_basketItem) {
			$_basketItem->subTotal = $_basketItem->unitPrice * $_basketItem->qty;
			$_basketItem->afterDiscount = $_basketItem->subTotal - $_basketItem->discount;

			$_basketItem->vatPercent = ($_basketItem->product->prdVAT ?? 0);
			$_basketItem->vat = $_basketItem->afterDiscount * $_basketItem->vatPercent / 100.0;

			$_basketItem->totalPrice = $_basketItem->afterDiscount - $_basketItem->vat;

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
		//    this->parsePrize(...); -> AssetItem.PendingVouchers

		//-- discount --------------------------------
		///@TODO: what if some one uses discount code and at the same time will pay by prize credit

		$this->computeSystemDiscount($_basketItem, null, $_oldVoucherItem);
		$fnComputeTotalPrice("after computeSystemDiscount");

		$this->computeCouponDiscount($_basketItem, $_oldVoucherItem);
		$fnComputeTotalPrice("after applyCouponBasedDiscount");

		//    //-- --------------------------------
		//    this->digestPrivs(_apiCallContext, $_basketItem, $_oldVoucherItem);

		//-- reserve and un-reserve saleable and product ------------------------------------
		///@TODO: call spSaleable_unReserve by cron

		if ($deltaQty > 0) {
			Yii::$app->db->createCommand("CALL spSaleable_Reserve(
					:iSaleableID,
					:iUserID,
					:iQty
				)")
				->bindParam("iSaleableID", $_basketItem->saleable->slbID)
				->bindParam("iUserID", $currentUserID)
				->bindParam("iQty", $deltaQty)
				->execute();
		} else if ($deltaQty < 0) {
			Yii::$app->db->createCommand("CALL spSaleable_unReserve(
					:iSaleableID,
					:iUserID,
					:iQty
				)")
				->bindParam("iSaleableID", $_basketItem->saleable->slbID)
				->bindParam("iUserID", $currentUserID)
				->bindParam("iQty", abs($deltaQty))
				->execute();
		}

		//-- new pre voucher item --------------------------------
		///@TODO: add ttl for order item

		$fnComputeTotalPrice("finish");
	}

	protected function computeSystemDiscount(
		/*INOUT*/ stuBasketItem   &$_basketItem,
		?stuPendingSystemDiscount $_pendingSystemDiscount = null,
		?stuVoucherItem           $_oldVoucherItem = null
	) {
		if ($_pendingSystemDiscount && ($_pendingSystemDiscount->amount > 0)) {
			if (empty($_pendingSystemDiscount->key))
				throw exHTTPBadRequest("Pending System Discount Key is empty.");

			//revert same key system discount
			if (isset($_oldVoucherItem->systemDiscounts[$_pendingSystemDiscount->key])) {
				$oldSystemDiscount = $_oldVoucherItem->systemDiscounts[$_pendingSystemDiscount->key];
				$_basketItem->discount -= $oldSystemDiscount->Amount;

				if ($_basketItem->qty == 0)
					return;
			};

			$systemDiscount = new stuSystemDiscount;

			$systemDiscount->info["desc"] = $_pendingSystemDiscount->desc;

			if ($_pendingSystemDiscount->amountType == enuDiscountType::Percent) {
				$systemDiscount->info["amount"] = "{$_pendingSystemDiscount->amount}%";

				$systemDiscount->amount = $_basketItem->subTotal * $_pendingSystemDiscount->amount / 100.0;

				//Amount is %, Max is $
				if ($_pendingSystemDiscount->max > 0)
					$systemDiscount->amount = min($systemDiscount->amount, $_pendingSystemDiscount->max);

			} else {
				$systemDiscount->info["amount"] = $_pendingSystemDiscount->amount;

				$systemDiscount->amount = $_pendingSystemDiscount->amount;

				//Amount is $, Max is %
				if ($_pendingSystemDiscount->max > 0) {
					$max = $_basketItem->subTotal * $_pendingSystemDiscount->max / 100.0;
					$systemDiscount->amount = min($systemDiscount->amount, $max);
				}
			}

			if ($systemDiscount->amount != $_pendingSystemDiscount->amount)
				$systemDiscount->info["applied-amount"] = $systemDiscount->amount;

			$_basketItem->systemDiscounts[$_pendingSystemDiscount->key] = json_decode(json_encode($systemDiscount), true);

			$_basketItem->discount += $systemDiscount->amount;

			return;
		}

		///@TODO: tblAccountSystemDiscounts and all its behaviors must be implemented
	}

	protected function computeCouponDiscount(
		/*INOUT*/ stuBasketItem	&$_basketItem,
		?stuVoucherItem					$_oldVoucherItem = null
	) {
		//    quint64 CurrentUserID = _apiCallContext.getActorID();

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

		$_basketItem->discountCode = trim($_basketItem->discountCode);

		//C1:
		if ((($_oldVoucherItem == null) || empty($_oldVoucherItem->couponDiscount['code']))
				&& empty($_basketItem->discountCode))
			return;

		//C4.1: qty not changed
		if (($_oldVoucherItem != null)
				&& (empty($_oldVoucherItem->couponDiscount['code']) == false)
				&& (empty($_basketItem->discountCode) == false)
				&& ($_basketItem->discountCode == $_oldVoucherItem->couponDiscount['code'])
				&& ($_basketItem->qty == $_oldVoucherItem->qty))
			return;

		//C3, 4.2, 5: remove
		if (($_oldVoucherItem != null)
				&& (empty($_oldVoucherItem->couponDiscount['code']) == false)
				// && ($_basketItem->discountCode != $_oldVoucherItem->couponDiscount['code'])
		) {
			$_basketItem->discount -= $_oldVoucherItem->couponDiscount.Amount;

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

		$discountModelClass = $this->discountModelClass;
		$userAssetModelClass = $this->userAssetModelClass;

		$ommitOldCondition = null;
		if (($_oldVoucherItem != null)
			&& (empty($_oldVoucherItem->couponDiscount['code']) == false)
		) {
			$ommitOldCondition = ['!=', 'uasID', $_oldVoucherItem->orderID];
		}

		$discountInfo = $discountModelClass::find()
			->select($discountModelClass::selectableColumns())

			->leftJoin(['tmp_cpn_count' => $userAssetModelClass::find()
				->select([
					'uasDiscountID',
					'uasVoucherID',
					new \yii\db\Expression("COUNT(uasID) AS _discountUsedCount")
				])
				->where(['uasActorID' => $_basketItem->assetActorID]) //CurrentUserID })
				->andWhere(['IN', 'uasStatus', [enuAuditableStatus::Active, enuAuditableStatus::Banned]])
				->andWhere($ommitOldCondition)
				->groupBy(['uasDiscountID', 'uasVoucherID'])
			], "tmp_cpn_count.uasDiscountID = {$discountModelClass::tableName()}.dscID")
			->addSelect('tmp_cpn_count._discountUsedCount')

			->leftJoin(['tmp_cpn_amount' => $userAssetModelClass::find()
				->select([
					'uasDiscountID',
					new \yii\db\Expression("SUM(uasDiscountAmount) AS _discountUsedAmount")
				])
				->where(['uasActorID' => $_basketItem->assetActorID]) //CurrentUserID })
				->andWhere(['IN', 'uasStatus', [enuAuditableStatus::Active, enuAuditableStatus::Banned]])
				->andWhere($ommitOldCondition)
				->groupBy('uasDiscountID')
			], "tmp_cpn_amount.uasDiscountID = {$discountModelClass::tableName()}.dscID")
			->addSelect('tmp_cpn_amount._discountUsedAmount')

			->where(['dscCode' => $_basketItem->discountCode])
			->andWhere(['OR',
				'dscValidFrom IS NULL',
				['<=', 'dscValidFrom', new \yii\db\Expression('NOW()')],
			])
			->andWhere(['OR',
				'dscValidTo IS NULL',
				['>=', 'dscValidTo', new \yii\db\Expression('DATE_ADD(NOW(), 15 MINUTE)')],
			])

			.one();

		if (DiscountInfo.size() == 0)
			throw exHTTPBadRequest("Discount code not found.");

		tblAccountCouponsBase::DTO DiscountDTO;
		DiscountDTO.fromJson(QJsonObject::fromVariantMap(DiscountInfo));

		QDateTime Now = DiscountInfo.value(Targoman::API::CURRENT_TIMESTAMP).toDateTime();

		stuCouponDiscount Discount;
		Discount.ID     = DiscountDTO.cpnID;
		Discount.Code   = DiscountDTO.cpnCode;
		Discount.Amount = DiscountDTO.cpnAmount;

		NULLABLE_TYPE(quint32) _discountUsedCount;
		TAPI::setFromVariant(_discountUsedCount, DiscountInfo.value("_discountUsedCount"));
		NULLABLE_TYPE(quint32) _discountUsedAmount;
		TAPI::setFromVariant(_discountUsedAmount, DiscountInfo.value("_discountUsedAmount"));

	//        if (NULLABLE_HAS_VALUE(cpnExpiryTime) && NULLABLE_GET(cpnExpiryTime).toDateTime() < Now)
	//            throw exHTTPBadRequest("Discount code has been expired");

		if (DiscountDTO.cpnTotalUsedCount >= DiscountDTO.cpnPrimaryCount)
			throw exHTTPBadRequest("Discount code has been finished");

		if ((NULLABLE_GET_OR_DEFAULT(DiscountDTO.cpnPerUserMaxCount, 0) > 0)
				&& (NULLABLE_GET_OR_DEFAULT(_discountUsedCount, 0) >= NULLABLE_GET_OR_DEFAULT(DiscountDTO.cpnPerUserMaxCount, 0)))
			throw exHTTPBadRequest("Max discount usage per user has been reached");

		if (DiscountDTO.cpnTotalUsedAmount >= DiscountDTO.cpnTotalMaxAmount)
			throw exHTTPBadRequest("Max discount usage amount has been reached");

		if ((NULLABLE_GET_OR_DEFAULT(DiscountDTO.cpnPerUserMaxAmount, 0) > 0)
				&& (NULLABLE_GET_OR_DEFAULT(_discountUsedAmount, 0) >= NULLABLE_GET_OR_DEFAULT(DiscountDTO.cpnPerUserMaxAmount, 0)))
			throw exHTTPBadRequest("Max discount usage amount per user has been reached");

		//-- SaleableBasedMultiplier ---------------------------
		QJsonArray arr = DiscountDTO.cpnSaleableBasedMultiplier.array();
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
	//                throw exHTTPBadRequest("Discount code is not valid on selected package");

			if (multiplier.Multiplier > 0) { //found
				auto m = Discount.Amount;
				Discount.Amount = Discount.Amount * multiplier.Multiplier;

				TargomanDebug(5) << "Discount Before Multiply(" << m << ")" << "multiplier (" << multiplier.Multiplier << ")" << "Discount After Multiply(" << Discount.Amount << ")";
			}
		} //if (arr.size())

	//        Discount.Code = _discountCode;

		TargomanDebug(5) << "1 Discount:" << "ID(" << Discount.ID << ")" << "Code(" << Discount.Code << ")" << "Amount(" << Discount.Amount << ")";

		if (DiscountDTO.cpnAmountType == enuDiscountType::Percent)
			Discount.Amount = $_basketItem->subTotal * Discount.Amount / 100.0;

		TargomanDebug(5) << "2 Discount:" << "ID(" << Discount.ID << ")" << "Code(" << Discount.Code << ")" << "Amount(" << Discount.Amount << ")";

		//check cpnMaxAmount
		if (NULLABLE_HAS_VALUE(DiscountDTO.cpnMaxAmount)) {
			//note: cpnMaxAmount type is opposite to cpnAmountType
			if (DiscountDTO.cpnAmountType == enuDiscountType::Percent)
				Discount.Amount = fmin(Discount.Amount, NULLABLE_GET_OR_DEFAULT(DiscountDTO.cpnMaxAmount, 0));
			else {
				quint32 _max = /*ceil*/($_basketItem->subTotal * NULLABLE_GET_OR_DEFAULT(DiscountDTO.cpnMaxAmount, 0) / 100.0);
				Discount.Amount = fmin(Discount.Amount, _max);
			}
			TargomanDebug(5) << "3 Discount:" << "ID(" << Discount.ID << ")" << "Code(" << Discount.Code << ")" << "Amount(" << Discount.Amount << ")";
		}

		//check total - used amount
		qint32 remainDiscountAmount = DiscountDTO.cpnTotalMaxAmount - DiscountDTO.cpnTotalUsedAmount;
		if (remainDiscountAmount < Discount.Amount) {
			Discount.Amount = remainDiscountAmount;
			TargomanDebug(5) << "4 Discount:" << "ID(" << Discount.ID << ")" << "Code(" << Discount.Code << ")" << "Amount(" << Discount.Amount << ")";
		}

		//check per user - used amount
		if (NULLABLE_GET_OR_DEFAULT(DiscountDTO.cpnPerUserMaxAmount, 0) > 0) {
			remainDiscountAmount = NULLABLE_GET_OR_DEFAULT(DiscountDTO.cpnPerUserMaxAmount, 0) - NULLABLE_GET_OR_DEFAULT(_discountUsedAmount, 0);
			if (remainDiscountAmount <= 0)
				Discount.Amount = 0;
			else if (remainDiscountAmount < Discount.Amount)
				Discount.Amount = remainDiscountAmount;
			TargomanDebug(5) << "5 Discount:" << "ID(" << Discount.ID << ")" << "Code(" << Discount.Code << ")" << "Amount(" << Discount.Amount << ")";
		}

		//----------
	//    Discount.Amount = ceil(Discount.Amount);
		TargomanDebug(5) << "Discount:" << "ID(" << Discount.ID << ")" << "Code(" << Discount.Code << ")" << "Amount(" << Discount.Amount << ")";

		if (Discount.Amount > 0) {
			$_basketItem->couponDiscount = Discount;
			$_basketItem->discount += Discount.Amount;

			//@kambizzandi: Increase coupon statistics were moved to finalizeBasket,
			// because the customer may be angry about not being able to use the coupon again in same voucher
		}
	}

}
