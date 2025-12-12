<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\PerModuleMigration;

class m230910_000000_accounting_create_accounting extends PerModuleMigration
{
  public function safeUp()
  {
    $this->execute(<<<SQL
CREATE TABLE `tbl_{{MODULE}}_Accounting_Unit` (
	`untID` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
	`untUUID` VARCHAR(38) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`untName` VARCHAR(64) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`untI18NData` JSON NULL DEFAULT NULL,
	`untCreatedAt` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
	`untCreatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`untUpdatedAt` DATETIME NULL DEFAULT NULL,
	`untUpdatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`untRemovedAt` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`untRemovedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`untID`) USING BTREE,
	UNIQUE INDEX `untUUID` (`untUUID`) USING BTREE,
	UNIQUE INDEX `untName` (`untName`) USING BTREE,
	INDEX `untCreatedAt` (`untCreatedAt`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;
SQL
    );
    $this->alterColumn('tbl_{{MODULE}}_Accounting_Unit', 'untI18NData', $this->json());

    $this->execute(<<<SQL
CREATE TABLE `tbl_{{MODULE}}_Accounting_Coupon` (
	`cpnID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`cpnUUID` VARCHAR(38) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`cpnCode` VARCHAR(32) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`cpnName` VARCHAR(64) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`cpnPrimaryCount` INT(10) UNSIGNED NOT NULL,
	`cpnTotalMaxAmount` INT(10) UNSIGNED NOT NULL,
	`cpnPerUserMaxCount` INT(10) UNSIGNED NULL DEFAULT NULL,
	`cpnPerUserMaxAmount` INT(10) UNSIGNED NULL DEFAULT NULL,
	`cpnValidFrom` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`cpnValidTo` DATETIME NULL DEFAULT NULL,
	`cpnAmount` INT(10) UNSIGNED NOT NULL,
	`cpnAmountType` CHAR(1) NOT NULL DEFAULT '%' COMMENT '%:Percent, $:Amount, Z:Free' COLLATE 'utf8mb4_unicode_ci',
	`cpnMaxAmount` INT(10) UNSIGNED NULL DEFAULT NULL,
	`cpnSaleableBasedMultiplier` JSON NULL DEFAULT NULL,
	`cpnTotalUsedCount` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`cpnTotalUsedAmount` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`cpnI18NData` JSON NULL DEFAULT NULL,
	`cpnStatus` CHAR(1) NOT NULL DEFAULT 'A' COMMENT 'A:Active, R:Removed' COLLATE 'utf8mb4_unicode_ci',
	`cpnCreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`cpnCreatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`cpnUpdatedAt` DATETIME NULL DEFAULT NULL,
	`cpnUpdatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`cpnRemovedAt` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`cpnRemovedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`cpnID`) USING BTREE,
	UNIQUE INDEX `cpnUUID` (`cpnUUID`) USING BTREE,
	UNIQUE INDEX `cpnCode_cpnRemovedAt` (`cpnCode`, `cpnRemovedAt`) USING BTREE,
	INDEX `cpnValidTo` (`cpnValidTo`) USING BTREE,
	INDEX `cpnCreatedBy` (`cpnCreatedBy`) USING BTREE,
	INDEX `cpnCreatedAt` (`cpnCreatedAt`) USING BTREE,
	INDEX `cpnUpdatedBy` (`cpnUpdatedBy`) USING BTREE,
	INDEX `cpnStatus` (`cpnStatus`) USING BTREE,
	INDEX `cpnValidFrom` (`cpnValidFrom`) USING BTREE,
	INDEX `cpnType` (`cpnAmountType`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;
SQL
    );
    $this->alterColumn('tbl_{{MODULE}}_Accounting_Coupon', 'cpnSaleableBasedMultiplier', $this->json());
    $this->alterColumn('tbl_{{MODULE}}_Accounting_Coupon', 'cpnI18NData', $this->json());

    $this->execute(<<<SQL
CREATE TABLE `tbl_{{MODULE}}_Accounting_Product` (
	`prdID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`prdUUID` VARCHAR(38) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`prdCode` VARCHAR(38) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`prdName` VARCHAR(64) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`prdDesc` VARCHAR(128) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`prdType` CHAR(1) NOT NULL DEFAULT 'P' COMMENT 'P:Physical, D:Digital' COLLATE 'utf8mb4_unicode_ci',
	`prdValidFromDate` DATETIME NULL DEFAULT NULL,
	`prdValidToDate` DATETIME NULL DEFAULT NULL,
	`prdValidFromHour` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
	`prdValidToHour` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
	`prdDurationMinutes` MEDIUMINT(7) UNSIGNED NULL DEFAULT NULL,
	`prdStartAtFirstUse` BIT(1) NOT NULL DEFAULT 0,
	`prdPrivs` JSON NULL DEFAULT NULL,
	`prdVAT` DOUBLE UNSIGNED NULL DEFAULT NULL,
	`prdUnitID` SMALLINT(5) UNSIGNED NOT NULL,
	`prdQtyIsDecimal` BIT(1) NOT NULL DEFAULT 0,
	`prdInStockQty` DOUBLE UNSIGNED NULL DEFAULT NULL,
	`prdOrderedQty` DOUBLE UNSIGNED NULL DEFAULT NULL,
	`prdReturnedQty` DOUBLE UNSIGNED NULL DEFAULT NULL,
	`prdI18NData` JSON NULL DEFAULT NULL,
	`prdStatus` CHAR(1) NOT NULL DEFAULT 'A' COMMENT 'A:Active, R:Removed' COLLATE 'utf8mb4_unicode_ci',
	`prdCreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`prdCreatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`prdUpdatedAt` DATETIME NULL DEFAULT NULL,
	`prdUpdatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`prdRemovedAt` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`prdRemovedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`prdID`) USING BTREE,
	UNIQUE INDEX `prdUUID` (`prdUUID`) USING BTREE,
	UNIQUE INDEX `prdCode_prdRemovedAt` (`prdCode`, `prdRemovedAt`) USING BTREE,
	INDEX `prdCreatedAt` (`prdCreatedAt`) USING BTREE,
	INDEX `prdStatus` (`prdStatus`) USING BTREE,
	INDEX `prdValidFrom` (`prdValidFromDate`) USING BTREE,
	INDEX `prdValidTo` (`prdValidToDate`) USING BTREE,
	INDEX `prdCreatedBy` (`prdCreatedBy`) USING BTREE,
	INDEX `prdUpdatedBy` (`prdUpdatedBy`) USING BTREE,
	INDEX `prdValidFromTime` (`prdValidFromHour`) USING BTREE,
	INDEX `prdValidToTime` (`prdValidToHour`) USING BTREE,
	INDEX `FK_tbl_{{MODULE}}_Acc_Product_tbl_Unit` (`prdUnitID`) USING BTREE,
	CONSTRAINT `FK_tbl_{{MODULE}}_Acc_Product_tbl_{{MODULE}}_Acc_Unit` FOREIGN KEY (`prdUnitID`) REFERENCES `tbl_{{MODULE}}_Accounting_Unit` (`untID`) ON UPDATE CASCADE ON DELETE NO ACTION
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;
SQL
    );
    $this->alterColumn('tbl_{{MODULE}}_Accounting_Product', 'prdPrivs', $this->json());
    $this->alterColumn('tbl_{{MODULE}}_Accounting_Product', 'prdI18NData', $this->json());

    $this->execute(<<<SQL
CREATE TABLE `tbl_{{MODULE}}_Accounting_Saleable` (
	`slbID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`slbUUID` VARCHAR(38) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`slbProductID` INT(10) UNSIGNED NOT NULL,
	`slbCode` VARCHAR(38) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`slbName` VARCHAR(64) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`slbDesc` VARCHAR(128) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`slbAvailableFromDate` DATETIME NULL DEFAULT NULL,
	`slbAvailableToDate` DATETIME NULL DEFAULT NULL,
	`slbPrivs` JSON NULL DEFAULT NULL,
	`slbBasePrice` DOUBLE UNSIGNED NOT NULL,
	`slbAdditives` JSON NULL DEFAULT NULL,
	`slbProductCount` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'what is this?',
	`slbMaxSaleCountPerUser` INT(10) UNSIGNED NULL DEFAULT NULL,
	`slbInStockQty` DOUBLE UNSIGNED NULL DEFAULT NULL,
	`slbOrderedQty` DOUBLE UNSIGNED NULL DEFAULT NULL,
	`slbReturnedQty` DOUBLE UNSIGNED NULL DEFAULT NULL,
	`slbVoucherTemplate` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`slbI18NData` JSON NULL DEFAULT NULL,
	`slbStatus` CHAR(1) NOT NULL DEFAULT 'A' COMMENT 'A:Active, R:Removed' COLLATE 'utf8mb4_unicode_ci',
	`slbCreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`slbCreatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`slbUpdatedAt` DATETIME NULL DEFAULT NULL,
	`slbUpdatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`slbRemovedAt` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`slbRemovedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`slbID`) USING BTREE,
	UNIQUE INDEX `slbUUID` (`slbUUID`) USING BTREE,
	UNIQUE INDEX `slbCode_slbRemovedAt` (`slbCode`, `slbRemovedAt`) USING BTREE,
	INDEX `slbCreatedAt` (`slbCreatedAt`) USING BTREE,
	INDEX `slbStatus` (`slbStatus`) USING BTREE,
	INDEX `slbCreatedBy` (`slbCreatedBy`) USING BTREE,
	INDEX `slbUpdatedBy` (`slbUpdatedBy`) USING BTREE,
	INDEX `slbAvailableToDate` (`slbAvailableToDate`) USING BTREE,
	INDEX `slbAvailableFromDate` (`slbAvailableFromDate`) USING BTREE,
	INDEX `FK_tbl_{{MODULE}}_Acc_Saleable_tbl_Product` (`slbProductID`) USING BTREE,
	CONSTRAINT `FK_tbl_{{MODULE}}_Acc_Saleable_tbl_Product` FOREIGN KEY (`slbProductID`) REFERENCES `tbl_{{MODULE}}_Accounting_Product` (`prdID`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;
SQL
    );
    $this->alterColumn('tbl_{{MODULE}}_Accounting_Saleable', 'slbPrivs', $this->json());
    $this->alterColumn('tbl_{{MODULE}}_Accounting_Saleable', 'slbAdditives', $this->json());
    $this->alterColumn('tbl_{{MODULE}}_Accounting_Saleable', 'slbI18NData', $this->json());

    $this->execute(<<<SQL
CREATE TABLE `tbl_{{MODULE}}_Accounting_SaleableFile` (
	`slfID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`slfSaleableID` INT(10) UNSIGNED NOT NULL,
	`slfName` VARCHAR(64) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`slfDesc` VARCHAR(512) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`slfMimeTypes` VARCHAR(256) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`slfExtensions` VARCHAR(128) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`slfMinSize` BIGINT(20) UNSIGNED NULL DEFAULT '0',
	`slfMaxSize` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`slfMinCount` SMALLINT(5) UNSIGNED NULL DEFAULT '0',
	`slfMaxCount` SMALLINT(5) UNSIGNED NULL DEFAULT NULL,
	`slfCreatedAt` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
	`slfCreatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`slfUpdatedAt` DATETIME NULL DEFAULT NULL,
	`slfUpdatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`slfRemovedAt` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`slfRemovedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`slfID`) USING BTREE,
	INDEX `FK_tbl_{{MODULE}}_Acc_SaleableFile_tbl_Saleable` (`slfSaleableID`) USING BTREE,
	INDEX `slfCreatedAt` (`slfCreatedAt`) USING BTREE,
	CONSTRAINT `FK_tbl_{{MODULE}}_Acc_SaleableFile_tbl_Saleable` FOREIGN KEY (`slfSaleableID`) REFERENCES `tbl_{{MODULE}}_Accounting_Saleable` (`slbID`) ON UPDATE NO ACTION ON DELETE CASCADE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;
SQL
    );

    $this->execute(<<<SQL
CREATE TABLE `tbl_{{MODULE}}_Accounting_UserAsset` (
	`uasID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`uasUUID` VARCHAR(38) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`uasActorID` BIGINT(20) UNSIGNED NOT NULL,
	`uasSaleableID` INT(10) UNSIGNED NOT NULL,
	`uasQty` DOUBLE UNSIGNED NOT NULL,
	`uasVoucherID` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`uasVoucherItemInfo` JSON NULL DEFAULT NULL,
	`uasCouponID` INT(10) UNSIGNED NULL DEFAULT NULL,
	`uasDiscountAmount` INT(10) UNSIGNED NULL DEFAULT NULL,
	`uasPrefered` BIT(1) NOT NULL DEFAULT 0,
	`uasValidFromDate` DATETIME NULL DEFAULT NULL,
	`uasValidToDate` DATETIME NULL DEFAULT NULL,
	`uasValidFromHour` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
	`uasValidToHour` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
	`uasDurationMinutes` MEDIUMINT(7) UNSIGNED NULL DEFAULT NULL,
	`uasBreakedAt` DATETIME NULL DEFAULT NULL,
	`uasStatus` CHAR(1) NOT NULL DEFAULT 'P' COMMENT 'P:Pending, A:Active, R:Removed, B:Blocked' COLLATE 'utf8mb4_unicode_ci',
	`uasCreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`uasCreatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`uasUpdatedAt` DATETIME NULL DEFAULT NULL,
	`uasUpdatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`uasRemovedAt` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`uasRemovedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`uasID`) USING BTREE,
	UNIQUE INDEX `uasUUID` (`uasUUID`) USING BTREE,
	INDEX `uasStatus` (`uasStatus`) USING BTREE,
	INDEX `uasUpdatedBy` (`uasUpdatedBy`) USING BTREE,
	INDEX `uas_invID` (`uasVoucherID`) USING BTREE,
	INDEX `uasActorID` (`uasActorID`) USING BTREE,
	INDEX `FK_tbl_{{MODULE}}_Acc_UserAsset_tbl_Saleable` (`uasSaleableID`) USING BTREE,
	INDEX `FK_tbl_{{MODULE}}_Acc_UserAsset_tbl_Coupon` (`uasCouponID`) USING BTREE,
	INDEX `uasCreatedAt` (`uasCreatedAt`) USING BTREE,
	CONSTRAINT `FK_tbl_{{MODULE}}_Acc_UserAsset_tbl_Coupon` FOREIGN KEY (`uasCouponID`) REFERENCES `tbl_{{MODULE}}_Accounting_Coupon` (`cpnID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_tbl_{{MODULE}}_Acc_UserAsset_tbl_Saleable` FOREIGN KEY (`uasSaleableID`) REFERENCES `tbl_{{MODULE}}_Accounting_Saleable` (`slbID`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;
SQL
    );
    $this->alterColumn('tbl_{{MODULE}}_Accounting_UserAsset', 'uasVoucherItemInfo', $this->json());

    $this->execute(<<<SQL
CREATE TABLE `tbl_{{MODULE}}_Accounting_UserAsset_File` (
	`uasuflID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`uasuflUserAssetID` BIGINT(20) UNSIGNED NOT NULL,
	`uasuflSaleableFileID` BIGINT(20) UNSIGNED NOT NULL,
	`uasuflFileID` BIGINT(20) UNSIGNED NOT NULL,
	`uasuflCreatedAt` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
	`uasuflCreatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`uasuflUpdatedAt` DATETIME NULL DEFAULT NULL,
	`uasuflUpdatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`uasuflRemovedAt` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`uasuflRemovedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`uasuflID`) USING BTREE,
	INDEX `FK_tbl_{{MODULE}}_Acc_UserAsset_File_tbl_UserAsset` (`uasuflUserAssetID`) USING BTREE,
	INDEX `FK_tbl_{{MODULE}}_Acc_UserAsset_File_tbl_AAA_UploadFile` (`uasuflFileID`) USING BTREE,
	INDEX `FK_tbl_{{MODULE}}_Acc_UserAsset_File_tbl_SaleableFile` (`uasuflSaleableFileID`) USING BTREE,
	CONSTRAINT `FK_tbl_{{MODULE}}_Acc_UserAsset_File_tbl_AAA_UploadFile` FOREIGN KEY (`uasuflFileID`) REFERENCES `tbl_AAA_UploadFile` (`uflID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_tbl_{{MODULE}}_Acc_UserAsset_File_tbl_SaleableFile` FOREIGN KEY (`uasuflSaleableFileID`) REFERENCES `tbl_{{MODULE}}_Accounting_SaleableFile` (`slfID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_tbl_{{MODULE}}_Acc_UserAsset_File_tbl_UserAsset` FOREIGN KEY (`uasuflUserAssetID`) REFERENCES `tbl_{{MODULE}}_Accounting_UserAsset` (`uasID`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;

SQL
    );

/*
    $this->execute(<<<SQL
CREATE TABLE `tbl_{{MODULE}}_Accounting_AssetUsage` (
	`usgUserAssetID` BIGINT(20) UNSIGNED NOT NULL,
	`usgResolution` CHAR(1) NOT NULL DEFAULT 'T' COMMENT 'T:Total, Y:Year, M:Month, D:Day, H:Hour, I:Minute' COLLATE 'utf8mb4_unicode_ci',
	`usgLastDateTime` DATETIME NOT NULL,
	`usgKey` VARCHAR(128) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`usgUniqueMD5` CHAR(32) AS (md5(concat_ws('X',`usgUserAssetID`,`usgResolution`,(case `usgResolution` when 'Y' then date_format(`usgLastDateTime`,'%Y') when 'M' then date_format(`usgLastDateTime`,'%Y-%m') when 'D' then date_format(`usgLastDateTime`,'%Y-%m-%d') when 'H' then date_format(`usgLastDateTime`,'%Y-%m-%d %H') when 'I' then date_format(`usgLastDateTime`,'%Y-%m-%d %H:%i') else 'TOTAL' end),ifnull(`usgKey`,'[[NO-KEY]]')))) virtual,
	`usgCreatedAt` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
	`usgCreatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`usgUpdatedAt` DATETIME NULL DEFAULT NULL,
	`usgUpdatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`usgRemovedAt` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`usgRemovedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	UNIQUE INDEX `usgUniqueMD5` (`usgUniqueMD5`) USING BTREE,
	INDEX `usgUserAssetID` (`usgUserAssetID`) USING BTREE,
	INDEX `usgCreatedAt` (`usgCreatedAt`) USING BTREE,
	CONSTRAINT `FK_tbl_{{MODULE}}_Acc_AssetUsage_tbl_UserAsset` FOREIGN KEY (`usgUserAssetID`) REFERENCES `tbl_{{MODULE}}_Accounting_UserAsset` (`uasID`) ON UPDATE CASCADE ON DELETE NO ACTION
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;
SQL
    );
*/

    $this->execute(<<<SQL
INSERT IGNORE INTO tbl_{{MODULE}}_Accounting_Unit(untID, untUUID, untName, untI18NData)
    VALUES
        (1, UUID(), 'سال', '{"en":{"untName":"Year"}}'),
        (2, UUID(), 'دفعه', '{"en":{"untName":"Times"}}')
    ;
SQL
    );

    $this->execute(<<<SQL
CREATE TRIGGER `trg_tbl_{{MODULE}}_Accounting_Saleable_before_insert` BEFORE INSERT ON `tbl_{{MODULE}}_Accounting_Saleable` FOR EACH ROW BEGIN
	IF NEW.slbCode IS NULL THEN
		SET NEW.slbCode = UUID();
	END IF;

	IF NEW.slbAvailableFromDate IS NULL THEN
		SET NEW.slbAvailableFromDate = NOW();
	END IF;
END
SQL
    );

    $this->execute("DROP TRIGGER IF EXISTS trg_updatelog_tbl_{{MODULE}}_Accounting_Unit;");
    $this->execute(<<<SQL
CREATE TRIGGER trg_updatelog_tbl_{{MODULE}}_Accounting_Unit AFTER UPDATE ON tbl_{{MODULE}}_Accounting_Unit FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.untUUID) != ISNULL(NEW.untUUID) OR OLD.untUUID != NEW.untUUID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("untUUID", IF(ISNULL(OLD.untUUID), NULL, OLD.untUUID))); END IF;
  IF ISNULL(OLD.untName) != ISNULL(NEW.untName) OR OLD.untName != NEW.untName THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("untName", IF(ISNULL(OLD.untName), NULL, OLD.untName))); END IF;
  IF ISNULL(OLD.untI18NData) != ISNULL(NEW.untI18NData) OR OLD.untI18NData != NEW.untI18NData THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("untI18NData", IF(ISNULL(OLD.untI18NData), NULL, OLD.untI18NData))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.untUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
        SET atlBy     = NEW.untUpdatedBy
          , atlAction = "UPDATE"
          , atlTarget = "tbl_{{MODULE}}_Accounting_Unit"
          , atlInfo   = JSON_OBJECT("untID", OLD.untID, "old", Changes);
  END IF;
END
SQL
    );

    $this->execute("DROP TRIGGER IF EXISTS trg_updatelog_tbl_{{MODULE}}_Accounting_Coupon;");
    $this->execute(<<<SQL
CREATE TRIGGER trg_updatelog_tbl_{{MODULE}}_Accounting_Coupon AFTER UPDATE ON tbl_{{MODULE}}_Accounting_Coupon FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.cpnUUID) != ISNULL(NEW.cpnUUID) OR OLD.cpnUUID != NEW.cpnUUID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("cpnUUID", IF(ISNULL(OLD.cpnUUID), NULL, OLD.cpnUUID))); END IF;
  IF ISNULL(OLD.cpnCode) != ISNULL(NEW.cpnCode) OR OLD.cpnCode != NEW.cpnCode THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("cpnCode", IF(ISNULL(OLD.cpnCode), NULL, OLD.cpnCode))); END IF;
  IF ISNULL(OLD.cpnName) != ISNULL(NEW.cpnName) OR OLD.cpnName != NEW.cpnName THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("cpnName", IF(ISNULL(OLD.cpnName), NULL, OLD.cpnName))); END IF;
  IF ISNULL(OLD.cpnPrimaryCount) != ISNULL(NEW.cpnPrimaryCount) OR OLD.cpnPrimaryCount != NEW.cpnPrimaryCount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("cpnPrimaryCount", IF(ISNULL(OLD.cpnPrimaryCount), NULL, OLD.cpnPrimaryCount))); END IF;
  IF ISNULL(OLD.cpnTotalMaxAmount) != ISNULL(NEW.cpnTotalMaxAmount) OR OLD.cpnTotalMaxAmount != NEW.cpnTotalMaxAmount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("cpnTotalMaxAmount", IF(ISNULL(OLD.cpnTotalMaxAmount), NULL, OLD.cpnTotalMaxAmount))); END IF;
  IF ISNULL(OLD.cpnPerUserMaxCount) != ISNULL(NEW.cpnPerUserMaxCount) OR OLD.cpnPerUserMaxCount != NEW.cpnPerUserMaxCount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("cpnPerUserMaxCount", IF(ISNULL(OLD.cpnPerUserMaxCount), NULL, OLD.cpnPerUserMaxCount))); END IF;
  IF ISNULL(OLD.cpnPerUserMaxAmount) != ISNULL(NEW.cpnPerUserMaxAmount) OR OLD.cpnPerUserMaxAmount != NEW.cpnPerUserMaxAmount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("cpnPerUserMaxAmount", IF(ISNULL(OLD.cpnPerUserMaxAmount), NULL, OLD.cpnPerUserMaxAmount))); END IF;
  IF ISNULL(OLD.cpnValidFrom) != ISNULL(NEW.cpnValidFrom) OR OLD.cpnValidFrom != NEW.cpnValidFrom THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("cpnValidFrom", IF(ISNULL(OLD.cpnValidFrom), NULL, OLD.cpnValidFrom))); END IF;
  IF ISNULL(OLD.cpnValidTo) != ISNULL(NEW.cpnValidTo) OR OLD.cpnValidTo != NEW.cpnValidTo THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("cpnValidTo", IF(ISNULL(OLD.cpnValidTo), NULL, OLD.cpnValidTo))); END IF;
  IF ISNULL(OLD.cpnAmount) != ISNULL(NEW.cpnAmount) OR OLD.cpnAmount != NEW.cpnAmount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("cpnAmount", IF(ISNULL(OLD.cpnAmount), NULL, OLD.cpnAmount))); END IF;
  IF ISNULL(OLD.cpnAmountType) != ISNULL(NEW.cpnAmountType) OR OLD.cpnAmountType != NEW.cpnAmountType THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("cpnAmountType", IF(ISNULL(OLD.cpnAmountType), NULL, OLD.cpnAmountType))); END IF;
  IF ISNULL(OLD.cpnMaxAmount) != ISNULL(NEW.cpnMaxAmount) OR OLD.cpnMaxAmount != NEW.cpnMaxAmount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("cpnMaxAmount", IF(ISNULL(OLD.cpnMaxAmount), NULL, OLD.cpnMaxAmount))); END IF;
  IF ISNULL(OLD.cpnSaleableBasedMultiplier) != ISNULL(NEW.cpnSaleableBasedMultiplier) OR OLD.cpnSaleableBasedMultiplier != NEW.cpnSaleableBasedMultiplier THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("cpnSaleableBasedMultiplier", IF(ISNULL(OLD.cpnSaleableBasedMultiplier), NULL, OLD.cpnSaleableBasedMultiplier))); END IF;
  IF ISNULL(OLD.cpnTotalUsedCount) != ISNULL(NEW.cpnTotalUsedCount) OR OLD.cpnTotalUsedCount != NEW.cpnTotalUsedCount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("cpnTotalUsedCount", IF(ISNULL(OLD.cpnTotalUsedCount), NULL, OLD.cpnTotalUsedCount))); END IF;
  IF ISNULL(OLD.cpnTotalUsedAmount) != ISNULL(NEW.cpnTotalUsedAmount) OR OLD.cpnTotalUsedAmount != NEW.cpnTotalUsedAmount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("cpnTotalUsedAmount", IF(ISNULL(OLD.cpnTotalUsedAmount), NULL, OLD.cpnTotalUsedAmount))); END IF;
  IF ISNULL(OLD.cpnI18NData) != ISNULL(NEW.cpnI18NData) OR OLD.cpnI18NData != NEW.cpnI18NData THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("cpnI18NData", IF(ISNULL(OLD.cpnI18NData), NULL, OLD.cpnI18NData))); END IF;
  IF ISNULL(OLD.cpnStatus) != ISNULL(NEW.cpnStatus) OR OLD.cpnStatus != NEW.cpnStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("cpnStatus", IF(ISNULL(OLD.cpnStatus), NULL, OLD.cpnStatus))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.cpnUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
        SET atlBy     = NEW.cpnUpdatedBy
          , atlAction = "UPDATE"
          , atlTarget = "tbl_{{MODULE}}_Accounting_Coupon"
          , atlInfo   = JSON_OBJECT("cpnID", OLD.cpnID, "old", Changes);
  END IF;
