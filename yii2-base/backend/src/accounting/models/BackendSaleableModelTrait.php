<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\accounting\models;

use shopack\base\common\accounting\enums\enuAmountType;
use shopack\base\common\accounting\enums\enuDiscountType;
use Yii;

trait BackendSaleableModelTrait
{
	public $discountsInfo;
	public $discountAmount;
	public $discountedBasePrice;

	public static function reserve($actorID, $slbID, $qty, $productModelClass)
	{
		$saleableTableName = static::tableName();
		$productTableName = $productModelClass::tableName();

		$qry =<<<SQL
UPDATE {$saleableTableName}
	 SET slbOrderedQty = IFNULL(slbOrderedQty, 0) + {$qty}
     , slbUpdatedAt = NOW()
     , slbUpdatedBy = {$actorID}
 WHERE slbID = {$slbID}
SQL;
		Yii::$app->db->createCommand($qry)->execute();

		$qry =<<<SQL
    UPDATE {$productTableName}
INNER JOIN {$saleableTableName}
        ON {$saleableTableName}.slbProductID = {$productTableName}.prdID
       SET prdOrderedQty = IFNULL(prdOrderedQty, 0) + {$qty}
         , prdUpdatedAt = NOW()
         , prdUpdatedBy = {$actorID}
     WHERE slbID = {$slbID}
SQL;
		Yii::$app->db->createCommand($qry)->execute();

		// Yii::$app->db->createCommand("CALL spSaleable_reserve(
		// 	:iSaleableID,
		// 	:iUserID,
		// 	:iQty
		// )")
		// ->bindValue("iSaleableID", $basketItem->saleable->slbID)
		// ->bindValue("iUserID", $currentUserID)
		// ->bindValue("iQty", $preVoucherItem->qty)
		// ->execute();
	}

	public static function unreserve($actorID, $slbID, $qty, $productModelClass)
	{
		$saleableTableName = static::tableName();
		$productTableName = $productModelClass::tableName();

		$qry =<<<SQL
UPDATE {$saleableTableName}
	 SET slbOrderedQty = IFNULL(slbOrderedQty, 0) - {$qty}
     , slbUpdatedAt = NOW()
     , slbUpdatedBy = {$actorID}
 WHERE slbID = {$slbID}
SQL;
		Yii::$app->db->createCommand($qry)->execute();

		$qry =<<<SQL
    UPDATE {$productTableName}
INNER JOIN {$saleableTableName}
        ON {$saleableTableName}.slbProductID = {$productTableName}.prdID
       SET prdOrderedQty = IFNULL(prdOrderedQty, 0) - {$qty}
         , prdUpdatedAt = NOW()
         , prdUpdatedBy = {$actorID}
     WHERE slbID = {$slbID}
SQL;
		Yii::$app->db->createCommand($qry)->execute();

		// Yii::$app->db->createCommand("CALL spSaleable_unReserve(
		// 	:iSaleableID,
		// 	:iUserID,
		// 	:iQty
		// )")
		// ->bindValue("iSaleableID", $basketItem->saleable->slbID)
		// ->bindValue("iUserID", $currentUserID)
		// ->bindValue("iQty", $preVoucherItem->qty)
		// ->execute();
	}

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

