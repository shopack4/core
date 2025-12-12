<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\PerModuleMigration;

class m231113_000000_accounting_rename_coupon_to_discount extends PerModuleMigration
{
	public function safeUp()
	{
		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_UserAsset`
	DROP INDEX `FK_tbl_{{MODULE}}_Acc_UserAsset_tbl_Coupon`,
	DROP FOREIGN KEY `FK_tbl_{{MODULE}}_Acc_UserAsset_tbl_Coupon`;
SQL
    );

		$this->execute(<<<SQL
RENAME TABLE `tbl_{{MODULE}}_Accounting_Coupon` TO `tbl_{{MODULE}}_Accounting_Discount`;
SQL
    );

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_Discount`
	CHANGE COLUMN `cpnID` `dscID` INT(10) UNSIGNED NOT NULL FIRST,
	DROP PRIMARY KEY;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_Discount`
	CHANGE COLUMN `dscID` `dscID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
	ADD PRIMARY KEY (`dscID`) USING BTREE;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_Discount`
	DROP INDEX `cpnUUID`,
	DROP INDEX `cpnCode_cpnRemovedAt`,
	DROP INDEX `cpnValidTo`,
	DROP INDEX `cpnCreatedBy`,
	DROP INDEX `cpnCreatedAt`,
	DROP INDEX `cpnUpdatedBy`,
	DROP INDEX `cpnStatus`,
	DROP INDEX `cpnValidFrom`,
	DROP INDEX `cpnType`;
SQL
    );

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_Discount`
	CHANGE COLUMN `cpnUUID` `dscUUID` VARCHAR(38) NOT NULL COLLATE 'utf8mb4_unicode_ci' AFTER `dscID`,
	CHANGE COLUMN `cpnCode` `dscCode` VARCHAR(32) NOT NULL COLLATE 'utf8mb4_unicode_ci' AFTER `dscUUID`,
	CHANGE COLUMN `cpnName` `dscName` VARCHAR(64) NOT NULL COLLATE 'utf8mb4_unicode_ci' AFTER `dscCode`,
	CHANGE COLUMN `cpnPrimaryCount` `dscPrimaryCount` INT(10) UNSIGNED NOT NULL AFTER `dscName`,
	CHANGE COLUMN `cpnTotalMaxAmount` `dscTotalMaxAmount` INT(10) UNSIGNED NOT NULL AFTER `dscPrimaryCount`,
	CHANGE COLUMN `cpnPerUserMaxCount` `dscPerUserMaxCount` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `dscTotalMaxAmount`,
	CHANGE COLUMN `cpnPerUserMaxAmount` `dscPerUserMaxAmount` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `dscPerUserMaxCount`,
	CHANGE COLUMN `cpnValidFrom` `dscValidFrom` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `dscPerUserMaxAmount`,
	CHANGE COLUMN `cpnValidTo` `dscValidTo` DATETIME NULL DEFAULT NULL AFTER `dscValidFrom`,
	CHANGE COLUMN `cpnAmount` `dscAmount` INT(10) UNSIGNED NOT NULL AFTER `dscValidTo`,
	CHANGE COLUMN `cpnAmountType` `dscAmountType` CHAR(1) NOT NULL DEFAULT '%' COMMENT '%:Percent, $:Amount, Z:Free' COLLATE 'utf8mb4_unicode_ci' AFTER `dscAmount`,
	CHANGE COLUMN `cpnMaxAmount` `dscMaxAmount` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `dscAmountType`,
	CHANGE COLUMN `cpnSaleableBasedMultiplier` `dscSaleableBasedMultiplier` JSON NULL DEFAULT NULL AFTER `dscMaxAmount`,
	CHANGE COLUMN `cpnTotalUsedCount` `dscTotalUsedCount` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `dscSaleableBasedMultiplier`,
	CHANGE COLUMN `cpnTotalUsedAmount` `dscTotalUsedAmount` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `dscTotalUsedCount`,
	CHANGE COLUMN `cpnI18NData` `dscI18NData` JSON NULL DEFAULT NULL AFTER `dscTotalUsedAmount`,
	CHANGE COLUMN `cpnStatus` `dscStatus` CHAR(1) NOT NULL DEFAULT 'A' COMMENT 'A:Active, R:Removed' COLLATE 'utf8mb4_unicode_ci' AFTER `dscI18NData`,
	CHANGE COLUMN `cpnCreatedAt` `dscCreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `dscStatus`,
	CHANGE COLUMN `cpnCreatedBy` `dscCreatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL AFTER `dscCreatedAt`,
	CHANGE COLUMN `cpnUpdatedAt` `dscUpdatedAt` DATETIME NULL DEFAULT NULL AFTER `dscCreatedBy`,
	CHANGE COLUMN `cpnUpdatedBy` `dscUpdatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL AFTER `dscUpdatedAt`,
	CHANGE COLUMN `cpnRemovedAt` `dscRemovedAt` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `dscUpdatedBy`,
	CHANGE COLUMN `cpnRemovedBy` `dscRemovedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL AFTER `dscRemovedAt`,
	ADD UNIQUE INDEX `dscUUID` (`dscUUID`) USING BTREE,
	ADD UNIQUE INDEX `dscCode_dscRemovedAt` (`dscCode`, `dscRemovedAt`) USING BTREE,
	ADD INDEX `dscValidTo` (`dscValidTo`) USING BTREE,
	ADD INDEX `dscCreatedBy` (`dscCreatedBy`) USING BTREE,
	ADD INDEX `dscCreatedAt` (`dscCreatedAt`) USING BTREE,
	ADD INDEX `dscUpdatedBy` (`dscUpdatedBy`) USING BTREE,
	ADD INDEX `dscStatus` (`dscStatus`) USING BTREE,
	ADD INDEX `dscValidFrom` (`dscValidFrom`) USING BTREE,
	ADD INDEX `dscType` (`dscAmountType`) USING BTREE;
SQL
    );

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_Discount`
	CHANGE COLUMN `dscName` `dscName` VARCHAR(64) NOT NULL COLLATE 'utf8mb4_unicode_ci' AFTER `dscUUID`,
	CHANGE COLUMN `dscCode` `dscCode` VARCHAR(32) NULL COLLATE 'utf8mb4_unicode_ci' AFTER `dscName`,
	CHANGE COLUMN `dscPrimaryCount` `dscPrimaryCount` INT(10) UNSIGNED NULL AFTER `dscCode`,
	CHANGE COLUMN `dscTotalMaxAmount` `dscTotalMaxAmount` INT(10) UNSIGNED NULL AFTER `dscPrimaryCount`;
SQL
    );

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_UserAsset`
	CHANGE COLUMN `uasCouponID` `uasDiscountID` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `uasVoucherItemInfo`,
	ADD CONSTRAINT `FK_tbl_{{MODULE}}_Acc_UserAsset_tbl_{{MODULE}}_Acc_Discount` FOREIGN KEY (`uasDiscountID`) REFERENCES `tbl_{{MODULE}}_Accounting_Discount` (`dscID`) ON UPDATE NO ACTION ON DELETE NO ACTION;
SQL
    );

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_Discount`
	CHANGE COLUMN `dscPrimaryCount` `dscTotalMaxCount` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `dscCode`;
SQL
    );

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_Discount`
	CHANGE COLUMN `dscValidFrom` `dscValidFrom` DATETIME NULL DEFAULT NULL AFTER `dscCode`,
	CHANGE COLUMN `dscValidTo` `dscValidTo` DATETIME NULL DEFAULT NULL AFTER `dscValidFrom`;
