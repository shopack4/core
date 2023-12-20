<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\accounting\models;

use Yii;
use yii\base\Model;
use yii\db\Expression;
use yii\web\ServerErrorHttpException;
use shopack\base\common\enums\enuModelScenario;
use yii\web\ForbiddenHttpException;
use yii\web\UnprocessableEntityHttpException;

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

TAPI_DEFINE_STRUCT(stuPendingSystemDiscount,
    SF_QString          (Key),
    SF_QString          (Desc),
    SF_qreal            (Amount),
    SF_Enum             (AmountType, enuDiscountType, enuDiscountType::Percent),
    SF_qreal            (Max) //MaxType is opposite of AmountType
);

TAPI_DEFINE_STRUCT(stuSystemDiscount,
//    SF_QString          (Key),
    SF_qreal            (Amount),
    SF_QJsonObject      (Info)
);
typedef QMap<QString, stuSystemDiscount> SystemDiscounts_t;

TAPI_DEFINE_STRUCT(stuCouponDiscount,
    SF_quint64          (ID),
    SF_QString          (Code),
    SF_qreal            (Amount)
);

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

	public $systemDiscounts; //SF_QMapOfVarStruct     stuSystemDiscount, SystemDiscounts_t),
	public $couponDiscount; //SF_Struct              stuCouponDiscount, v.ID),
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
   service         => //          | //
   key             => //          | -> uasUUID
   slbid           => slbID       | -> uasSaleableID
   desc            => //          | //
1) qty             => //          | //
   unit            => //          | //
   prdtype         => prdType     | X
2) unitprice       => unitPrice   | unitPrice
   slbinfo         => params      | params
   maxqty          => maxQty      | X
   qtystep         => qtyStep     | X
3) discount        => //          | //

-- NEW FIELDS:        vchItems    | uasVoucherItemInfo
-------------------  -------------|----------------------
   orderID         =>             |
4) subTotal        => 1*2         | 1*2
   systemDiscounts => //          | //
   couponDiscount  => //          | //
5) afterDiscount   => 4-3         | 4-3
6) totalPrice      => 5           | 5

UPDATE tbl_MHA_Accounting_UserAsset
SET uasVoucherItemInfo = JSON_INSERT(
		JSON_REMOVE(
		JSON_REMOVE(
		JSON_REMOVE(
		JSON_REMOVE(
		JSON_REMOVE(
		JSON_REMOVE(OLD_uasVoucherItemInfo,
			'$.slbid'),
			'$.prdtype'),
			'$.unitprice'),
			'$.slbinfo'),
			'$.maxqty'),
			'$.qtystep')

		, '$.slbID',     JSON_EXTRACT(OLD_uasVoucherItemInfo, '$.slbid')
		, '$.unitPrice', JSON_EXTRACT(OLD_uasVoucherItemInfo, '$.unitprice')
		, '$.params',    JSON_EXTRACT(OLD_uasVoucherItemInfo, '$.slbinfo')

		, '$.subTotal',      JSON_EXTRACT(OLD_uasVoucherItemInfo, '$.qty')
                       * JSON_EXTRACT(OLD_uasVoucherItemInfo, '$.unitprice')
		, '$.afterDiscount', JSON_EXTRACT(OLD_uasVoucherItemInfo, '$.qty')
                       * JSON_EXTRACT(OLD_uasVoucherItemInfo, '$.unitprice')
		, '$.totalPrice',    JSON_EXTRACT(OLD_uasVoucherItemInfo, '$.qty')
                       * JSON_EXTRACT(OLD_uasVoucherItemInfo, '$.unitprice')
	)
	WHERE JSON_LENGTH(IFNULL(OLD_uasVoucherItemInfo, '[]')) > 0
;

*/