END
SQL
    );

    $this->execute("DROP TRIGGER IF EXISTS trg_updatelog_tbl_{{MODULE}}_Accounting_Product;");
    $this->execute(<<<SQL
CREATE TRIGGER trg_updatelog_tbl_{{MODULE}}_Accounting_Product AFTER UPDATE ON tbl_{{MODULE}}_Accounting_Product FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.prdUUID) != ISNULL(NEW.prdUUID) OR OLD.prdUUID != NEW.prdUUID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("prdUUID", IF(ISNULL(OLD.prdUUID), NULL, OLD.prdUUID))); END IF;
  IF ISNULL(OLD.prdCode) != ISNULL(NEW.prdCode) OR OLD.prdCode != NEW.prdCode THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("prdCode", IF(ISNULL(OLD.prdCode), NULL, OLD.prdCode))); END IF;
  IF ISNULL(OLD.prdName) != ISNULL(NEW.prdName) OR OLD.prdName != NEW.prdName THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("prdName", IF(ISNULL(OLD.prdName), NULL, OLD.prdName))); END IF;
  IF ISNULL(OLD.prdDesc) != ISNULL(NEW.prdDesc) OR OLD.prdDesc != NEW.prdDesc THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("prdDesc", IF(ISNULL(OLD.prdDesc), NULL, OLD.prdDesc))); END IF;
  IF ISNULL(OLD.prdType) != ISNULL(NEW.prdType) OR OLD.prdType != NEW.prdType THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("prdType", IF(ISNULL(OLD.prdType), NULL, OLD.prdType))); END IF;
  IF ISNULL(OLD.prdValidFromDate) != ISNULL(NEW.prdValidFromDate) OR OLD.prdValidFromDate != NEW.prdValidFromDate THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("prdValidFromDate", IF(ISNULL(OLD.prdValidFromDate), NULL, OLD.prdValidFromDate))); END IF;
  IF ISNULL(OLD.prdValidToDate) != ISNULL(NEW.prdValidToDate) OR OLD.prdValidToDate != NEW.prdValidToDate THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("prdValidToDate", IF(ISNULL(OLD.prdValidToDate), NULL, OLD.prdValidToDate))); END IF;
  IF ISNULL(OLD.prdValidFromHour) != ISNULL(NEW.prdValidFromHour) OR OLD.prdValidFromHour != NEW.prdValidFromHour THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("prdValidFromHour", IF(ISNULL(OLD.prdValidFromHour), NULL, OLD.prdValidFromHour))); END IF;
  IF ISNULL(OLD.prdValidToHour) != ISNULL(NEW.prdValidToHour) OR OLD.prdValidToHour != NEW.prdValidToHour THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("prdValidToHour", IF(ISNULL(OLD.prdValidToHour), NULL, OLD.prdValidToHour))); END IF;
  IF ISNULL(OLD.prdDurationMinutes) != ISNULL(NEW.prdDurationMinutes) OR OLD.prdDurationMinutes != NEW.prdDurationMinutes THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("prdDurationMinutes", IF(ISNULL(OLD.prdDurationMinutes), NULL, OLD.prdDurationMinutes))); END IF;
  IF ISNULL(OLD.prdStartAtFirstUse) != ISNULL(NEW.prdStartAtFirstUse) OR OLD.prdStartAtFirstUse != NEW.prdStartAtFirstUse THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("prdStartAtFirstUse", IF(ISNULL(OLD.prdStartAtFirstUse), NULL, OLD.prdStartAtFirstUse))); END IF;
  IF ISNULL(OLD.prdPrivs) != ISNULL(NEW.prdPrivs) OR OLD.prdPrivs != NEW.prdPrivs THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("prdPrivs", IF(ISNULL(OLD.prdPrivs), NULL, OLD.prdPrivs))); END IF;
  IF ISNULL(OLD.prdVAT) != ISNULL(NEW.prdVAT) OR OLD.prdVAT != NEW.prdVAT THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("prdVAT", IF(ISNULL(OLD.prdVAT), NULL, OLD.prdVAT))); END IF;
  IF ISNULL(OLD.prdUnitID) != ISNULL(NEW.prdUnitID) OR OLD.prdUnitID != NEW.prdUnitID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("prdUnitID", IF(ISNULL(OLD.prdUnitID), NULL, OLD.prdUnitID))); END IF;
  IF ISNULL(OLD.prdQtyIsDecimal) != ISNULL(NEW.prdQtyIsDecimal) OR OLD.prdQtyIsDecimal != NEW.prdQtyIsDecimal THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("prdQtyIsDecimal", IF(ISNULL(OLD.prdQtyIsDecimal), NULL, OLD.prdQtyIsDecimal))); END IF;
  IF ISNULL(OLD.prdInStockQty) != ISNULL(NEW.prdInStockQty) OR OLD.prdInStockQty != NEW.prdInStockQty THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("prdInStockQty", IF(ISNULL(OLD.prdInStockQty), NULL, OLD.prdInStockQty))); END IF;
  IF ISNULL(OLD.prdOrderedQty) != ISNULL(NEW.prdOrderedQty) OR OLD.prdOrderedQty != NEW.prdOrderedQty THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("prdOrderedQty", IF(ISNULL(OLD.prdOrderedQty), NULL, OLD.prdOrderedQty))); END IF;
  IF ISNULL(OLD.prdReturnedQty) != ISNULL(NEW.prdReturnedQty) OR OLD.prdReturnedQty != NEW.prdReturnedQty THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("prdReturnedQty", IF(ISNULL(OLD.prdReturnedQty), NULL, OLD.prdReturnedQty))); END IF;
  IF ISNULL(OLD.prdI18NData) != ISNULL(NEW.prdI18NData) OR OLD.prdI18NData != NEW.prdI18NData THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("prdI18NData", IF(ISNULL(OLD.prdI18NData), NULL, OLD.prdI18NData))); END IF;
  IF ISNULL(OLD.prdStatus) != ISNULL(NEW.prdStatus) OR OLD.prdStatus != NEW.prdStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("prdStatus", IF(ISNULL(OLD.prdStatus), NULL, OLD.prdStatus))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.prdUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
        SET atlBy     = NEW.prdUpdatedBy
          , atlAction = "UPDATE"
          , atlTarget = "tbl_{{MODULE}}_Accounting_Product"
          , atlInfo   = JSON_OBJECT("prdID", OLD.prdID, "old", Changes);
  END IF;