SQL
    );

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_Discount`
	ADD COLUMN `dscTargetUserIDs` JSON NULL AFTER `dscPerUserMaxAmount`;
SQL
    );
		///JSON
    $this->alterColumn('tbl_{{MODULE}}_Accounting_Discount', 'dscTargetUserIDs', $this->json());

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_Discount`
	ADD COLUMN `dscTargetProductIDs` JSON NULL DEFAULT NULL AFTER `dscTargetUserIDs`,
	ADD COLUMN `dscTargetSaleableIDs` JSON NULL DEFAULT NULL AFTER `dscTargetProductIDs`,
	CHANGE COLUMN `dscSaleableBasedMultiplier` `dscSaleableBasedMultiplier` JSON NULL DEFAULT NULL AFTER `dscTargetSaleableIDs`,
	CHANGE COLUMN `dscAmount` `dscAmount` INT(10) UNSIGNED NOT NULL AFTER `dscSaleableBasedMultiplier`,
	CHANGE COLUMN `dscAmountType` `dscAmountType` CHAR(1) NOT NULL DEFAULT '%' COMMENT '%:Percent, $:Amount' COLLATE 'utf8mb4_unicode_ci' AFTER `dscAmount`,
	CHANGE COLUMN `dscMaxAmount` `dscMaxAmount` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `dscAmountType`;
