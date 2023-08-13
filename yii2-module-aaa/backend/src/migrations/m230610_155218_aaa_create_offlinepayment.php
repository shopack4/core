<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m230610_155218_aaa_create_offlinepayment extends Migration
{
  public function safeUp()
  {
    $this->execute(<<<SQLSTR
CREATE TABLE `tbl_AAA_OfflinePayment` (
	`ofpID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ofpUUID` VARCHAR(38) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`ofpOwnerUserID` BIGINT(20) UNSIGNED NOT NULL,
	`ofpVoucherID` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`ofpBankOrCart` VARCHAR(64) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`ofpTrackNumber` VARCHAR(64) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`ofpReferenceNumber` VARCHAR(64) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`ofpAmount` DOUBLE UNSIGNED NOT NULL,
	`ofpPayDate` DATETIME NOT NULL,
	`ofpPayer` VARCHAR(64) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`ofpSourceCartNumber` VARCHAR(20) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`ofpImageFileID` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`ofpWalletID` BIGINT(20) UNSIGNED NOT NULL,
	`ofpComment` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`ofpStatus` CHAR(1) NOT NULL DEFAULT 'W' COMMENT 'W:Wait for approve, A:Approved, J:Rejected, R:Removed' COLLATE 'utf8mb4_unicode_ci',
	`ofpCreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`ofpCreatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`ofpUpdatedAt` DATETIME NULL DEFAULT NULL,
	`ofpUpdatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`ofpRemovedAt` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`ofpRemovedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`ofpID`) USING BTREE,
	UNIQUE INDEX `ofpUUID` (`ofpUUID`) USING BTREE,
	INDEX `FK_tbl_AAA_OfflinePayment_tbl_AAA_Wallet` (`ofpWalletID`) USING BTREE,
	INDEX `FK_tbl_AAA_OfflinePayment_tbl_AAA_UploadFile` (`ofpImageFileID`) USING BTREE,
	INDEX `FK_tbl_AAA_OfflinePayment_tbl_AAA_Voucher` (`ofpVoucherID`) USING BTREE,
	INDEX `FK_tbl_AAA_OfflinePayment_tbl_AAA_User` (`ofpOwnerUserID`) USING BTREE,
	CONSTRAINT `FK_tbl_AAA_OfflinePayment_tbl_AAA_UploadFile` FOREIGN KEY (`ofpImageFileID`) REFERENCES `tbl_AAA_UploadFile` (`uflID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_tbl_AAA_OfflinePayment_tbl_AAA_User` FOREIGN KEY (`ofpOwnerUserID`) REFERENCES `tbl_AAA_User` (`usrID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_tbl_AAA_OfflinePayment_tbl_AAA_Voucher` FOREIGN KEY (`ofpVoucherID`) REFERENCES `tbl_AAA_Voucher` (`vchID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_tbl_AAA_OfflinePayment_tbl_AAA_Wallet` FOREIGN KEY (`ofpWalletID`) REFERENCES `tbl_AAA_Wallet` (`walID`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;
SQLSTR
    );

    $this->execute(<<<SQLSTR
ALTER TABLE `tbl_AAA_WalletTransaction`
  ADD CONSTRAINT `FK_tbl_AAA_WalletTransaction_tbl_AAA_Voucher` FOREIGN KEY (`wtrVoucherID`) REFERENCES `tbl_AAA_Voucher` (`vchID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
  ADD CONSTRAINT `FK_tbl_AAA_WalletTransaction_tbl_AAA_OnlinePayment` FOREIGN KEY (`wtrOnlinePaymentID`) REFERENCES `tbl_AAA_OnlinePayment` (`onpID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
  ADD CONSTRAINT `FK_tbl_AAA_WalletTransaction_tbl_AAA_OfflinePayment` FOREIGN KEY (`wtrOfflinePaymentID`) REFERENCES `tbl_AAA_OfflinePayment` (`ofpID`) ON UPDATE NO ACTION ON DELETE NO ACTION;
SQLSTR
    );

    $this->execute(<<<SQLSTR
DROP TRIGGER IF EXISTS trg_updatelog_tbl_AAA_OfflinePayment;
SQLSTR
    );

    $this->execute(<<<SQLSTR
CREATE TRIGGER trg_updatelog_tbl_AAA_OfflinePayment AFTER UPDATE ON tbl_AAA_OfflinePayment FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.ofpAmount) != ISNULL(NEW.ofpAmount) OR OLD.ofpAmount != NEW.ofpAmount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpAmount", IF(ISNULL(OLD.ofpAmount), NULL, OLD.ofpAmount))); END IF;
  IF ISNULL(OLD.ofpBankOrCart) != ISNULL(NEW.ofpBankOrCart) OR OLD.ofpBankOrCart != NEW.ofpBankOrCart THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpBankOrCart", IF(ISNULL(OLD.ofpBankOrCart), NULL, OLD.ofpBankOrCart))); END IF;
  IF ISNULL(OLD.ofpComment) != ISNULL(NEW.ofpComment) OR OLD.ofpComment != NEW.ofpComment THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpComment", IF(ISNULL(OLD.ofpComment), NULL, OLD.ofpComment))); END IF;
  IF ISNULL(OLD.ofpImageFileID) != ISNULL(NEW.ofpImageFileID) OR OLD.ofpImageFileID != NEW.ofpImageFileID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpImageFileID", IF(ISNULL(OLD.ofpImageFileID), NULL, OLD.ofpImageFileID))); END IF;
  IF ISNULL(OLD.ofpOwnerUserID) != ISNULL(NEW.ofpOwnerUserID) OR OLD.ofpOwnerUserID != NEW.ofpOwnerUserID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpOwnerUserID", IF(ISNULL(OLD.ofpOwnerUserID), NULL, OLD.ofpOwnerUserID))); END IF;
  IF ISNULL(OLD.ofpPayDate) != ISNULL(NEW.ofpPayDate) OR OLD.ofpPayDate != NEW.ofpPayDate THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpPayDate", IF(ISNULL(OLD.ofpPayDate), NULL, OLD.ofpPayDate))); END IF;
  IF ISNULL(OLD.ofpPayer) != ISNULL(NEW.ofpPayer) OR OLD.ofpPayer != NEW.ofpPayer THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpPayer", IF(ISNULL(OLD.ofpPayer), NULL, OLD.ofpPayer))); END IF;
  IF ISNULL(OLD.ofpReferenceNumber) != ISNULL(NEW.ofpReferenceNumber) OR OLD.ofpReferenceNumber != NEW.ofpReferenceNumber THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpReferenceNumber", IF(ISNULL(OLD.ofpReferenceNumber), NULL, OLD.ofpReferenceNumber))); END IF;
  IF ISNULL(OLD.ofpSourceCartNumber) != ISNULL(NEW.ofpSourceCartNumber) OR OLD.ofpSourceCartNumber != NEW.ofpSourceCartNumber THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpSourceCartNumber", IF(ISNULL(OLD.ofpSourceCartNumber), NULL, OLD.ofpSourceCartNumber))); END IF;
  IF ISNULL(OLD.ofpStatus) != ISNULL(NEW.ofpStatus) OR OLD.ofpStatus != NEW.ofpStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpStatus", IF(ISNULL(OLD.ofpStatus), NULL, OLD.ofpStatus))); END IF;
  IF ISNULL(OLD.ofpTrackNumber) != ISNULL(NEW.ofpTrackNumber) OR OLD.ofpTrackNumber != NEW.ofpTrackNumber THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpTrackNumber", IF(ISNULL(OLD.ofpTrackNumber), NULL, OLD.ofpTrackNumber))); END IF;
  IF ISNULL(OLD.ofpUUID) != ISNULL(NEW.ofpUUID) OR OLD.ofpUUID != NEW.ofpUUID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpUUID", IF(ISNULL(OLD.ofpUUID), NULL, OLD.ofpUUID))); END IF;
  IF ISNULL(OLD.ofpVoucherID) != ISNULL(NEW.ofpVoucherID) OR OLD.ofpVoucherID != NEW.ofpVoucherID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpVoucherID", IF(ISNULL(OLD.ofpVoucherID), NULL, OLD.ofpVoucherID))); END IF;
  IF ISNULL(OLD.ofpWalletID) != ISNULL(NEW.ofpWalletID) OR OLD.ofpWalletID != NEW.ofpWalletID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpWalletID", IF(ISNULL(OLD.ofpWalletID), NULL, OLD.ofpWalletID))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.ofpUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
        SET atlBy     = NEW.ofpUpdatedBy
          , atlAction = "UPDATE"
          , atlTarget = "tbl_AAA_OfflinePayment"
          , atlInfo   = JSON_OBJECT("ofpID", OLD.ofpID, "old", Changes);
  END IF;
END;
SQLSTR
    );
  }

  public function safeDown()
  {
    echo "m230610_155218_aaa_create_offlinepayment cannot be reverted.\n";
    return false;
  }

}