END
SQL
    );

    $this->execute("DROP TRIGGER IF EXISTS trg_updatelog_tbl_{{MODULE}}_Accounting_Saleable;");
    $this->execute(<<<SQL
CREATE TRIGGER trg_updatelog_tbl_{{MODULE}}_Accounting_Saleable AFTER UPDATE ON tbl_{{MODULE}}_Accounting_Saleable FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.slbUUID) != ISNULL(NEW.slbUUID) OR OLD.slbUUID != NEW.slbUUID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slbUUID", IF(ISNULL(OLD.slbUUID), NULL, OLD.slbUUID))); END IF;
  IF ISNULL(OLD.slbProductID) != ISNULL(NEW.slbProductID) OR OLD.slbProductID != NEW.slbProductID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slbProductID", IF(ISNULL(OLD.slbProductID), NULL, OLD.slbProductID))); END IF;
  IF ISNULL(OLD.slbCode) != ISNULL(NEW.slbCode) OR OLD.slbCode != NEW.slbCode THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slbCode", IF(ISNULL(OLD.slbCode), NULL, OLD.slbCode))); END IF;
  IF ISNULL(OLD.slbName) != ISNULL(NEW.slbName) OR OLD.slbName != NEW.slbName THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slbName", IF(ISNULL(OLD.slbName), NULL, OLD.slbName))); END IF;
  IF ISNULL(OLD.slbDesc) != ISNULL(NEW.slbDesc) OR OLD.slbDesc != NEW.slbDesc THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slbDesc", IF(ISNULL(OLD.slbDesc), NULL, OLD.slbDesc))); END IF;
  IF ISNULL(OLD.slbAvailableFromDate) != ISNULL(NEW.slbAvailableFromDate) OR OLD.slbAvailableFromDate != NEW.slbAvailableFromDate THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slbAvailableFromDate", IF(ISNULL(OLD.slbAvailableFromDate), NULL, OLD.slbAvailableFromDate))); END IF;
  IF ISNULL(OLD.slbAvailableToDate) != ISNULL(NEW.slbAvailableToDate) OR OLD.slbAvailableToDate != NEW.slbAvailableToDate THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slbAvailableToDate", IF(ISNULL(OLD.slbAvailableToDate), NULL, OLD.slbAvailableToDate))); END IF;
  IF ISNULL(OLD.slbPrivs) != ISNULL(NEW.slbPrivs) OR OLD.slbPrivs != NEW.slbPrivs THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slbPrivs", IF(ISNULL(OLD.slbPrivs), NULL, OLD.slbPrivs))); END IF;
  IF ISNULL(OLD.slbBasePrice) != ISNULL(NEW.slbBasePrice) OR OLD.slbBasePrice != NEW.slbBasePrice THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slbBasePrice", IF(ISNULL(OLD.slbBasePrice), NULL, OLD.slbBasePrice))); END IF;
  IF ISNULL(OLD.slbAdditives) != ISNULL(NEW.slbAdditives) OR OLD.slbAdditives != NEW.slbAdditives THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slbAdditives", IF(ISNULL(OLD.slbAdditives), NULL, OLD.slbAdditives))); END IF;
  IF ISNULL(OLD.slbProductCount) != ISNULL(NEW.slbProductCount) OR OLD.slbProductCount != NEW.slbProductCount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slbProductCount", IF(ISNULL(OLD.slbProductCount), NULL, OLD.slbProductCount))); END IF;
  IF ISNULL(OLD.slbMaxSaleCountPerUser) != ISNULL(NEW.slbMaxSaleCountPerUser) OR OLD.slbMaxSaleCountPerUser != NEW.slbMaxSaleCountPerUser THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slbMaxSaleCountPerUser", IF(ISNULL(OLD.slbMaxSaleCountPerUser), NULL, OLD.slbMaxSaleCountPerUser))); END IF;
  IF ISNULL(OLD.slbInStockQty) != ISNULL(NEW.slbInStockQty) OR OLD.slbInStockQty != NEW.slbInStockQty THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slbInStockQty", IF(ISNULL(OLD.slbInStockQty), NULL, OLD.slbInStockQty))); END IF;
  IF ISNULL(OLD.slbOrderedQty) != ISNULL(NEW.slbOrderedQty) OR OLD.slbOrderedQty != NEW.slbOrderedQty THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slbOrderedQty", IF(ISNULL(OLD.slbOrderedQty), NULL, OLD.slbOrderedQty))); END IF;
  IF ISNULL(OLD.slbReturnedQty) != ISNULL(NEW.slbReturnedQty) OR OLD.slbReturnedQty != NEW.slbReturnedQty THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slbReturnedQty", IF(ISNULL(OLD.slbReturnedQty), NULL, OLD.slbReturnedQty))); END IF;
  IF ISNULL(OLD.slbVoucherTemplate) != ISNULL(NEW.slbVoucherTemplate) OR OLD.slbVoucherTemplate != NEW.slbVoucherTemplate THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slbVoucherTemplate", IF(ISNULL(OLD.slbVoucherTemplate), NULL, OLD.slbVoucherTemplate))); END IF;
  IF ISNULL(OLD.slbI18NData) != ISNULL(NEW.slbI18NData) OR OLD.slbI18NData != NEW.slbI18NData THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slbI18NData", IF(ISNULL(OLD.slbI18NData), NULL, OLD.slbI18NData))); END IF;
  IF ISNULL(OLD.slbStatus) != ISNULL(NEW.slbStatus) OR OLD.slbStatus != NEW.slbStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slbStatus", IF(ISNULL(OLD.slbStatus), NULL, OLD.slbStatus))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.slbUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
        SET atlBy     = NEW.slbUpdatedBy
          , atlAction = "UPDATE"
          , atlTarget = "tbl_{{MODULE}}_Accounting_Saleable"
          , atlInfo   = JSON_OBJECT("slbID", OLD.slbID, "old", Changes);
  END IF;
