<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\PerModuleMigration;

class m240102_000000_accounting_create_discount_sn_usage_referrer extends PerModuleMigration
{
	public function safeUp()
	{
		//discount
		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_Discount`
	CHANGE COLUMN `dscTotalMaxPrice` `dscTotalMaxPrice` DOUBLE UNSIGNED NULL DEFAULT NULL AFTER `dscTotalMaxCount`,
	CHANGE COLUMN `dscPerUserMaxPrice` `dscPerUserMaxPrice` DOUBLE UNSIGNED NULL DEFAULT NULL AFTER `dscPerUserMaxCount`,
	CHANGE COLUMN `dscAmount` `dscAmount` DOUBLE UNSIGNED NOT NULL AFTER `dscSaleableBasedMultiplier`,
	CHANGE COLUMN `dscTotalUsedCount` `dscTotalUsedCount` INT(10) UNSIGNED NOT NULL AFTER `dscMaxAmount`,
	CHANGE COLUMN `dscTotalUsedPrice` `dscTotalUsedPrice` DOUBLE UNSIGNED NOT NULL AFTER `dscTotalUsedCount`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_Discount`
	CHANGE COLUMN `dscType` `dscType` CHAR(1) NOT NULL DEFAULT 'C' COMMENT 'S:System, I:System Increase, C:Coupon' COLLATE 'utf8mb4_unicode_ci' AFTER `dscName`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_Discount`
	DROP INDEX `dscType`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_Discount`
	ADD INDEX `dscType` (`dscType`);
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_Discount`
	ADD INDEX `dscType_dscStatus` (`dscType`, `dscStatus`);
SQL
		);

		//serial
		$this->execute(<<<SQL
CREATE TABLE `tbl_{{MODULE}}_Accounting_DiscountSerial` (
	`dscsnID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`dscsnDiscountID` INT(10) UNSIGNED NOT NULL,
	`dscsnSN` VARCHAR(64) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	PRIMARY KEY (`dscsnID`) USING BTREE,
	UNIQUE INDEX `dscsnDiscountID_dscsnSN` (`dscsnDiscountID`, `dscsnSN`) USING BTREE,
	INDEX `FK_tbl_{{MODULE}}_Acc_DiscountSerial_tbl_{{MODULE}}_Accounting_Discount` (`dscsnDiscountID`) USING BTREE,
	CONSTRAINT `FK_tbl_{{MODULE}}_Acc_DiscountSerial_tbl_{{MODULE}}_Accounting_Discount` FOREIGN KEY (`dscsnDiscountID`) REFERENCES `tbl_{{MODULE}}_Accounting_Discount` (`dscID`) ON UPDATE NO ACTION ON DELETE CASCADE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;
SQL
		);

