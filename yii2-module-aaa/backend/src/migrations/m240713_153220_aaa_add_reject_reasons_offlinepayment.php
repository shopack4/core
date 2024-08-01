<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m240713_153220_aaa_add_reject_reasons_offlinepayment extends Migration
{
	public function safeUp()
	{
    $this->execute(<<<SQL
ALTER TABLE `tbl_AAA_OfflinePayment`
	ADD COLUMN `ofpRejectReasonIDs` JSON NULL AFTER `ofpComment`;
SQL
		);

    $this->alterColumn('tbl_AAA_OfflinePayment', 'ofpRejectReasonIDs', $this->json());

    $this->execute(<<<SQL
ALTER TABLE `tbl_AAA_OfflinePayment`
	ADD COLUMN `ofpUniqueMD5` CHAR(32) AS (IF(`ofpStatus`='A',MD5(CONCAT_WS('_',IFNULL(`ofpTrackNumber`,'-'),IFNULL(`ofpReferenceNumber`,'-'),`ofpStatus`)),REPLACE(`ofpUUID`,'-',''))) VIRTUAL AFTER `ofpRemovedBy`;
SQL
		);

    //todo: fix this
    /*
      SQLSTATE[HY000]: General error: 1901 Function or expression 'if(`ofpStatus` = 'A',md5(concat_ws('_',ifnull(`ofpTrackNumber`,'-'),ifnull(`ofpReferenceNumber`,'-'),`ofpStatus`)),replace(`ofpUUID`,'-',''))' cannot be used in the GENERATED ALWAYS AS clause of `ofpUniqueMD5`
    */
// 		$this->execute(<<<SQL
// ALTER TABLE `tbl_AAA_OfflinePayment`
// 	ADD UNIQUE INDEX `ofpUniqueMD5` (`ofpUniqueMD5`) USING BTREE;
// SQL
//     );

		$this->execute("DROP TRIGGER IF EXISTS `trg_updatelog_tbl_AAA_OfflinePayment`;");
    $this->execute(<<<SQL
CREATE TRIGGER trg_updatelog_tbl_AAA_OfflinePayment AFTER UPDATE ON tbl_AAA_OfflinePayment FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.ofpUUID) != ISNULL(NEW.ofpUUID) OR OLD.ofpUUID != NEW.ofpUUID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpUUID", IF(ISNULL(OLD.ofpUUID), NULL, OLD.ofpUUID))); END IF;
  IF ISNULL(OLD.ofpOwnerUserID) != ISNULL(NEW.ofpOwnerUserID) OR OLD.ofpOwnerUserID != NEW.ofpOwnerUserID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpOwnerUserID", IF(ISNULL(OLD.ofpOwnerUserID), NULL, OLD.ofpOwnerUserID))); END IF;
  IF ISNULL(OLD.ofpVoucherID) != ISNULL(NEW.ofpVoucherID) OR OLD.ofpVoucherID != NEW.ofpVoucherID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpVoucherID", IF(ISNULL(OLD.ofpVoucherID), NULL, OLD.ofpVoucherID))); END IF;
  IF ISNULL(OLD.ofpBankOrCart) != ISNULL(NEW.ofpBankOrCart) OR OLD.ofpBankOrCart != NEW.ofpBankOrCart THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ofpBankOrCart", IF(ISNULL(OLD.ofpBankOrCart), NULL, OLD.ofpBankOrCart))); END IF;
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

	}

	public function safeDown()
	{
		echo "m240713_153220_aaa_add_unique_key_to_offlinepayment cannot be reverted.\n";
		return false;
	}

}