	public static function appendDiscountQuery(
		&$query,
		$actorID = null
	) {
		if ($actorID == null)
			$actorID = 0;

		// $productTableName = $productModelClass::tableName();

		$accountingModule = self::getAccountingModule();

    $fnGetConst = function($value) { return $value; };
		$fnGetConstQouted = function($value) { return "'{$value}'"; };

		// $discountModelClass = $this->discountModelClass;
		$discountTableName = $accountingModule->discountModelClass::tableName();

		// $discountUsageModelClass = $this->discountUsageModelClass;
		$discountUsageTableName = $accountingModule->discountUsageModelClass::tableName();

		// $saleableModelClass = $this->saleableModelClass;
		// $saleableTableName = $saleableModelClass::tableName();
		$saleableTableName = static::tableName();

		//1: fetch effective system discounts

		$qry_discount_with_usage =<<<SQL
			select {$discountTableName}.dscID
					 , tmp_total_used.totalUsedCount
					 , tmp_total_amount.totalUsedAmount
					 , tmp_user_used.userUsedCount
					 , tmp_user_amount.userUsedAmount

			from {$discountTableName}

			left join (
				select dscusgDiscountID
						 , count(*) as totalUsedCount
				from {$discountUsageTableName}
				group by dscusgDiscountID
			) as tmp_total_used
			on tmp_total_used.dscusgDiscountID = {$discountTableName}.dscID

			left join (
				select dscusgDiscountID
						 , sum(dscusgAmount) as totalUsedAmount
				from {$discountUsageTableName}
				group by dscusgDiscountID
			) as tmp_total_amount
			on tmp_total_amount.dscusgDiscountID = {$discountTableName}.dscID

			left join (
				select dscusgDiscountID
						 , count(*) as userUsedCount
				from {$discountUsageTableName}
				where dscusgUserID = {$actorID}
				group by dscusgDiscountID
			) as tmp_user_used
			on tmp_user_used.dscusgDiscountID = {$discountTableName}.dscID

			left join (
				select dscusgDiscountID
						 , sum(dscusgAmount) as userUsedAmount
				from {$discountUsageTableName}
				where dscusgUserID = {$actorID}
				group by dscusgDiscountID
			) as tmp_user_amount
			on tmp_user_amount.dscusgDiscountID = {$discountTableName}.dscID

			where dscStatus != 'R'

			and dscType IN ({$fnGetConstQouted(enuDiscountType::System)}, {$fnGetConstQouted(enuDiscountType::SystemIncrease)})

SQL; //$qry_discount_with_usage

		$qry_valid_discount =<<<SQL
			select {$discountTableName}.*
					 , tmp_discount_with_usage.totalUsedAmount
					 , tmp_discount_with_usage.userUsedAmount

			from {$discountTableName}

			inner join (
				{$qry_discount_with_usage}
			) tmp_discount_with_usage
			on tmp_discount_with_usage.dscID = {$discountTableName}.dscID

			where (dscValidFrom is null
				or dscValidFrom <= NOW()
			)

			and (dscValidTo is null
				or dscValidTo >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
			)

			and (dscTargetUserIDs is null
				or JSON_SEARCH(dscTargetUserIDs, 'one', {$actorID}) is not null
			)

			and (dscTotalMaxCount is null or dscTotalMaxCount = 0
				or tmp_discount_with_usage.totalUsedCount is null or tmp_discount_with_usage.totalUsedCount = 0
				or dscTotalMaxCount > tmp_discount_with_usage.totalUsedCount
			)

			and (dscTotalMaxPrice is null or dscTotalMaxPrice = 0
				or tmp_discount_with_usage.totalUsedAmount is null or tmp_discount_with_usage.totalUsedAmount = 0
				or dscTotalMaxPrice > tmp_discount_with_usage.totalUsedAmount
			)

			and (dscPerUserMaxCount is null or dscPerUserMaxCount = 0
				or tmp_discount_with_usage.userUsedCount is null or tmp_discount_with_usage.userUsedCount = 0
				or dscPerUserMaxCount > tmp_discount_with_usage.userUsedCount
			)

			and (dscPerUserMaxPrice is null or dscPerUserMaxPrice = 0
				or tmp_discount_with_usage.userUsedAmount is null or tmp_discount_with_usage.userUsedAmount = 0
				or dscPerUserMaxPrice > tmp_discount_with_usage.userUsedAmount
			)

SQL; //$qry_valid_discount

/*
			and (dscTargetProductIDs is null
			or JSON_SEARCH(dscTargetProductIDs, 'one', {$_basketItem->saleable->slbProductID}) is not null
			)

			and (dscTargetSaleableIDs is null
			or JSON_SEARCH(dscTargetSaleableIDs, 'one', {$_basketItem->saleable->slbID}) is not null
			)
*/

		$qry_saleable_with_computd_valid_discounts =<<<SQL
			select {$saleableTableName}.slbID
			, tmp_valid_discount.*
			,	LEAST(
				slbBasePrice

				, IF(dscTotalMaxPrice is null or dscTotalMaxPrice = 0
					, slbBasePrice
					, dscTotalMaxPrice - IF(totalUsedAmount is null, 0, totalUsedAmount)
				)

				, IF(dscPerUserMaxPrice is null or dscPerUserMaxPrice = 0
					, slbBasePrice
					, dscPerUserMaxPrice - IF(userUsedAmount is null, 0, userUsedAmount)
				)

				, CASE WHEN dscMaxAmount IS NULL OR dscMaxAmount = 0 THEN
						CASE dscAmountType
							WHEN {$fnGetConstQouted(enuAmountType::Price)}   THEN dscAmount
							WHEN {$fnGetConstQouted(enuAmountType::Percent)} THEN (dscAmount / 100) * slbBasePrice
							ELSE 0
						END
					ELSE CASE dscAmountType
						WHEN {$fnGetConstQouted(enuAmountType::Price)}   THEN LEAST((dscMaxAmount / 100) * slbBasePrice, dscAmount)
						WHEN {$fnGetConstQouted(enuAmountType::Percent)} THEN LEAST(dscMaxAmount, (dscAmount / 100) * slbBasePrice)
						ELSE 0
					END
				END) AS discountAmount

			from {$saleableTableName}

			cross join  (
				{$qry_valid_discount}
			) tmp_valid_discount

			where (dscTargetProductIDs is null
				or JSON_SEARCH(dscTargetProductIDs, 'one', slbProductID) is not null
			)

			and (dscTargetSaleableIDs is null
				or JSON_SEARCH(dscTargetSaleableIDs, 'one', slbID) is not null
			)

SQL; //$qry_saleable_with_computd_valid_discounts

		//SYSTEM FIX
		$qry_saleable_with_SF_discounts =<<<SQL
			SELECT tmp_outer.*
			FROM (
				SELECT row_number() OVER (
					PARTITION BY {$saleableTableName}.slbID
					ORDER BY tmp_saleable_with_computd_valid_discounts.discountAmount DESC) AS row_num

				, {$saleableTableName}.slbID AS _slbID
				, tmp_saleable_with_computd_valid_discounts.dscID
				, tmp_saleable_with_computd_valid_discounts.discountAmount

				FROM {$saleableTableName}

				INNER JOIN (
					{$qry_saleable_with_computd_valid_discounts}
				) tmp_saleable_with_computd_valid_discounts
				ON tmp_saleable_with_computd_valid_discounts.slbID = {$saleableTableName}.slbID

				where dscType = {$fnGetConstQouted(enuDiscountType::System)}
			) tmp_outer

			where row_num = 1
SQL; //$qry_saleable_with_SF_discounts

		//SYSTEM INCREASE
		$qry_saleable_with_SI_discounts =<<<SQL
			SELECT {$saleableTableName}.slbID AS _slbID
				, GROUP_CONCAT(CONCAT(tmp_saleable_with_computd_valid_discounts.dscID, ':', tmp_saleable_with_computd_valid_discounts.discountAmount)) AS dscIDs
				, SUM(tmp_saleable_with_computd_valid_discounts.discountAmount) AS discountAmount

			FROM {$saleableTableName}

			INNER JOIN (
				{$qry_saleable_with_computd_valid_discounts}
			) tmp_saleable_with_computd_valid_discounts
			ON tmp_saleable_with_computd_valid_discounts.slbID = {$saleableTableName}.slbID

			where dscType = {$fnGetConstQouted(enuDiscountType::SystemIncrease)}

			group by {$saleableTableName}.slbID
SQL; //$qry_saleable_with_SI_discounts

		$query
			->leftJoin(['tmp_saleable_with_SF_discounts' => "({$qry_saleable_with_SF_discounts})"],
				"tmp_saleable_with_SF_discounts._slbID = {$saleableTableName}.slbID")

			->leftJoin(['tmp_saleable_with_SI_discounts' => "({$qry_saleable_with_SI_discounts})"],
				"tmp_saleable_with_SI_discounts._slbID = {$saleableTableName}.slbID")

			->addSelect(new \yii\db\Expression("CONCAT_WS(','
				, IF(tmp_saleable_with_SF_discounts.dscID IS NULL, NULL, CONCAT_WS(':', tmp_saleable_with_SF_discounts.dscID, tmp_saleable_with_SF_discounts.discountAmount))
				, tmp_saleable_with_SI_discounts.dscIDs
			) as discountsInfo"))
			->addSelect(new \yii\db\Expression("LEAST(slbBasePrice
				, IF(tmp_saleable_with_SF_discounts.discountAmount is null, 0, tmp_saleable_with_SF_discounts.discountAmount)
					+ IF(tmp_saleable_with_SI_discounts.discountAmount is null, 0, tmp_saleable_with_SI_discounts.discountAmount)
			) AS discountAmount"))
			->addSelect(new \yii\db\Expression("slbBasePrice - LEAST(slbBasePrice
				, IF(tmp_saleable_with_SF_discounts.discountAmount is null, 0, tmp_saleable_with_SF_discounts.discountAmount)
					+ IF(tmp_saleable_with_SI_discounts.discountAmount is null, 0, tmp_saleable_with_SI_discounts.discountAmount)
			) AS discountedBasePrice"))
		;

		/*
			-- mha: --
			dscTargetMemberGroupIDs
			dscTargetKanoonIDs
			dscTargetProductMhaTypes


			dscReferrers
			dscSaleableBasedMultiplier
		*/


	}

}