		//usage
		$this->execute(<<<SQL
CREATE TABLE `tbl_{{MODULE}}_Accounting_DiscountUsage` (
	`dscusgID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`dscusgUserID` BIGINT(20) UNSIGNED NOT NULL,
	`dscusgUserAssetID` BIGINT(20) UNSIGNED NOT NULL,
	`dscusgDiscountID` INT(10) UNSIGNED NOT NULL,
	`dscusgDiscountSerialID` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`dscusgAmount` DOUBLE UNSIGNED NOT NULL,
	`dscusgCreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`dscusgID`) USING BTREE,
	UNIQUE INDEX `dscusgDiscountID_dscusgDiscountSerialID_dscusgUserAssetID` (`dscusgDiscountID`, `dscusgDiscountSerialID`, `dscusgUserAssetID`) USING BTREE,
	INDEX `FK_tbl_{{MODULE}}_Acc_DiscountUsage_Serial` (`dscusgDiscountSerialID`) USING BTREE,
	INDEX `FK_tbl_{{MODULE}}_Acc_DiscountUsage_tbl_{{MODULE}}_Accounting_UserAsset` (`dscusgUserAssetID`) USING BTREE,
	CONSTRAINT `FK_tbl_{{MODULE}}_Acc_DiscountUsage_Serial` FOREIGN KEY (`dscusgDiscountSerialID`) REFERENCES `tbl_{{MODULE}}_Accounting_DiscountSerial` (`dscsnID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_tbl_{{MODULE}}_Acc_DiscountUsage_tbl_{{MODULE}}_Accounting_Discount` FOREIGN KEY (`dscusgDiscountID`) REFERENCES `tbl_{{MODULE}}_Accounting_Discount` (`dscID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_tbl_{{MODULE}}_Acc_DiscountUsage_tbl_{{MODULE}}_Accounting_UserAsset` FOREIGN KEY (`dscusgUserAssetID`) REFERENCES `tbl_{{MODULE}}_Accounting_UserAsset` (`uasID`) ON UPDATE NO ACTION ON DELETE CASCADE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;
SQL
		);

		$this->execute("DROP TRIGGER IF EXISTS trg_tbl_{{MODULE}}_Accounting_DiscountUsage_before_insert;");
		$this->execute(<<<SQL
CREATE TRIGGER `trg_tbl_{{MODULE}}_Accounting_DiscountUsage_before_insert` BEFORE INSERT ON `tbl_{{MODULE}}_Accounting_DiscountUsage` FOR EACH ROW BEGIN
	DECLARE pNewVoucherID BIGINT;
	DECLARE pCount INT;

	SELECT uasVoucherID
	  INTO pNewVoucherID
	  FROM tbl_{{MODULE}}_Accounting_UserAsset
	 WHERE uasID = NEW.dscusgUserAssetID;

	SET pCount = IF(EXISTS(SELECT *
		FROM tbl_{{MODULE}}_Accounting_DiscountUsage
		INNER JOIN tbl_{{MODULE}}_Accounting_UserAsset
		ON tbl_{{MODULE}}_Accounting_UserAsset.uasID = tbl_{{MODULE}}_Accounting_DiscountUsage.dscusgUserAssetID
		WHERE uasVoucherID = pNewVoucherID
		AND dscusgDiscountID = NEW.dscusgDiscountID
--		AND IFNULL(dscusgDiscountSerialID, 0) = IFNULL(NEW.dscusgDiscountSerialID, 0)
	), 0, 1);

	UPDATE tbl_{{MODULE}}_Accounting_Discount
	   SET dscTotalUsedCount = IFNULL(dscTotalUsedCount, 0) + pCount
	     , dscTotalUsedPrice = IFNULL(dscTotalUsedPrice, 0) + NEW.dscusgAmount
	 WHERE dscID = NEW.dscusgDiscountID;
END
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_Discount`
	ADD COLUMN `dscReferrers` JSON NULL DEFAULT NULL AFTER `dscTargetSaleableIDs`;
SQL
	);
		$this->alterColumn('tbl_{{MODULE}}_Accounting_Discount', 'dscReferrers', $this->json());

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
  IF ISNULL(OLD.dscReferrers) != ISNULL(NEW.dscReferrers) OR OLD.dscReferrers != NEW.dscReferrers THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscReferrers", IF(ISNULL(OLD.dscReferrers), NULL, OLD.dscReferrers))); END IF;
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

		//asset
		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_UserAsset`
	DROP FOREIGN KEY `FK_tbl_{{MODULE}}_Acc_UserAsset_tbl_{{MODULE}}_Acc_Discount`,
	DROP INDEX `FK_tbl_{{MODULE}}_Acc_UserAsset_tbl_{{MODULE}}_Acc_Discount`,
	DROP INDEX `uas_invID`,
	ADD INDEX `uasVoucherID` (`uasVoucherID`) USING BTREE;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_UserAsset`
	CHANGE COLUMN `uasDiscountID` `uasDiscountID_DEL` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `uasVoucherItemInfo`,
	CHANGE COLUMN `uasDiscountAmount` `uasDiscountAmount_DEL` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `uasDiscountID_DEL`;
SQL
		);

		$this->execute("DROP TRIGGER IF EXISTS trg_updatelog_tbl_{{MODULE}}_Accounting_UserAsset;");
		$this->execute(<<<SQL
CREATE TRIGGER trg_updatelog_tbl_{{MODULE}}_Accounting_UserAsset AFTER UPDATE ON tbl_{{MODULE}}_Accounting_UserAsset FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.uasActorID) != ISNULL(NEW.uasActorID) OR OLD.uasActorID != NEW.uasActorID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasActorID", IF(ISNULL(OLD.uasActorID), NULL, OLD.uasActorID))); END IF;
  IF ISNULL(OLD.uasBreakedAt) != ISNULL(NEW.uasBreakedAt) OR OLD.uasBreakedAt != NEW.uasBreakedAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasBreakedAt", IF(ISNULL(OLD.uasBreakedAt), NULL, OLD.uasBreakedAt))); END IF;
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
  IF ISNULL(OLD.uasVoucherItemInfo_OLD) != ISNULL(NEW.uasVoucherItemInfo_OLD) OR OLD.uasVoucherItemInfo_OLD != NEW.uasVoucherItemInfo_OLD THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasVoucherItemInfo_OLD", IF(ISNULL(OLD.uasVoucherItemInfo_OLD), NULL, OLD.uasVoucherItemInfo_OLD))); END IF;

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

		$this->execute("DROP TRIGGER IF EXISTS trg_updatelog_tbl_{{MODULE}}_Accounting_DiscountSerial;");
		$this->execute(<<<SQL
CREATE TRIGGER trg_updatelog_tbl_{{MODULE}}_Accounting_DiscountSerial AFTER UPDATE ON tbl_{{MODULE}}_Accounting_DiscountSerial FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.dscsnDiscountID) != ISNULL(NEW.dscsnDiscountID) OR OLD.dscsnDiscountID != NEW.dscsnDiscountID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscsnDiscountID", IF(ISNULL(OLD.dscsnDiscountID), NULL, OLD.dscsnDiscountID))); END IF;
  IF ISNULL(OLD.dscsnSN) != ISNULL(NEW.dscsnSN) OR OLD.dscsnSN != NEW.dscsnSN THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscsnSN", IF(ISNULL(OLD.dscsnSN), NULL, OLD.dscsnSN))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.dscsnUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
        SET atlBy     = NULL
          , atlAction = "UPDATE"
          , atlTarget = "tbl_{{MODULE}}_Accounting_DiscountSerial"
          , atlInfo   = JSON_OBJECT("dscsnID", OLD.dscsnID, "old", Changes);
  END IF;
END
SQL
		);

		$this->execute("DROP TRIGGER IF EXISTS trg_updatelog_tbl_{{MODULE}}_Accounting_DiscountUsage;");
		$this->execute(<<<SQL
CREATE TRIGGER trg_updatelog_tbl_{{MODULE}}_Accounting_DiscountUsage AFTER UPDATE ON tbl_{{MODULE}}_Accounting_DiscountUsage FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.dscusgAmount) != ISNULL(NEW.dscusgAmount) OR OLD.dscusgAmount != NEW.dscusgAmount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscusgAmount", IF(ISNULL(OLD.dscusgAmount), NULL, OLD.dscusgAmount))); END IF;
  IF ISNULL(OLD.dscusgDiscountID) != ISNULL(NEW.dscusgDiscountID) OR OLD.dscusgDiscountID != NEW.dscusgDiscountID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscusgDiscountID", IF(ISNULL(OLD.dscusgDiscountID), NULL, OLD.dscusgDiscountID))); END IF;
  IF ISNULL(OLD.dscusgDiscountSerialID) != ISNULL(NEW.dscusgDiscountSerialID) OR OLD.dscusgDiscountSerialID != NEW.dscusgDiscountSerialID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscusgDiscountSerialID", IF(ISNULL(OLD.dscusgDiscountSerialID), NULL, OLD.dscusgDiscountSerialID))); END IF;
  IF ISNULL(OLD.dscusgUserAssetID) != ISNULL(NEW.dscusgUserAssetID) OR OLD.dscusgUserAssetID != NEW.dscusgUserAssetID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscusgUserAssetID", IF(ISNULL(OLD.dscusgUserAssetID), NULL, OLD.dscusgUserAssetID))); END IF;
  IF ISNULL(OLD.dscusgUserID) != ISNULL(NEW.dscusgUserID) OR OLD.dscusgUserID != NEW.dscusgUserID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dscusgUserID", IF(ISNULL(OLD.dscusgUserID), NULL, OLD.dscusgUserID))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.dscusgUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
        SET atlBy     = NULL
          , atlAction = "UPDATE"
          , atlTarget = "tbl_{{MODULE}}_Accounting_DiscountUsage"
          , atlInfo   = JSON_OBJECT("dscusgID", OLD.dscusgID, "old", Changes);
  END IF;
END
SQL
    );

	}

	public function safeDown()
	{
		echo "m240102_000000_accounting_create_discount_sn_usage_referrer cannot be reverted.\n";
		return false;
	}

}