SQL
    );
		///JSON
    $this->alterColumn('tbl_{{MODULE}}_Accounting_Discount', 'dscTargetProductIDs', $this->json());
    $this->alterColumn('tbl_{{MODULE}}_Accounting_Discount', 'dscTargetSaleableIDs', $this->json());

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_Discount`
	CHANGE COLUMN `dscTotalMaxAmount` `dscTotalMaxPrice` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `dscTotalMaxCount`,
	CHANGE COLUMN `dscPerUserMaxAmount` `dscPerUserMaxPrice` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `dscPerUserMaxCount`,
	CHANGE COLUMN `dscAmount` `dscAmount` DOUBLE UNSIGNED NOT NULL DEFAULT 0 AFTER `dscSaleableBasedMultiplier`,
	CHANGE COLUMN `dscMaxAmount` `dscMaxAmount` DOUBLE UNSIGNED NULL DEFAULT NULL AFTER `dscAmountType`,
	CHANGE COLUMN `dscTotalUsedAmount` `dscTotalUsedPrice` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `dscTotalUsedCount`;
SQL
    );

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_Discount`
	ADD COLUMN `dscType` CHAR(1) NOT NULL DEFAULT 'C' COMMENT 'S:System, C:Coupon' AFTER `dscName`;
SQL
    );

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_Discount`
	ADD COLUMN `dscCodeHasSerial` BIT NULL DEFAULT NULL AFTER `dscCode`,
	ADD COLUMN `dscCodeSerialCount` MEDIUMINT NULL DEFAULT NULL AFTER `dscCodeHasSerial`,
	ADD COLUMN `dscCodeSerialLength` TINYINT NULL DEFAULT NULL AFTER `dscCodeSerialCount`;
SQL
    );

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_Discount`
	CHANGE COLUMN `dscCode` `dscCodeString` VARCHAR(32) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci' AFTER `dscType`,
	DROP INDEX `dscCode_dscRemovedAt`,
	ADD UNIQUE INDEX `dscCodeString_dscRemovedAt` (`dscCodeString`, `dscRemovedAt`) USING BTREE;