END
SQL
    );

    $this->execute("DROP TRIGGER IF EXISTS trg_updatelog_tbl_{{MODULE}}_Accounting_SaleableFile;");
    $this->execute(<<<SQL
CREATE TRIGGER trg_updatelog_tbl_{{MODULE}}_Accounting_SaleableFile AFTER UPDATE ON tbl_{{MODULE}}_Accounting_SaleableFile FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.slfSaleableID) != ISNULL(NEW.slfSaleableID) OR OLD.slfSaleableID != NEW.slfSaleableID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slfSaleableID", IF(ISNULL(OLD.slfSaleableID), NULL, OLD.slfSaleableID))); END IF;
  IF ISNULL(OLD.slfName) != ISNULL(NEW.slfName) OR OLD.slfName != NEW.slfName THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slfName", IF(ISNULL(OLD.slfName), NULL, OLD.slfName))); END IF;
  IF ISNULL(OLD.slfDesc) != ISNULL(NEW.slfDesc) OR OLD.slfDesc != NEW.slfDesc THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slfDesc", IF(ISNULL(OLD.slfDesc), NULL, OLD.slfDesc))); END IF;
  IF ISNULL(OLD.slfMimeTypes) != ISNULL(NEW.slfMimeTypes) OR OLD.slfMimeTypes != NEW.slfMimeTypes THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slfMimeTypes", IF(ISNULL(OLD.slfMimeTypes), NULL, OLD.slfMimeTypes))); END IF;
  IF ISNULL(OLD.slfExtensions) != ISNULL(NEW.slfExtensions) OR OLD.slfExtensions != NEW.slfExtensions THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slfExtensions", IF(ISNULL(OLD.slfExtensions), NULL, OLD.slfExtensions))); END IF;
  IF ISNULL(OLD.slfMinSize) != ISNULL(NEW.slfMinSize) OR OLD.slfMinSize != NEW.slfMinSize THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slfMinSize", IF(ISNULL(OLD.slfMinSize), NULL, OLD.slfMinSize))); END IF;
  IF ISNULL(OLD.slfMaxSize) != ISNULL(NEW.slfMaxSize) OR OLD.slfMaxSize != NEW.slfMaxSize THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slfMaxSize", IF(ISNULL(OLD.slfMaxSize), NULL, OLD.slfMaxSize))); END IF;
  IF ISNULL(OLD.slfMinCount) != ISNULL(NEW.slfMinCount) OR OLD.slfMinCount != NEW.slfMinCount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slfMinCount", IF(ISNULL(OLD.slfMinCount), NULL, OLD.slfMinCount))); END IF;
  IF ISNULL(OLD.slfMaxCount) != ISNULL(NEW.slfMaxCount) OR OLD.slfMaxCount != NEW.slfMaxCount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("slfMaxCount", IF(ISNULL(OLD.slfMaxCount), NULL, OLD.slfMaxCount))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.slfUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
        SET atlBy     = NEW.slfUpdatedBy
          , atlAction = "UPDATE"
          , atlTarget = "tbl_{{MODULE}}_Accounting_SaleableFile"
          , atlInfo   = JSON_OBJECT("slfID", OLD.slfID, "old", Changes);
  END IF;
