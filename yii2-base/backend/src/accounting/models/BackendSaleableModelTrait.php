<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\accounting\models;

use Yii;
use shopack\base\common\accounting\enums\enuAmountType;
use shopack\base\common\accounting\enums\enuDiscountStatus;
use shopack\base\common\accounting\enums\enuDiscountType;

trait BackendSaleableModelTrait
{
  //moved to BaseSaleableModelTrait
  // public $discountsInfo;
  // public $discountAmount;
  // public $discountedBasePrice;

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

  public static function appendDiscountQueryNonCte(
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

    $qry_dsc_with_usg =<<<SQL
      SELECT	dscu.dscID
           ,	tmp_total_used.totalUsedCount
           ,	tmp_total_amount.totalUsedAmount
           ,	tmp_user_used.userUsedCount
           ,	tmp_user_amount.userUsedAmount
        FROM	{$discountTableName} AS dscu
   LEFT JOIN	(
      SELECT 	dscusgDiscountID
           , 	count(*) AS totalUsedCount
        FROM 	{$discountUsageTableName} AS dscusgtuc
    GROUP BY 	dscusgDiscountID
              ) AS tmp_total_used
          ON	tmp_total_used.dscusgDiscountID = dscu.dscID
   LEFT JOIN	(
      SELECT	dscusgDiscountID
           ,	sum(dscusgAmount) AS totalUsedAmount
        FROM	{$discountUsageTableName} AS dscusgtua
    GROUP BY	dscusgDiscountID
              ) AS tmp_total_amount
          ON	tmp_total_amount.dscusgDiscountID = dscu.dscID
   LEFT JOIN	(
      SELECT	dscusgDiscountID
           ,	count(*) AS userUsedCount
        FROM	{$discountUsageTableName} AS dscusguuc
       WHERE	dscusgUserID = {$actorID}
    GROUP BY	dscusgDiscountID
              ) AS tmp_user_used
          ON	tmp_user_used.dscusgDiscountID = dscu.dscID
   LEFT JOIN	(
      SELECT	dscusgDiscountID
           ,	sum(dscusgAmount) AS userUsedAmount
        FROM	{$discountUsageTableName} AS dscusguua
       WHERE	dscusgUserID = {$actorID}
    GROUP BY	dscusgDiscountID
              ) AS tmp_user_amount
          ON	tmp_user_amount.dscusgDiscountID = dscu.dscID
       WHERE	dscStatus != {$fnGetConstQouted(enuDiscountStatus::Removed)}
         AND 	scType IN ({$fnGetConstQouted(enuDiscountType::System)}, {$fnGetConstQouted(enuDiscountType::SystemIncrease)})

SQL; //$qry_dsc_with_usg

    $qry_valid_dsc =<<<SQL
      select dscv.*
           , tmp_dsc_with_usg.totalUsedAmount
           , tmp_dsc_with_usg.userUsedAmount
      from {$discountTableName} AS dscv

      inner join (
        {$qry_dsc_with_usg}
      ) tmp_dsc_with_usg
      on tmp_dsc_with_usg.dscID = dscv.dscID

      where (dscValidFrom IS NULL
        OR dscValidFrom <= NOW()
      )

      and (dscValidTo IS NULL
        OR dscValidTo >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
      )

      and (dscTargetUserIDs IS NULL
        OR JSON_SEARCH(dscTargetUserIDs, 'one', {$actorID}) IS NOT NULL
      )

      and (dscTotalMaxCount IS NULL OR dscTotalMaxCount = 0
        OR tmp_dsc_with_usg.totalUsedCount IS NULL OR tmp_dsc_with_usg.totalUsedCount = 0
        OR dscTotalMaxCount > tmp_dsc_with_usg.totalUsedCount
      )

      and (dscTotalMaxPrice IS NULL OR dscTotalMaxPrice = 0
        OR tmp_dsc_with_usg.totalUsedAmount IS NULL OR tmp_dsc_with_usg.totalUsedAmount = 0
        OR dscTotalMaxPrice > tmp_dsc_with_usg.totalUsedAmount
      )

      and (dscPerUserMaxCount IS NULL OR dscPerUserMaxCount = 0
        OR tmp_dsc_with_usg.userUsedCount IS NULL OR tmp_dsc_with_usg.userUsedCount = 0
        OR dscPerUserMaxCount > tmp_dsc_with_usg.userUsedCount
      )

      and (dscPerUserMaxPrice IS NULL OR dscPerUserMaxPrice = 0
        OR tmp_dsc_with_usg.userUsedAmount IS NULL OR tmp_dsc_with_usg.userUsedAmount = 0
        OR dscPerUserMaxPrice > tmp_dsc_with_usg.userUsedAmount
      )

SQL; //$qry_valid_dsc

/*
      and (dscTargetProductIDs IS NULL
      OR JSON_SEARCH(dscTargetProductIDs, 'one', {$_basketItem->saleable->slbProductID}) IS NOT NULL
      )

      and (dscTargetSaleableIDs IS NULL
      OR JSON_SEARCH(dscTargetSaleableIDs, 'one', {$_basketItem->saleable->slbID}) IS NOT NULL
      )
*/

    $qry_slb_with_computed_dscs =<<<SQL
      select slbwcv.slbID
      , tmp_valid_dsc.*
      ,	LEAST(
        slbBasePrice

        , IF(dscTotalMaxPrice IS NULL OR dscTotalMaxPrice = 0
          , slbBasePrice
          , dscTotalMaxPrice - IF(totalUsedAmount IS NULL, 0, totalUsedAmount)
        )

        , IF(dscPerUserMaxPrice IS NULL OR dscPerUserMaxPrice = 0
          , slbBasePrice
          , dscPerUserMaxPrice - IF(userUsedAmount IS NULL, 0, userUsedAmount)
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

      from {$saleableTableName} AS slbwcv

      cross join  (
        {$qry_valid_dsc}
      ) tmp_valid_dsc

      where (dscTargetProductIDs IS NULL
        OR JSON_SEARCH(dscTargetProductIDs, 'one', slbProductID) IS NOT NULL
      )

      and (dscTargetSaleableIDs IS NULL
        OR JSON_SEARCH(dscTargetSaleableIDs, 'one', slbID) IS NOT NULL
      )

SQL; //$qry_slb_with_computed_dscs

    //SYSTEM FIX
    $qry_slb_with_SF_dscs =<<<SQL
      SELECT tmp_slbsf_outer.*
      FROM (
        SELECT row_number() OVER (
          PARTITION BY slbsf.slbID
          ORDER BY tmp_slb_with_computed_dscs.discountAmount DESC) AS row_num

        , slbsf.slbID AS _slbID
        , tmp_slb_with_computed_dscs.dscID
        , tmp_slb_with_computed_dscs.discountAmount

        FROM {$saleableTableName} AS slbsf

        INNER JOIN (
          {$qry_slb_with_computed_dscs}
        ) tmp_slb_with_computed_dscs
        ON tmp_slb_with_computed_dscs.slbID = slbsf.slbID

        where dscType = {$fnGetConstQouted(enuDiscountType::System)}
      ) tmp_slbsf_outer

      where row_num = 1
SQL; //$qry_slb_with_SF_dscs

    //SYSTEM INCREASE
    $qry_slb_with_SI_dscs =<<<SQL
      SELECT slbsi.slbID AS _slbID
        , GROUP_CONCAT(CONCAT('{"id":', tmp_slb_with_computed_dscs.dscID, ',\"amount\":', tmp_slb_with_computed_dscs.discountAmount, '}')) AS dscIDs
        , SUM(tmp_slb_with_computed_dscs.discountAmount) AS discountAmount

      FROM {$saleableTableName} AS slbsi

      INNER JOIN (
        {$qry_slb_with_computed_dscs}
      ) tmp_slb_with_computed_dscs
      ON tmp_slb_with_computed_dscs.slbID = slbsi.slbID

      where dscType = {$fnGetConstQouted(enuDiscountType::SystemIncrease)}

      group by slbsi.slbID
SQL; //$qry_slb_with_SI_dscs

    $query
      ->leftJoin(['tmp_slb_with_SF_dscs' => "({$qry_slb_with_SF_dscs})"],
        "tmp_slb_with_SF_dscs._slbID = {$saleableTableName}.slbID")

      ->leftJoin(['tmp_slb_with_SI_dscs' => "({$qry_slb_with_SI_dscs})"],
        "tmp_slb_with_SI_dscs._slbID = {$saleableTableName}.slbID")

      ->addSelect(new \yii\db\Expression("CONCAT_WS(','
        , IF(tmp_slb_with_SF_dscs.dscID IS NULL, NULL, CONCAT_WS(':', tmp_slb_with_SF_dscs.dscID, tmp_slb_with_SF_dscs.discountAmount))
        , tmp_slb_with_SI_dscs.dscIDs
      ) AS discountsInfo"))
      ->addSelect(new \yii\db\Expression("LEAST(slbBasePrice
        , IF(tmp_slb_with_SF_dscs.discountAmount IS NULL, 0, tmp_slb_with_SF_dscs.discountAmount)
          + IF(tmp_slb_with_SI_dscs.discountAmount IS NULL, 0, tmp_slb_with_SI_dscs.discountAmount)
      ) AS discountAmount"))
      ->addSelect(new \yii\db\Expression("slbBasePrice - LEAST(slbBasePrice
        , IF(tmp_slb_with_SF_dscs.discountAmount IS NULL, 0, tmp_slb_with_SF_dscs.discountAmount)
          + IF(tmp_slb_with_SI_dscs.discountAmount IS NULL, 0, tmp_slb_with_SI_dscs.discountAmount)
      ) AS discountedBasePrice"))
    ;
  }

  public static function appendDiscountQuery(
    &$query,
    $actorID = null,
    $qty = 1,
    $referrer = null,
    $referrerParams = null
  ) {
    if ($actorID == null)
      $actorID = 0;

    $fnGetConst = function($value) { return $value; };
    $fnGetConstQouted = function($value) { return "'{$value}'"; };

    $accountingModule = self::getAccountingModule();

    $saleableTableName = static::tableName();
    $productTableName = $accountingModule->productModelClass::tableName();
    $discountTableName = $accountingModule->discountModelClass::tableName();
    $discountUsageTableName = $accountingModule->discountUsageModelClass::tableName();

    //1: fetch effective system discounts

    $qry_dsc_with_usg =<<<SQL

      SELECT  dscu.dscID
           ,  tmp_total_used.totalUsedCount
           ,  tmp_total_amount.totalUsedAmount
           ,  tmp_user_used.userUsedCount
           ,  tmp_user_amount.userUsedAmount
        FROM  {$discountTableName} AS dscu
   LEFT JOIN  (
      SELECT  dscusgDiscountID
           ,  count(*) AS totalUsedCount
        FROM  {$discountUsageTableName} AS dscusgtuc
    GROUP BY  dscusgDiscountID
              ) AS tmp_total_used
          ON  tmp_total_used.dscusgDiscountID = dscu.dscID
   LEFT JOIN  (
      SELECT  dscusgDiscountID
           ,  sum(dscusgAmount) AS totalUsedAmount
        FROM  {$discountUsageTableName} AS dscusgtua
    GROUP BY  dscusgDiscountID
              ) AS tmp_total_amount
          ON  tmp_total_amount.dscusgDiscountID = dscu.dscID
   LEFT JOIN  (
      SELECT  dscusgDiscountID
           ,  count(*) AS userUsedCount
        FROM  {$discountUsageTableName} AS dscusguuc
       WHERE  dscusgUserID = {$actorID}
    GROUP BY  dscusgDiscountID
              ) AS tmp_user_used
          ON  tmp_user_used.dscusgDiscountID = dscu.dscID
   LEFT JOIN  (
      SELECT  dscusgDiscountID
           ,  sum(dscusgAmount) AS userUsedAmount
        FROM  {$discountUsageTableName} AS dscusguua
       WHERE  dscusgUserID = {$actorID}
    GROUP BY  dscusgDiscountID
              ) AS tmp_user_amount
          ON  tmp_user_amount.dscusgDiscountID = dscu.dscID
       WHERE  dscStatus = {$fnGetConstQouted(enuDiscountStatus::Active)}
         AND  dscType IN ({$fnGetConstQouted(enuDiscountType::System)}, {$fnGetConstQouted(enuDiscountType::SystemIncrease)})

SQL; //$qry_dsc_with_usg

    $qry_valid_dsc =<<<SQL

      SELECT  dscv.*
           ,  tmp_dsc_with_usg.totalUsedAmount
           ,  tmp_dsc_with_usg.userUsedAmount
        FROM  {$discountTableName} AS dscv
  INNER JOIN  qry_dsc_with_usg AS tmp_dsc_with_usg
          ON  tmp_dsc_with_usg.dscID = dscv.dscID
       WHERE  (dscValidFrom IS NULL
          OR  dscValidFrom <= NOW()
              )
         AND  (dscValidTo IS NULL
          OR  dscValidTo >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
              )
         AND  (dscTargetUserIDs IS NULL
          OR  JSON_SEARCH(dscTargetUserIDs, 'one', {$actorID}) IS NOT NULL
              )
         AND  (dscTotalMaxCount IS NULL OR dscTotalMaxCount = 0
          OR  tmp_dsc_with_usg.totalUsedCount IS NULL OR tmp_dsc_with_usg.totalUsedCount = 0
          OR  dscTotalMaxCount > tmp_dsc_with_usg.totalUsedCount
              )
         AND  (dscTotalMaxPrice IS NULL OR dscTotalMaxPrice = 0
          OR  tmp_dsc_with_usg.totalUsedAmount IS NULL OR tmp_dsc_with_usg.totalUsedAmount = 0
          OR  dscTotalMaxPrice > tmp_dsc_with_usg.totalUsedAmount
              )
         AND  (dscPerUserMaxCount IS NULL OR dscPerUserMaxCount = 0
          OR  tmp_dsc_with_usg.userUsedCount IS NULL OR tmp_dsc_with_usg.userUsedCount = 0
          OR  dscPerUserMaxCount > tmp_dsc_with_usg.userUsedCount
              )
         AND  (dscPerUserMaxPrice IS NULL OR dscPerUserMaxPrice = 0
          OR  tmp_dsc_with_usg.userUsedAmount IS NULL OR tmp_dsc_with_usg.userUsedAmount = 0
          OR  dscPerUserMaxPrice > tmp_dsc_with_usg.userUsedAmount
              )

SQL; //$qry_valid_dsc

    //dscSaleableBasedMultiplier

    //dscReferrers
    $qry_valid_dsc .= "         AND  (dscReferrers IS NULL ";
    if (empty($referrer) == false) {
      // $referrerParams
      $qry_valid_dsc .= "          OR  JSON_SEARCH(dscReferrers, 'one', '{$referrer}') IS NOT NULL";
    }
    $qry_valid_dsc .= "              )";

    //per service:
    $qry_valid_dsc .= self::getCustomConditionsToValidDiscountsQuery($actorID, 'dscv', 'tmp_dsc_with_usg');

/*
      and (dscTargetProductIDs IS NULL
      OR JSON_SEARCH(dscTargetProductIDs, 'one', {$_basketItem->saleable->slbProductID}) IS NOT NULL
      )

      and (dscTargetSaleableIDs IS NULL
      OR JSON_SEARCH(dscTargetSaleableIDs, 'one', {$_basketItem->saleable->slbID}) IS NOT NULL
      )
*/

    $qry_slb_with_computed_dscs =<<<SQL

      SELECT  slbwcv.slbID
           ,  tmp_valid_dsc.*
           ,  LEAST(
              slbBasePrice * {$qty}
              , IF(dscTotalMaxPrice IS NULL OR dscTotalMaxPrice = 0
                , slbBasePrice * {$qty}
                , dscTotalMaxPrice - IF(totalUsedAmount IS NULL, 0, totalUsedAmount)
              )
              , IF(dscPerUserMaxPrice IS NULL OR dscPerUserMaxPrice = 0
                , slbBasePrice * {$qty}
                , dscPerUserMaxPrice - IF(userUsedAmount IS NULL, 0, userUsedAmount)
              )
              , CASE WHEN dscMaxAmount IS NULL OR dscMaxAmount = 0 THEN
                  CASE dscAmountType
                    WHEN {$fnGetConstQouted(enuAmountType::Price)}   THEN dscAmount
                    WHEN {$fnGetConstQouted(enuAmountType::Percent)} THEN (dscAmount / 100) * slbBasePrice * {$qty}
                    ELSE 0
                  END
                ELSE CASE dscAmountType
                  WHEN {$fnGetConstQouted(enuAmountType::Price)}   THEN LEAST((dscMaxAmount / 100) * (slbBasePrice * {$qty}), dscAmount)
                  WHEN {$fnGetConstQouted(enuAmountType::Percent)} THEN LEAST(dscMaxAmount, (dscAmount / 100) * (slbBasePrice * {$qty}))
                  ELSE 0
                END
              END
              ) AS discountAmount
        FROM  {$saleableTableName} AS slbwcv
  INNER JOIN  {$productTableName} AS prd
          ON  prd.prdID = slbwcv.slbProductID
  CROSS JOIN  qry_valid_dsc AS tmp_valid_dsc
       WHERE  (dscTargetProductIDs IS NULL
          OR  JSON_SEARCH(dscTargetProductIDs, 'one', slbProductID) IS NOT NULL
              )
         AND  (dscTargetSaleableIDs IS NULL
          OR  JSON_SEARCH(dscTargetSaleableIDs, 'one', slbID) IS NOT NULL
              )

SQL; //$qry_slb_with_computed_dscs

    //per service:
    $qry_slb_with_computed_dscs .= self::getCustomConditionsToSaleableWithComputedDiscountsQuery($actorID, 'slbwcv', 'prd');

    //SYSTEM FIX
    $qry_slb_with_SF_dscs =<<<SQL

      SELECT  tmp_slbsf_outer.*
        FROM  (
      SELECT  row_number() OVER (PARTITION BY slbsf.slbID ORDER BY tmp_slb_with_computed_dscs.discountAmount DESC) AS row_num
           ,  slbsf.slbID AS _slbID
           ,  tmp_slb_with_computed_dscs.dscID
           ,  tmp_slb_with_computed_dscs.dscName
           ,  tmp_slb_with_computed_dscs.discountAmount
        FROM  {$saleableTableName} AS slbsf
  INNER JOIN  qry_slb_with_computed_dscs AS tmp_slb_with_computed_dscs
          ON  tmp_slb_with_computed_dscs.slbID = slbsf.slbID
       WHERE  dscType = {$fnGetConstQouted(enuDiscountType::System)}
              ) tmp_slbsf_outer
       WHERE  row_num = 1

SQL; //$qry_slb_with_SF_dscs

    //SYSTEM INCREASE
    $qry_slb_with_SI_dscs =<<<SQL

      SELECT  slbsi.slbID AS _slbID
           ,  GROUP_CONCAT(CONCAT(
                '{"id":', tmp_slb_with_computed_dscs.dscID,
                ',"amount":', tmp_slb_with_computed_dscs.discountAmount,
                ',"name":"', tmp_slb_with_computed_dscs.dscName, '"',
                ',"type":"I"',
                '}')) AS dscIDs
           ,  SUM(tmp_slb_with_computed_dscs.discountAmount) AS discountAmount
        FROM  {$saleableTableName} AS slbsi
  INNER JOIN  qry_slb_with_computed_dscs AS tmp_slb_with_computed_dscs
          ON  tmp_slb_with_computed_dscs.slbID = slbsi.slbID
       WHERE  dscType = {$fnGetConstQouted(enuDiscountType::SystemIncrease)}
    GROUP BY  slbsi.slbID

SQL; //$qry_slb_with_SI_dscs

    $query
      ->withQuery($qry_dsc_with_usg,           'qry_dsc_with_usg',           false)
      ->withQuery($qry_valid_dsc,              'qry_valid_dsc',              false)
      ->withQuery($qry_slb_with_computed_dscs, 'qry_slb_with_computed_dscs', false)
      ->withQuery($qry_slb_with_SF_dscs,       'qry_slb_with_SF_dscs',       false)
      ->withQuery($qry_slb_with_SI_dscs,       'qry_slb_with_SI_dscs',       false)

      ->leftJoin(['tmp_slb_with_SF_dscs' => "qry_slb_with_SF_dscs"],
        "tmp_slb_with_SF_dscs._slbID = {$saleableTableName}.slbID")

      ->leftJoin(['tmp_slb_with_SI_dscs' => "qry_slb_with_SI_dscs"],
        "tmp_slb_with_SI_dscs._slbID = {$saleableTableName}.slbID")

      ->addSelect(new \yii\db\Expression("CONCAT('[', CONCAT_WS(','
        , IF(tmp_slb_with_SF_dscs.dscID IS NULL, NULL, CONCAT(
          '{\"id\":', tmp_slb_with_SF_dscs.dscID,
          ',\"amount\":', tmp_slb_with_SF_dscs.discountAmount,
          ',\"name\":\"', tmp_slb_with_SF_dscs.dscName, '\"',
          ',\"type\":\"S\"',
          '}'))
        , tmp_slb_with_SI_dscs.dscIDs
        ), ']') AS discountsInfo"))
      ->addSelect(new \yii\db\Expression("LEAST(slbBasePrice * {$qty}
        , IF(tmp_slb_with_SF_dscs.discountAmount IS NULL, 0, tmp_slb_with_SF_dscs.discountAmount)
          + IF(tmp_slb_with_SI_dscs.discountAmount IS NULL, 0, tmp_slb_with_SI_dscs.discountAmount)
      ) AS discountAmount"))
      ->addSelect(new \yii\db\Expression("(slbBasePrice * {$qty}) - LEAST(slbBasePrice * {$qty}
        , IF(tmp_slb_with_SF_dscs.discountAmount IS NULL, 0, tmp_slb_with_SF_dscs.discountAmount)
          + IF(tmp_slb_with_SI_dscs.discountAmount IS NULL, 0, tmp_slb_with_SI_dscs.discountAmount)
      ) AS discountedBasePrice"))
    ;

    self::addCustomConditionsToResultQuery($actorID, $query);
  }

  public static function getCustomConditionsToValidDiscountsQuery(
    $actorID,
    $validDiscountAlias = 'dscv',
    $discountWithUsageAlias = 'tmp_dsc_with_usg'
  ) {
    return '';
  }

  public static function getCustomConditionsToSaleableWithComputedDiscountsQuery(
    $actorID,
    $saleableWithDiscountAlias = 'slbwcv',
    $productAlias = 'prd'
  ) {
    return '';
  }

  public static function addCustomConditionsToResultQuery($actorID, &$query)
  { }

}