SQL
    );

		$this->execute("DROP TRIGGER IF EXISTS trg_updatelog_tbl_{{MODULE}}_Accounting_Coupon;");
		$this->execute("DROP TRIGGER IF EXISTS trg_updatelog_tbl_{{MODULE}}_Accounting_Discount;");
		$this->execute(<<<SQL
CREATE TRIGGER trg_updatelog_tbl_{{MODULE}}_Accounting_Discount AFTER UPDATE ON tbl_{{MODULE}}_Accounting_Discount FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.dscAmount) != ISNULL(NEW.dscAmount) OR OLD.dscAmount != NEW.dscAmount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscAmount", IF(ISNULL(OLD.dscAmount), NULL, OLD.dscAmount))); END IF;
  IF ISNULL(OLD.dscAmountType) != ISNULL(NEW.dscAmountType) OR OLD.dscAmountType != NEW.dscAmountType THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscAmountType", IF(ISNULL(OLD.dscAmountType), NULL, OLD.dscAmountType))); END IF;
  IF ISNULL(OLD.dscCodeHasSerial) != ISNULL(NEW.dscCodeHasSerial) OR OLD.dscCodeHasSerial != NEW.dscCodeHasSerial THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscCodeHasSerial", IF(ISNULL(OLD.dscCodeHasSerial), NULL, OLD.dscCodeHasSerial))); END IF;
  IF ISNULL(OLD.dscCodeSerialCount) != ISNULL(NEW.dscCodeSerialCount) OR OLD.dscCodeSerialCount != NEW.dscCodeSerialCount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscCodeSerialCount", IF(ISNULL(OLD.dscCodeSerialCount), NULL, OLD.dscCodeSerialCount))); END IF;
  IF ISNULL(OLD.dscCodeSerialLength) != ISNULL(NEW.dscCodeSerialLength) OR OLD.dscCodeSerialLength != NEW.dscCodeSerialLength THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscCodeSerialLength", IF(ISNULL(OLD.dscCodeSerialLength), NULL, OLD.dscCodeSerialLength))); END IF;
  IF ISNULL(OLD.dscCodeString) != ISNULL(NEW.dscCodeString) OR OLD.dscCodeString != NEW.dscCodeString THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscCodeString", IF(ISNULL(OLD.dscCodeString), NULL, OLD.dscCodeString))); END IF;
  IF ISNULL(OLD.dscI18NData) != ISNULL(NEW.dscI18NData) OR OLD.dscI18NData != NEW.dscI18NData THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscI18NData", IF(ISNULL(OLD.dscI18NData), NULL, OLD.dscI18NData))); END IF;
  IF ISNULL(OLD.dscMaxAmount) != ISNULL(NEW.dscMaxAmount) OR OLD.dscMaxAmount != NEW.dscMaxAmount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscMaxAmount", IF(ISNULL(OLD.dscMaxAmount), NULL, OLD.dscMaxAmount))); END IF;
  IF ISNULL(OLD.dscName) != ISNULL(NEW.dscName) OR OLD.dscName != NEW.dscName THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscName", IF(ISNULL(OLD.dscName), NULL, OLD.dscName))); END IF;
  IF ISNULL(OLD.dscPerUserMaxCount) != ISNULL(NEW.dscPerUserMaxCount) OR OLD.dscPerUserMaxCount != NEW.dscPerUserMaxCount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscPerUserMaxCount", IF(ISNULL(OLD.dscPerUserMaxCount), NULL, OLD.dscPerUserMaxCount))); END IF;
  IF ISNULL(OLD.dscPerUserMaxPrice) != ISNULL(NEW.dscPerUserMaxPrice) OR OLD.dscPerUserMaxPrice != NEW.dscPerUserMaxPrice THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscPerUserMaxPrice", IF(ISNULL(OLD.dscPerUserMaxPrice), NULL, OLD.dscPerUserMaxPrice))); END IF;
  IF ISNULL(OLD.dscSaleableBasedMultiplier) != ISNULL(NEW.dscSaleableBasedMultiplier) OR OLD.dscSaleableBasedMultiplier != NEW.dscSaleableBasedMultiplier THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscSaleableBasedMultiplier", IF(ISNULL(OLD.dscSaleableBasedMultiplier), NULL, OLD.dscSaleableBasedMultiplier))); END IF;
  IF ISNULL(OLD.dscStatus) != ISNULL(NEW.dscStatus) OR OLD.dscStatus != NEW.dscStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscStatus", IF(ISNULL(OLD.dscStatus), NULL, OLD.dscStatus))); END IF;
  IF ISNULL(OLD.dscTargetProductIDs) != ISNULL(NEW.dscTargetProductIDs) OR OLD.dscTargetProductIDs != NEW.dscTargetProductIDs THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscTargetProductIDs", IF(ISNULL(OLD.dscTargetProductIDs), NULL, OLD.dscTargetProductIDs))); END IF;
  IF ISNULL(OLD.dscTargetSaleableIDs) != ISNULL(NEW.dscTargetSaleableIDs) OR OLD.dscTargetSaleableIDs != NEW.dscTargetSaleableIDs THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscTargetSaleableIDs", IF(ISNULL(OLD.dscTargetSaleableIDs), NULL, OLD.dscTargetSaleableIDs))); END IF;
  IF ISNULL(OLD.dscTargetUserIDs) != ISNULL(NEW.dscTargetUserIDs) OR OLD.dscTargetUserIDs != NEW.dscTargetUserIDs THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscTargetUserIDs", IF(ISNULL(OLD.dscTargetUserIDs), NULL, OLD.dscTargetUserIDs))); END IF;
  IF ISNULL(OLD.dscTotalMaxCount) != ISNULL(NEW.dscTotalMaxCount) OR OLD.dscTotalMaxCount != NEW.dscTotalMaxCount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscTotalMaxCount", IF(ISNULL(OLD.dscTotalMaxCount), NULL, OLD.dscTotalMaxCount))); END IF;
  IF ISNULL(OLD.dscTotalMaxPrice) != ISNULL(NEW.dscTotalMaxPrice) OR OLD.dscTotalMaxPrice != NEW.dscTotalMaxPrice THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscTotalMaxPrice", IF(ISNULL(OLD.dscTotalMaxPrice), NULL, OLD.dscTotalMaxPrice))); END IF;
  IF ISNULL(OLD.dscTotalUsedCount) != ISNULL(NEW.dscTotalUsedCount) OR OLD.dscTotalUsedCount != NEW.dscTotalUsedCount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscTotalUsedCount", IF(ISNULL(OLD.dscTotalUsedCount), NULL, OLD.dscTotalUsedCount))); END IF;
  IF ISNULL(OLD.dscTotalUsedPrice) != ISNULL(NEW.dscTotalUsedPrice) OR OLD.dscTotalUsedPrice != NEW.dscTotalUsedPrice THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscTotalUsedPrice", IF(ISNULL(OLD.dscTotalUsedPrice), NULL, OLD.dscTotalUsedPrice))); END IF;
  IF ISNULL(OLD.dscType) != ISNULL(NEW.dscType) OR OLD.dscType != NEW.dscType THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscType", IF(ISNULL(OLD.dscType), NULL, OLD.dscType))); END IF;
  IF ISNULL(OLD.dscUUID) != ISNULL(NEW.dscUUID) OR OLD.dscUUID != NEW.dscUUID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscUUID", IF(ISNULL(OLD.dscUUID), NULL, OLD.dscUUID))); END IF;
  IF ISNULL(OLD.dscValidFrom) != ISNULL(NEW.dscValidFrom) OR OLD.dscValidFrom != NEW.dscValidFrom THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscValidFrom", IF(ISNULL(OLD.dscValidFrom), NULL, OLD.dscValidFrom))); END IF;
  IF ISNULL(OLD.dscValidTo) != ISNULL(NEW.dscValidTo) OR OLD.dscValidTo != NEW.dscValidTo THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscValidTo", IF(ISNULL(OLD.dscValidTo), NULL, OLD.dscValidTo))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.dscUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
        SET atlBy     = NEW.dscUpdatedBy
          , atlAction = "UPDATE"
          , atlTarget = "tbl_{{MODULE}}_Accounting_Discount"
          , atlInfo   = JSON_OBJECT("dscID", OLD.dscID, "old", Changes);
  END IF;