//Caution: Do not rename fields. Field names are used in vchItems (as json)
class stuVoucherItem
{
	public $service; // SF_QString
	public $key; // SF_MD5_t
	public $orderID; // SF_quint64                //AssetID per Service
	public $desc; // SF_QString
	public $prdType; //P:Physical, D:Digital
	public $qty; // SF_qreal
	public $unit; // SF_QString
	public $unitPrice; // SF_qreal
	public $subTotal; // SF_qreal
	public $systemDiscounts; // SF_QMapOfVarStruct       stuSystemDiscount, SystemDiscounts_t),
	public $couponDiscount; // SF_Struct                stuCouponDiscount, v.ID),
	public $discount; // SF_qreal
	public $afterDiscount; // SF_qreal
	public $vatPercent; // SF_quint8
	public $vat; // SF_qreal
	public $totalPrice; // SF_qreal

//    SF_QListOfVarStruct (Referrers, stuVoucherItemReferrer),
	public $params;
	public $additives; // SF_QMapOfQString
	public $referrer; // SF_QString
	public $referrerParams; // SF_JSON_t
	public $apiTokenID; // SF_NULLABLE_quint64

	public $private; // SF_QString                //encrypted + base64
	public $subItems; // SF_QListOfVarStruct      stuVoucherItem),

	public $sign; // SF_QString
}

class BaseBasketModel extends Model
{
	public $unitModelClass;
	public $productModelClass;
	public $saleableModelClass;
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
	public $lastPreVouche;

	public stuBasketItem $basketItem;

	public function rules()
	{
		return [
			['saleableCode',   'safe'],
			['orderAdditives', 'safe'],
			['qty',            'integer', 'min' => 0],
			['discountCode',   'safe'],
			['referrer',       'safe'],
			['referrerParams', 'safe'],
			['itemUUID',       'safe'],
			// ['lastPreVouche',  'safe'],

			['saleableCode',   'required', 'on' => [ enuModelScenario::CREATE ]],
			// ['orderAdditives', 'required', 'on' => [ enuModelScenario::CREATE ]],
			['qty',            'required', 'on' => [ enuModelScenario::CREATE, enuModelScenario::UPDATE ]],
			// ['discountCode',   'required', 'on' => [ enuModelScenario::CREATE ]],
			// ['referrer',       'required', 'on' => [ enuModelScenario::CREATE ]],
			// ['referrerParams', 'required', 'on' => [ enuModelScenario::CREATE ]],
			['itemUUID',       'required', 'on' => enuModelScenario::UPDATE],
			// ['lastPreVouche',  'required', 'on' => [ enuModelScenario::CREATE ]],
		];
	}

	// use \shopack\base\common\models\BasketModelTrait;

	// //convert to json and sign it
	// public function getPrevoucher()
	// {

	// }
	public static function getParentModule()
	{
		$parentModule = Yii::$app->controller->module;
		if ($parentModule->id == 'accounting')
			$parentModule = $parentModule->module;
		return $parentModule;
	}