END
SQL
    );

    $this->execute("DROP TRIGGER IF EXISTS trg_updatelog_tbl_{{MODULE}}_Accounting_UserAsset;");
    $this->execute(<<<SQL
CREATE TRIGGER trg_updatelog_tbl_{{MODULE}}_Accounting_UserAsset AFTER UPDATE ON tbl_{{MODULE}}_Accounting_UserAsset FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.uasUUID) != ISNULL(NEW.uasUUID) OR OLD.uasUUID != NEW.uasUUID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasUUID", IF(ISNULL(OLD.uasUUID), NULL, OLD.uasUUID))); END IF;
  IF ISNULL(OLD.uasActorID) != ISNULL(NEW.uasActorID) OR OLD.uasActorID != NEW.uasActorID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasActorID", IF(ISNULL(OLD.uasActorID), NULL, OLD.uasActorID))); END IF;
  IF ISNULL(OLD.uasSaleableID) != ISNULL(NEW.uasSaleableID) OR OLD.uasSaleableID != NEW.uasSaleableID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasSaleableID", IF(ISNULL(OLD.uasSaleableID), NULL, OLD.uasSaleableID))); END IF;
  IF ISNULL(OLD.uasQty) != ISNULL(NEW.uasQty) OR OLD.uasQty != NEW.uasQty THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasQty", IF(ISNULL(OLD.uasQty), NULL, OLD.uasQty))); END IF;
  IF ISNULL(OLD.uasVoucherID) != ISNULL(NEW.uasVoucherID) OR OLD.uasVoucherID != NEW.uasVoucherID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasVoucherID", IF(ISNULL(OLD.uasVoucherID), NULL, OLD.uasVoucherID))); END IF;
  IF ISNULL(OLD.uasVoucherItemInfo) != ISNULL(NEW.uasVoucherItemInfo) OR OLD.uasVoucherItemInfo != NEW.uasVoucherItemInfo THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasVoucherItemInfo", IF(ISNULL(OLD.uasVoucherItemInfo), NULL, OLD.uasVoucherItemInfo))); END IF;
  IF ISNULL(OLD.uasCouponID) != ISNULL(NEW.uasCouponID) OR OLD.uasCouponID != NEW.uasCouponID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasCouponID", IF(ISNULL(OLD.uasCouponID), NULL, OLD.uasCouponID))); END IF;
  IF ISNULL(OLD.uasDiscountAmount) != ISNULL(NEW.uasDiscountAmount) OR OLD.uasDiscountAmount != NEW.uasDiscountAmount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasDiscountAmount", IF(ISNULL(OLD.uasDiscountAmount), NULL, OLD.uasDiscountAmount))); END IF;
  IF ISNULL(OLD.uasPrefered) != ISNULL(NEW.uasPrefered) OR OLD.uasPrefered != NEW.uasPrefered THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasPrefered", IF(ISNULL(OLD.uasPrefered), NULL, OLD.uasPrefered))); END IF;
  IF ISNULL(OLD.uasValidFromDate) != ISNULL(NEW.uasValidFromDate) OR OLD.uasValidFromDate != NEW.uasValidFromDate THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasValidFromDate", IF(ISNULL(OLD.uasValidFromDate), NULL, OLD.uasValidFromDate))); END IF;
  IF ISNULL(OLD.uasValidToDate) != ISNULL(NEW.uasValidToDate) OR OLD.uasValidToDate != NEW.uasValidToDate THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasValidToDate", IF(ISNULL(OLD.uasValidToDate), NULL, OLD.uasValidToDate))); END IF;
  IF ISNULL(OLD.uasValidFromHour) != ISNULL(NEW.uasValidFromHour) OR OLD.uasValidFromHour != NEW.uasValidFromHour THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasValidFromHour", IF(ISNULL(OLD.uasValidFromHour), NULL, OLD.uasValidFromHour))); END IF;
  IF ISNULL(OLD.uasValidToHour) != ISNULL(NEW.uasValidToHour) OR OLD.uasValidToHour != NEW.uasValidToHour THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasValidToHour", IF(ISNULL(OLD.uasValidToHour), NULL, OLD.uasValidToHour))); END IF;
  IF ISNULL(OLD.uasDurationMinutes) != ISNULL(NEW.uasDurationMinutes) OR OLD.uasDurationMinutes != NEW.uasDurationMinutes THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasDurationMinutes", IF(ISNULL(OLD.uasDurationMinutes), NULL, OLD.uasDurationMinutes))); END IF;
  IF ISNULL(OLD.uasBreakedAt) != ISNULL(NEW.uasBreakedAt) OR OLD.uasBreakedAt != NEW.uasBreakedAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasBreakedAt", IF(ISNULL(OLD.uasBreakedAt), NULL, OLD.uasBreakedAt))); END IF;
  IF ISNULL(OLD.uasStatus) != ISNULL(NEW.uasStatus) OR OLD.uasStatus != NEW.uasStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasStatus", IF(ISNULL(OLD.uasStatus), NULL, OLD.uasStatus))); END IF;

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

    $this->execute("DROP TRIGGER IF EXISTS trg_updatelog_tbl_{{MODULE}}_Accounting_UserAsset_File;");
    $this->execute(<<<SQL
CREATE TRIGGER trg_updatelog_tbl_{{MODULE}}_Accounting_UserAsset_File AFTER UPDATE ON tbl_{{MODULE}}_Accounting_UserAsset_File FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.uasuflUserAssetID) != ISNULL(NEW.uasuflUserAssetID) OR OLD.uasuflUserAssetID != NEW.uasuflUserAssetID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasuflUserAssetID", IF(ISNULL(OLD.uasuflUserAssetID), NULL, OLD.uasuflUserAssetID))); END IF;
  IF ISNULL(OLD.uasuflSaleableFileID) != ISNULL(NEW.uasuflSaleableFileID) OR OLD.uasuflSaleableFileID != NEW.uasuflSaleableFileID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasuflSaleableFileID", IF(ISNULL(OLD.uasuflSaleableFileID), NULL, OLD.uasuflSaleableFileID))); END IF;
  IF ISNULL(OLD.uasuflFileID) != ISNULL(NEW.uasuflFileID) OR OLD.uasuflFileID != NEW.uasuflFileID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uasuflFileID", IF(ISNULL(OLD.uasuflFileID), NULL, OLD.uasuflFileID))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.uasuflUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
        SET atlBy     = NEW.uasuflUpdatedBy
          , atlAction = "UPDATE"
          , atlTarget = "tbl_{{MODULE}}_Accounting_UserAsset_File"
          , atlInfo   = JSON_OBJECT("uasuflID", OLD.uasuflID, "old", Changes);
  END IF;
END
SQL
    );

//     tbl_{{MODULE}}_Accounting_AssetUsage
//     $this->execute("XXXXXXXXXXX");
//     $this->execute(<<<SQL
// SQL
//     );

  }

  public function safeDown()
  {
    echo "m230910_000000_accounting_create_accounting cannot be reverted.\n";
    return false;
  }

}
