<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\accounting\models;

use Yii;

trait BackendSaleableModelTrait
{
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

}
