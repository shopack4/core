<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m231021_122609_aaa_create_deliverymethod extends Migration
{
	public function safeUp()
	{
    $this->execute(<<<SQLSTR
CREATE TABLE `tbl_AAA_DeliveryMethod` (
	`dlvID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`dlvUUID` VARCHAR(38) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`dlvName` VARCHAR(128) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`dlvType` CHAR(1) NOT NULL DEFAULT 'S' COMMENT 'R:Receive by customer, S:Send to customer' COLLATE 'utf8mb4_unicode_ci',
	`dlvAmount` INT(10) UNSIGNED NULL DEFAULT NULL,
	`dlvTotalUsedCount` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`dlvTotalUsedAmount` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`dlvI18NData` JSON NULL DEFAULT NULL,
	`dlvStatus` CHAR(1) NOT NULL DEFAULT 'A' COMMENT 'A:Active, R:Removed' COLLATE 'utf8mb4_unicode_ci',
	`dlvCreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`dlvCreatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`dlvUpdatedAt` DATETIME NULL DEFAULT NULL,
	`dlvUpdatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`dlvRemovedAt` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`dlvRemovedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`dlvID`) USING BTREE,
	UNIQUE INDEX `dlvUUID` (`dlvUUID`) USING BTREE,
	INDEX `dlvCreatedBy` (`dlvCreatedBy`) USING BTREE,
	INDEX `dlvCreatedAt` (`dlvCreatedAt`) USING BTREE,
	INDEX `dlvUpdatedBy` (`dlvUpdatedBy`) USING BTREE,
	INDEX `dlvStatus` (`dlvStatus`) USING BTREE,
	INDEX `dlvType` (`dlvType`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;
SQLSTR
    );
    $this->alterColumn('tbl_AAA_DeliveryMethod', 'dlvI18NData', $this->json());

		$this->execute(<<<SQLSTR
ALTER TABLE `tbl_AAA_Voucher`
	ADD COLUMN `vchDeliveryMethodID` INT NULL DEFAULT NULL AFTER `vchAmount`,
	ADD COLUMN `vchDeliveryAmount` INT(10) NULL DEFAULT NULL AFTER `vchDeliveryMethodID`;
SQLSTR
    );

		$this->execute(<<<SQLSTR
ALTER TABLE `tbl_AAA_Voucher`
	ADD COLUMN `vchTotalAmount` INT(10) NULL AFTER `vchDeliveryAmount`;
SQLSTR
    );

		$this->execute(<<<SQLSTR
UPDATE tbl_AAA_Voucher
	SET vchTotalAmount = vchAmount
	WHERE vchTotalAmount IS NULL;
SQLSTR
    );

    $this->execute(<<<SQLSTR
ALTER TABLE `tbl_AAA_Voucher`
	CHANGE COLUMN `vchTotalAmount` `vchTotalAmount` INT(10) NOT NULL AFTER `vchDeliveryAmount`;
SQLSTR
    );

    $this->execute("DROP TRIGGER IF EXISTS trg_updatelog_tbl_AAA_DeliveryMethod;");
    $this->execute(<<<SQLSTR
CREATE TRIGGER trg_updatelog_tbl_AAA_DeliveryMethod AFTER UPDATE ON tbl_AAA_DeliveryMethod FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.dlvUUID) != ISNULL(NEW.dlvUUID) OR OLD.dlvUUID != NEW.dlvUUID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dlvUUID", IF(ISNULL(OLD.dlvUUID), NULL, OLD.dlvUUID))); END IF;
  IF ISNULL(OLD.dlvName) != ISNULL(NEW.dlvName) OR OLD.dlvName != NEW.dlvName THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dlvName", IF(ISNULL(OLD.dlvName), NULL, OLD.dlvName))); END IF;
  IF ISNULL(OLD.dlvType) != ISNULL(NEW.dlvType) OR OLD.dlvType != NEW.dlvType THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dlvType", IF(ISNULL(OLD.dlvType), NULL, OLD.dlvType))); END IF;
  IF ISNULL(OLD.dlvAmount) != ISNULL(NEW.dlvAmount) OR OLD.dlvAmount != NEW.dlvAmount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dlvAmount", IF(ISNULL(OLD.dlvAmount), NULL, OLD.dlvAmount))); END IF;
  IF ISNULL(OLD.dlvTotalUsedCount) != ISNULL(NEW.dlvTotalUsedCount) OR OLD.dlvTotalUsedCount != NEW.dlvTotalUsedCount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dlvTotalUsedCount", IF(ISNULL(OLD.dlvTotalUsedCount), NULL, OLD.dlvTotalUsedCount))); END IF;
  IF ISNULL(OLD.dlvTotalUsedAmount) != ISNULL(NEW.dlvTotalUsedAmount) OR OLD.dlvTotalUsedAmount != NEW.dlvTotalUsedAmount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dlvTotalUsedAmount", IF(ISNULL(OLD.dlvTotalUsedAmount), NULL, OLD.dlvTotalUsedAmount))); END IF;
  IF ISNULL(OLD.dlvI18NData) != ISNULL(NEW.dlvI18NData) OR OLD.dlvI18NData != NEW.dlvI18NData THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dlvI18NData", IF(ISNULL(OLD.dlvI18NData), NULL, OLD.dlvI18NData))); END IF;
  IF ISNULL(OLD.dlvStatus) != ISNULL(NEW.dlvStatus) OR OLD.dlvStatus != NEW.dlvStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("dlvStatus", IF(ISNULL(OLD.dlvStatus), NULL, OLD.dlvStatus))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.dlvUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
        SET atlBy     = NEW.dlvUpdatedBy
          , atlAction = "UPDATE"
          , atlTarget = "tbl_AAA_DeliveryMethod"
          , atlInfo   = JSON_OBJECT("dlvID", OLD.dlvID, "old", Changes);
  END IF;
END
SQLSTR
    );

    $this->execute("DROP TRIGGER IF EXISTS trg_updatelog_tbl_AAA_Voucher;");
    $this->execute(<<<SQLSTR
CREATE TRIGGER trg_updatelog_tbl_AAA_Voucher AFTER UPDATE ON tbl_AAA_Voucher FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.vchUUID) != ISNULL(NEW.vchUUID) OR OLD.vchUUID != NEW.vchUUID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchUUID", IF(ISNULL(OLD.vchUUID), NULL, OLD.vchUUID))); END IF;
  IF ISNULL(OLD.vchOwnerUserID) != ISNULL(NEW.vchOwnerUserID) OR OLD.vchOwnerUserID != NEW.vchOwnerUserID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchOwnerUserID", IF(ISNULL(OLD.vchOwnerUserID), NULL, OLD.vchOwnerUserID))); END IF;
  IF ISNULL(OLD.vchType) != ISNULL(NEW.vchType) OR OLD.vchType != NEW.vchType THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchType", IF(ISNULL(OLD.vchType), NULL, OLD.vchType))); END IF;
  IF ISNULL(OLD.vchAmount) != ISNULL(NEW.vchAmount) OR OLD.vchAmount != NEW.vchAmount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchAmount", IF(ISNULL(OLD.vchAmount), NULL, OLD.vchAmount))); END IF;
  IF ISNULL(OLD.vchDeliveryMethodID) != ISNULL(NEW.vchDeliveryMethodID) OR OLD.vchDeliveryMethodID != NEW.vchDeliveryMethodID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchDeliveryMethodID", IF(ISNULL(OLD.vchDeliveryMethodID), NULL, OLD.vchDeliveryMethodID))); END IF;
  IF ISNULL(OLD.vchDeliveryAmount) != ISNULL(NEW.vchDeliveryAmount) OR OLD.vchDeliveryAmount != NEW.vchDeliveryAmount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchDeliveryAmount", IF(ISNULL(OLD.vchDeliveryAmount), NULL, OLD.vchDeliveryAmount))); END IF;
  IF ISNULL(OLD.vchTotalAmount) != ISNULL(NEW.vchTotalAmount) OR OLD.vchTotalAmount != NEW.vchTotalAmount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchTotalAmount", IF(ISNULL(OLD.vchTotalAmount), NULL, OLD.vchTotalAmount))); END IF;
  IF ISNULL(OLD.vchPaidByWallet) != ISNULL(NEW.vchPaidByWallet) OR OLD.vchPaidByWallet != NEW.vchPaidByWallet THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchPaidByWallet", IF(ISNULL(OLD.vchPaidByWallet), NULL, OLD.vchPaidByWallet))); END IF;
  IF ISNULL(OLD.vchOnlinePaid) != ISNULL(NEW.vchOnlinePaid) OR OLD.vchOnlinePaid != NEW.vchOnlinePaid THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchOnlinePaid", IF(ISNULL(OLD.vchOnlinePaid), NULL, OLD.vchOnlinePaid))); END IF;
  IF ISNULL(OLD.vchOfflinePaid) != ISNULL(NEW.vchOfflinePaid) OR OLD.vchOfflinePaid != NEW.vchOfflinePaid THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchOfflinePaid", IF(ISNULL(OLD.vchOfflinePaid), NULL, OLD.vchOfflinePaid))); END IF;
  IF ISNULL(OLD.vchTotalPaid) != ISNULL(NEW.vchTotalPaid) OR OLD.vchTotalPaid != NEW.vchTotalPaid THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchTotalPaid", IF(ISNULL(OLD.vchTotalPaid), NULL, OLD.vchTotalPaid))); END IF;
  IF ISNULL(OLD.vchItems) != ISNULL(NEW.vchItems) OR OLD.vchItems != NEW.vchItems THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchItems", IF(ISNULL(OLD.vchItems), NULL, OLD.vchItems))); END IF;
  IF ISNULL(OLD.vchStatus) != ISNULL(NEW.vchStatus) OR OLD.vchStatus != NEW.vchStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchStatus", IF(ISNULL(OLD.vchStatus), NULL, OLD.vchStatus))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.vchUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
        SET atlBy     = NEW.vchUpdatedBy
          , atlAction = "UPDATE"
          , atlTarget = "tbl_AAA_Voucher"
          , atlInfo   = JSON_OBJECT("vchID", OLD.vchID, "old", Changes);
  END IF;
END
SQLSTR
    );
	}

	public function safeDown()
	{
		echo "m231021_122609_aaa_create_deliverymethod cannot be reverted.\n";
		return false;
	}

}
