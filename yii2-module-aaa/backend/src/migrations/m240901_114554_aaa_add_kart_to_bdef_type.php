<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m240901_114554_aaa_add_kart_to_bdef_type extends Migration
{
	public function safeUp()
	{
		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_BasicDefinition`
	CHANGE COLUMN `bdfType` `bdfType` CHAR(1) NOT NULL COMMENT 'O:Offline Payment Reject Reason, B:Bank, K:Bank Kart' COLLATE 'utf8mb4_unicode_ci' AFTER `bdfUUID`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_OfflinePayment`
	ADD COLUMN `ofpDestCartID` INT UNSIGNED NULL DEFAULT NULL AFTER `ofpDestCartNumber`,
	ADD CONSTRAINT `FK_tbl_AAA_OfflinePayment_tbl_AAA_BasicDefinition_2` FOREIGN KEY (`ofpDestCartID`) REFERENCES `tbl_AAA_BasicDefinition` (`bdfID`) ON UPDATE NO ACTION ON DELETE NO ACTION;
SQL
		);

		$this->execute("DROP TRIGGER IF EXISTS trg_updatelog_tbl_AAA_OfflinePayment;");
		$this->execute(<<<SQL
CREATE TRIGGER trg_updatelog_tbl_AAA_OfflinePayment AFTER UPDATE ON tbl_AAA_OfflinePayment FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.ofpUUID) != ISNULL(NEW.ofpUUID) OR OLD.ofpUUID != NEW.ofpUUID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpUUID", IF(ISNULL(OLD.ofpUUID), NULL, OLD.ofpUUID))); END IF;
  IF ISNULL(OLD.ofpOwnerUserID) != ISNULL(NEW.ofpOwnerUserID) OR OLD.ofpOwnerUserID != NEW.ofpOwnerUserID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpOwnerUserID", IF(ISNULL(OLD.ofpOwnerUserID), NULL, OLD.ofpOwnerUserID))); END IF;
  IF ISNULL(OLD.ofpVoucherID) != ISNULL(NEW.ofpVoucherID) OR OLD.ofpVoucherID != NEW.ofpVoucherID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpVoucherID", IF(ISNULL(OLD.ofpVoucherID), NULL, OLD.ofpVoucherID))); END IF;
  IF ISNULL(OLD.ofpType) != ISNULL(NEW.ofpType) OR OLD.ofpType != NEW.ofpType THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpType", IF(ISNULL(OLD.ofpType), NULL, OLD.ofpType))); END IF;
  IF ISNULL(OLD.ofpDestCartID) != ISNULL(NEW.ofpDestCartID) OR OLD.ofpDestCartID != NEW.ofpDestCartID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpDestCartID", IF(ISNULL(OLD.ofpDestCartID), NULL, OLD.ofpDestCartID))); END IF;
  IF ISNULL(OLD.ofpDestAccountNumber) != ISNULL(NEW.ofpDestAccountNumber) OR OLD.ofpDestAccountNumber != NEW.ofpDestAccountNumber THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpDestAccountNumber", IF(ISNULL(OLD.ofpDestAccountNumber), NULL, OLD.ofpDestAccountNumber))); END IF;
  IF ISNULL(OLD.ofpDestISBN) != ISNULL(NEW.ofpDestISBN) OR OLD.ofpDestISBN != NEW.ofpDestISBN THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpDestISBN", IF(ISNULL(OLD.ofpDestISBN), NULL, OLD.ofpDestISBN))); END IF;
  IF ISNULL(OLD.ofpDestBankID) != ISNULL(NEW.ofpDestBankID) OR OLD.ofpDestBankID != NEW.ofpDestBankID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpDestBankID", IF(ISNULL(OLD.ofpDestBankID), NULL, OLD.ofpDestBankID))); END IF;
  IF ISNULL(OLD.ofpDestName) != ISNULL(NEW.ofpDestName) OR OLD.ofpDestName != NEW.ofpDestName THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpDestName", IF(ISNULL(OLD.ofpDestName), NULL, OLD.ofpDestName))); END IF;
  IF ISNULL(OLD.ofpDueDate) != ISNULL(NEW.ofpDueDate) OR OLD.ofpDueDate != NEW.ofpDueDate THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpDueDate", IF(ISNULL(OLD.ofpDueDate), NULL, OLD.ofpDueDate))); END IF;
  IF ISNULL(OLD.ofpTrackNumber) != ISNULL(NEW.ofpTrackNumber) OR OLD.ofpTrackNumber != NEW.ofpTrackNumber THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpTrackNumber", IF(ISNULL(OLD.ofpTrackNumber), NULL, OLD.ofpTrackNumber))); END IF;
  IF ISNULL(OLD.ofpReferenceNumber) != ISNULL(NEW.ofpReferenceNumber) OR OLD.ofpReferenceNumber != NEW.ofpReferenceNumber THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpReferenceNumber", IF(ISNULL(OLD.ofpReferenceNumber), NULL, OLD.ofpReferenceNumber))); END IF;
  IF ISNULL(OLD.ofpAmount) != ISNULL(NEW.ofpAmount) OR OLD.ofpAmount != NEW.ofpAmount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpAmount", IF(ISNULL(OLD.ofpAmount), NULL, OLD.ofpAmount))); END IF;
  IF ISNULL(OLD.ofpPayDate) != ISNULL(NEW.ofpPayDate) OR OLD.ofpPayDate != NEW.ofpPayDate THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpPayDate", IF(ISNULL(OLD.ofpPayDate), NULL, OLD.ofpPayDate))); END IF;
  IF ISNULL(OLD.ofpPayer) != ISNULL(NEW.ofpPayer) OR OLD.ofpPayer != NEW.ofpPayer THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpPayer", IF(ISNULL(OLD.ofpPayer), NULL, OLD.ofpPayer))); END IF;
  IF ISNULL(OLD.ofpSourceCartNumber) != ISNULL(NEW.ofpSourceCartNumber) OR OLD.ofpSourceCartNumber != NEW.ofpSourceCartNumber THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpSourceCartNumber", IF(ISNULL(OLD.ofpSourceCartNumber), NULL, OLD.ofpSourceCartNumber))); END IF;
  IF ISNULL(OLD.ofpImageFileID) != ISNULL(NEW.ofpImageFileID) OR OLD.ofpImageFileID != NEW.ofpImageFileID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpImageFileID", IF(ISNULL(OLD.ofpImageFileID), NULL, OLD.ofpImageFileID))); END IF;
  IF ISNULL(OLD.ofpWalletID) != ISNULL(NEW.ofpWalletID) OR OLD.ofpWalletID != NEW.ofpWalletID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpWalletID", IF(ISNULL(OLD.ofpWalletID), NULL, OLD.ofpWalletID))); END IF;
  IF ISNULL(OLD.ofpComment) != ISNULL(NEW.ofpComment) OR OLD.ofpComment != NEW.ofpComment THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpComment", IF(ISNULL(OLD.ofpComment), NULL, OLD.ofpComment))); END IF;
  IF ISNULL(OLD.ofpRejectReasonIDs) != ISNULL(NEW.ofpRejectReasonIDs) OR OLD.ofpRejectReasonIDs != NEW.ofpRejectReasonIDs THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpRejectReasonIDs", IF(ISNULL(OLD.ofpRejectReasonIDs), NULL, OLD.ofpRejectReasonIDs))); END IF;
  IF ISNULL(OLD.ofpStatus) != ISNULL(NEW.ofpStatus) OR OLD.ofpStatus != NEW.ofpStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpStatus", IF(ISNULL(OLD.ofpStatus), NULL, OLD.ofpStatus))); END IF;
  IF ISNULL(OLD.ofpUniqueMD5) != ISNULL(NEW.ofpUniqueMD5) OR OLD.ofpUniqueMD5 != NEW.ofpUniqueMD5 THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpUniqueMD5", IF(ISNULL(OLD.ofpUniqueMD5), NULL, OLD.ofpUniqueMD5))); END IF;

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
END
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_OfflinePayment`
	CHANGE COLUMN `ofpDestCartNumber` `ofpDestCartNumber_OLD` VARCHAR(64) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci' AFTER `ofpBankOrCart_OLD`;
SQL
		);

	}

	public function safeDown()
	{
		echo "m240901_114554_aaa_add_kart_to_bdef_type cannot be reverted.\n";
		return false;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{
	}

	public function down()
	{
		echo "m240901_114554_aaa_add_kart_to_bdef_type cannot be reverted.\n";
		return false;
	}
	*/

}