END
SQL
    );

		$this->execute("DROP TRIGGER IF EXISTS trg_updatelog_tbl_{{MODULE}}_Accounting_UserAsset;");
		$this->execute(<<<SQL
CREATE TRIGGER trg_updatelog_tbl_{{MODULE}}_Accounting_UserAsset AFTER UPDATE ON tbl_{{MODULE}}_Accounting_UserAsset FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.uasActorID) != ISNULL(NEW.uasActorID) OR OLD.uasActorID != NEW.uasActorID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasActorID", IF(ISNULL(OLD.uasActorID), NULL, OLD.uasActorID))); END IF;
  IF ISNULL(OLD.uasBreakedAt) != ISNULL(NEW.uasBreakedAt) OR OLD.uasBreakedAt != NEW.uasBreakedAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasBreakedAt", IF(ISNULL(OLD.uasBreakedAt), NULL, OLD.uasBreakedAt))); END IF;
  IF ISNULL(OLD.uasDiscountAmount) != ISNULL(NEW.uasDiscountAmount) OR OLD.uasDiscountAmount != NEW.uasDiscountAmount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasDiscountAmount", IF(ISNULL(OLD.uasDiscountAmount), NULL, OLD.uasDiscountAmount))); END IF;
  IF ISNULL(OLD.uasDiscountID) != ISNULL(NEW.uasDiscountID) OR OLD.uasDiscountID != NEW.uasDiscountID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasDiscountID", IF(ISNULL(OLD.uasDiscountID), NULL, OLD.uasDiscountID))); END IF;
  IF ISNULL(OLD.uasDurationMinutes) != ISNULL(NEW.uasDurationMinutes) OR OLD.uasDurationMinutes != NEW.uasDurationMinutes THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasDurationMinutes", IF(ISNULL(OLD.uasDurationMinutes), NULL, OLD.uasDurationMinutes))); END IF;
  IF ISNULL(OLD.uasPrefered) != ISNULL(NEW.uasPrefered) OR OLD.uasPrefered != NEW.uasPrefered THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasPrefered", IF(ISNULL(OLD.uasPrefered), NULL, OLD.uasPrefered))); END IF;
  IF ISNULL(OLD.uasQty) != ISNULL(NEW.uasQty) OR OLD.uasQty != NEW.uasQty THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasQty", IF(ISNULL(OLD.uasQty), NULL, OLD.uasQty))); END IF;
  IF ISNULL(OLD.uasSaleableID) != ISNULL(NEW.uasSaleableID) OR OLD.uasSaleableID != NEW.uasSaleableID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasSaleableID", IF(ISNULL(OLD.uasSaleableID), NULL, OLD.uasSaleableID))); END IF;
  IF ISNULL(OLD.uasStatus) != ISNULL(NEW.uasStatus) OR OLD.uasStatus != NEW.uasStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasStatus", IF(ISNULL(OLD.uasStatus), NULL, OLD.uasStatus))); END IF;
  IF ISNULL(OLD.uasUUID) != ISNULL(NEW.uasUUID) OR OLD.uasUUID != NEW.uasUUID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasUUID", IF(ISNULL(OLD.uasUUID), NULL, OLD.uasUUID))); END IF;
  IF ISNULL(OLD.uasValidFromDate) != ISNULL(NEW.uasValidFromDate) OR OLD.uasValidFromDate != NEW.uasValidFromDate THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasValidFromDate", IF(ISNULL(OLD.uasValidFromDate), NULL, OLD.uasValidFromDate))); END IF;
  IF ISNULL(OLD.uasValidFromHour) != ISNULL(NEW.uasValidFromHour) OR OLD.uasValidFromHour != NEW.uasValidFromHour THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasValidFromHour", IF(ISNULL(OLD.uasValidFromHour), NULL, OLD.uasValidFromHour))); END IF;
  IF ISNULL(OLD.uasValidToDate) != ISNULL(NEW.uasValidToDate) OR OLD.uasValidToDate != NEW.uasValidToDate THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasValidToDate", IF(ISNULL(OLD.uasValidToDate), NULL, OLD.uasValidToDate))); END IF;
  IF ISNULL(OLD.uasValidToHour) != ISNULL(NEW.uasValidToHour) OR OLD.uasValidToHour != NEW.uasValidToHour THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasValidToHour", IF(ISNULL(OLD.uasValidToHour), NULL, OLD.uasValidToHour))); END IF;
  IF ISNULL(OLD.uasVoucherID) != ISNULL(NEW.uasVoucherID) OR OLD.uasVoucherID != NEW.uasVoucherID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasVoucherID", IF(ISNULL(OLD.uasVoucherID), NULL, OLD.uasVoucherID))); END IF;
  IF ISNULL(OLD.uasVoucherItemInfo) != ISNULL(NEW.uasVoucherItemInfo) OR OLD.uasVoucherItemInfo != NEW.uasVoucherItemInfo THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasVoucherItemInfo", IF(ISNULL(OLD.uasVoucherItemInfo), NULL, OLD.uasVoucherItemInfo))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.uasUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
        SET atlBy     = NEW.uasUpdatedBy
          , atlAction = "UPDATE"
          , atlTarget = "tbl_{{MODULE}}_Accounting_UserAsset"
          , atlInfo   = JSON_OBJECT("uasID", OLD.uasID, "old", Changes);
  END IF;
END
SQL
    );

	}

	public function safeDown()
	{
		echo "m231113_000000_accounting_rename_coupon_to_discount cannot be reverted.\n";
		return false;
	}

}