	public static function getCurrentBasket() //$userid = null)
	{
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

		return $resultData;

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
		$this->scenario = enuModelScenario::CREATE;
		if ($this->validate() == false)
			return false;

		/*
			1: validate preVoucher and owner
			2: find duplicates
			3: fetch SLB & PRD
			4: processItemForBasket
			5: create new user asset (+ custom user asset fields)
			6: compute preVoucherItem prices and sign
			7: compute preVoucher prices and sign
		*/

		$parentModule = self::getParentModule();
		$serviceName = $parentModule->id;

		$lastPreVoucher = self::getCurrentBasket();

		//-- validate preVoucher and owner --------------------------------
		// checkPreVoucherSanity($lastPreVoucher);

		// quint64 $currentUserID = _apiCallContext.getActorID();
		$currentUserID = Yii::$app->user->id;

		//temp:
		$this->basketItem->AssetActorID = $currentUserID;
		// $this->basketItem->AssetActorID = this->IsTokenBase()
		// 							? NULLABLE_VALUE(_apiTokenID)
		// 							: $currentUserID;

		//-- --------------------------------
		if (empty($lastPreVoucher['items']))
			$lastPreVoucher['vchOwnerUserID'] = $currentUserID;
		else if ($lastPreVoucher['vchOwnerUserID'] != $currentUserID)
			throw new ForbiddenHttpException("invalid pre-Voucher owner");

		if ($this->qty <= 0)
			throw new UnprocessableEntityHttpException("invalid qty");

		$productModelClass = $this->productModelClass;
		$saleableModelClass = $this->saleableModelClass;
		$userAssetModelClass = $this->userAssetModelClass;

		//-- find duplicates --------------------------------
		if (empty($lastPreVoucher['items']) == false) {
			foreach ($lastPreVoucher['items'] as $kItem => $vItem) {
				if (($vItem['service'] ?? null) != $serviceName)
					continue;

				if (($vItem['additives'] ?? null) != $this->orderAdditives)
					continue;

				if (($vItem['referrer'] ?? null) != $this->referrer)
					continue;

				if (($vItem['apiTokenID'] ?? null) != _apiTokenID)
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
				if (($vItem['couponDiscount']['id'] ?? null) > 0) {
					//C3:
					if (empty($newDiscountCode))
						$newDiscountCode = $vItem['couponDiscount']['code'];
					//C5:
					else if ($vItem['couponDiscount']['code'] != $newDiscountCode)
						continue;
				}

				$userAssetInfo = $userAssetModelClass::find()
					->innerJoinWith('saleable')
					->andWhere(['uasID' => $vItem['orderID']])
					->one();

				if (($userAssetInfo->saleable->slbCode != $this->saleableCode)
					|| ($userAssetInfo->uasActorID != $this->basketItem->AssetActorID)
					|| ($userAssetInfo->uasVoucherItemInfo['key'] != $vItem['key'])
				)
					continue;

				return this->internalUpdateBasketItem(
					_apiCallContext,
					$lastPreVoucher,
					*it,
					it->Qty + $this->qty,
					$newDiscountCode);
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

		$this->basketItem->saleable = new $saleableModelClass;
		$this->basketItem->saleable->load($saleableInfo, '');

		$this->basketItem->prdQtyInHand = $saleableInfo['ProductQtyInHand'];
		$this->basketItem->slbQtyInHand = $saleableInfo['SaleableQtyInHand'];

		//-- --------------------------------
		$this->basketItem->discountCode      = $this->discountCode;
		$this->basketItem->orderAdditives    = $this->orderAdditives;
		$this->basketItem->referrer          = $this->referrer;
		$this->basketItem->referrerParams    = $this->referrerParams;

		$this->basketItem->qty               = $this->qty;
		$this->basketItem->unitPrice         = $this->basketItem->saleable->slbBasePrice;
		$this->basketItem->discount          = 0;

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
	//    $this->basketItem->digested.Limits = SaleableUsageLimits;

		//-- --------------------------------
		this->processItemForBasket(_apiCallContext, $this->basketItem);

		//-- --------------------------------
		$preVoucherItem = new stuVoucherItem;

		// QJsonDocument JSDPendingVouchers = QJsonDocument();
		// JSDPendingVouchers.setObject($this->basketItem->Private.toJson());
		// $preVoucherItem->private = simpleCryptInstance()->encryptToString(JSDPendingVouchers.toJson(QJsonDocument::Compact));

		$preVoucherItem->service          = $serviceName;
		$preVoucherItem->uuid             = SecurityHelper::UUIDtoMD5();
		$preVoucherItem->desc             = $this->basketItem->saleable->slbName;
		$preVoucherItem->qty              = $this->basketItem->qty; //$this->qty;
		$preVoucherItem->unit             = $this->basketItem->saleable->unit->untName;
		$preVoucherItem->unitPrice        = $this->basketItem->unitPrice;
		$preVoucherItem->subTotal         = $this->basketItem->subTotal;

		//store multiple discounts (system (multi) + coupon (one))
		$preVoucherItem->systemDiscounts  = $this->basketItem->systemDiscounts;
		$preVoucherItem->couponDiscount   = $this->basketItem->couponDiscount;

		$preVoucherItem->discount        = $this->basketItem->discount;
		$preVoucherItem->afterDiscount    = $this->basketItem->AfterDiscount;

		$preVoucherItem->vatPercent       = $this->basketItem->vatPercent;
		$preVoucherItem->vat        = $this->basketItem->vat;

		$preVoucherItem->totalPrice       = $this->basketItem->totalPrice;

		$preVoucherItem->additives        = $this->basketItem->orderAdditives;
		$preVoucherItem->referrer         = $this->basketItem->referrer;
		$preVoucherItem->referrerParams   = $this->basketItem->referrerParams;
		$preVoucherItem->apiTokenID       = _apiTokenID;

		ORMCreateQuery qry = this->AccountUserAssets->makeCreateQuery(_apiCallContext)
			.addCols({
						tblAccountUserAssetsBase::Fields::uasActorID,
						tblAccountUserAssetsBase::Fields::uas_slbID,
						tblAccountUserAssetsBase::Fields::uasQty,
	//                     tblAccountUserAssetsBase::Fields::uas_vchID,
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
			{ tblAccountUserAssetsBase::Fields::uasActorID, $this->basketItem->AssetActorID },
			{ tblAccountUserAssetsBase::Fields::uas_slbID, $this->basketItem->saleable.slbID },
			{ tblAccountUserAssetsBase::Fields::uasQty, $this->qty },
	//        { tblAccountUserAssetsBase::Fields::uas_vchID, ??? },
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
		if ($this->basketItem->couponDiscount.ID > 0) {
			qry.addCols({
							tblAccountUserAssetsBase::Fields::uas_cpnID,
							tblAccountUserAssetsBase::Fields::uasDiscountAmount,
						})
			;

			values.insert(tblAccountUserAssetsBase::Fields::uas_cpnID, $this->basketItem->couponDiscount.ID);
			values.insert(tblAccountUserAssetsBase::Fields::uasDiscountAmount, $this->basketItem->discount); //CouponDiscount.Amount);
		}

		//-- duration
		if (NULLABLE_HAS_VALUE($this->basketItem->Product.prdDurationMinutes)) {

			qry.addCol(tblAccountUserAssetsBase::Fields::uasDurationMinutes);
			values.insert(tblAccountUserAssetsBase::Fields::uasDurationMinutes, NULLABLE_VALUE($this->basketItem->Product.prdDurationMinutes));

			if ($this->basketItem->Product.prdStartAtFirstUse == false) {
				qry.addCols({
								tblAccountUserAssetsBase::Fields::uasValidFromDate,
								tblAccountUserAssetsBase::Fields::uasValidToDate,
							})
				;
				values.insert(tblAccountUserAssetsBase::Fields::uasValidFromDate, DBExpression::NOW());
				values.insert(tblAccountUserAssetsBase::Fields::uasValidToDate,
								DBExpression::DATE_ADD(DBExpression::NOW(),
													NULLABLE_VALUE($this->basketItem->Product.prdDurationMinutes),
													enuDBExpressionIntervalUnit::MINUTE));
			}
		}

		if (NULLABLE_HAS_VALUE($this->basketItem->Product.prdValidFromHour)) {
			qry.addCol(tblAccountUserAssetsBase::Fields::uasValidFromHour);
			values.insert(tblAccountUserAssetsBase::Fields::uasValidFromHour, NULLABLE_VALUE($this->basketItem->Product.prdValidFromHour));
		}

		if (NULLABLE_HAS_VALUE($this->basketItem->Product.prdValidToHour)) {
			qry.addCol(tblAccountUserAssetsBase::Fields::uasValidToHour);
			values.insert(tblAccountUserAssetsBase::Fields::uasValidToHour, NULLABLE_VALUE($this->basketItem->Product.prdValidToHour));
		}

		//-- CustomUserAssetFields
		QVariantMap CustomFields = this->getCustomUserAssetFieldsForQuery(_apiCallContext, $this->basketItem);
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
												{ "iSaleableID", $this->basketItem->saleable.slbID },
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

	public function internalUpdateBasketItem() {}

	public function applySystemDiscount() {}
	public function applyCouponDiscount() {}

}
