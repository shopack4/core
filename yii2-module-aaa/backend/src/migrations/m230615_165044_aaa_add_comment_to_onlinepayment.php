<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m230615_165044_aaa_add_comment_to_onlinepayment extends Migration
{
	public function safeUp()
	{
		$this->execute(<<<SQLSTR
ALTER TABLE `tbl_AAA_OnlinePayment`
	ADD COLUMN `onpComment` TEXT NULL AFTER `onpResult`;
SQLSTR
    );

		$this->execute("DROP TRIGGER IF EXISTS trg_updatelog_tbl_AAA_OnlinePayment;");

    $this->execute(<<<SQLSTR
CREATE TRIGGER trg_updatelog_tbl_AAA_OnlinePayment AFTER UPDATE ON tbl_AAA_OnlinePayment FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.onpUUID) != ISNULL(NEW.onpUUID) OR OLD.onpUUID != NEW.onpUUID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("onpUUID", IF(ISNULL(OLD.onpUUID), NULL, OLD.onpUUID))); END IF;
  IF ISNULL(OLD.onpGatewayID) != ISNULL(NEW.onpGatewayID) OR OLD.onpGatewayID != NEW.onpGatewayID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("onpGatewayID", IF(ISNULL(OLD.onpGatewayID), NULL, OLD.onpGatewayID))); END IF;
  IF ISNULL(OLD.onpVoucherID) != ISNULL(NEW.onpVoucherID) OR OLD.onpVoucherID != NEW.onpVoucherID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("onpVoucherID", IF(ISNULL(OLD.onpVoucherID), NULL, OLD.onpVoucherID))); END IF;
  IF ISNULL(OLD.onpAmount) != ISNULL(NEW.onpAmount) OR OLD.onpAmount != NEW.onpAmount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("onpAmount", IF(ISNULL(OLD.onpAmount), NULL, OLD.onpAmount))); END IF;
  IF ISNULL(OLD.onpCallbackUrl) != ISNULL(NEW.onpCallbackUrl) OR OLD.onpCallbackUrl != NEW.onpCallbackUrl THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("onpCallbackUrl", IF(ISNULL(OLD.onpCallbackUrl), NULL, OLD.onpCallbackUrl))); END IF;
  IF ISNULL(OLD.onpWalletID) != ISNULL(NEW.onpWalletID) OR OLD.onpWalletID != NEW.onpWalletID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("onpWalletID", IF(ISNULL(OLD.onpWalletID), NULL, OLD.onpWalletID))); END IF;
  IF ISNULL(OLD.onpTrackNumber) != ISNULL(NEW.onpTrackNumber) OR OLD.onpTrackNumber != NEW.onpTrackNumber THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("onpTrackNumber", IF(ISNULL(OLD.onpTrackNumber), NULL, OLD.onpTrackNumber))); END IF;
  IF ISNULL(OLD.onpRRN) != ISNULL(NEW.onpRRN) OR OLD.onpRRN != NEW.onpRRN THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("onpRRN", IF(ISNULL(OLD.onpRRN), NULL, OLD.onpRRN))); END IF;
  IF ISNULL(OLD.onpResult) != ISNULL(NEW.onpResult) OR OLD.onpResult != NEW.onpResult THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("onpResult", IF(ISNULL(OLD.onpResult), NULL, OLD.onpResult))); END IF;
  IF ISNULL(OLD.onpComment) != ISNULL(NEW.onpComment) OR OLD.onpComment != NEW.onpComment THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("onpComment", IF(ISNULL(OLD.onpComment), NULL, OLD.onpComment))); END IF;
  IF ISNULL(OLD.onpStatus) != ISNULL(NEW.onpStatus) OR OLD.onpStatus != NEW.onpStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("onpStatus", IF(ISNULL(OLD.onpStatus), NULL, OLD.onpStatus))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.onpUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
        SET atlBy     = NEW.onpUpdatedBy
          , atlAction = "UPDATE"
          , atlTarget = "tbl_AAA_OnlinePayment"
          , atlInfo   = JSON_OBJECT("onpID", OLD.onpID, "old", Changes);
  END IF;
END
SQLSTR
    );

	}

	public function safeDown()
	{
		echo "m230615_165044_aaa_add_comment_to_onlinepayment cannot be reverted.\n";
		return false;
	}

}
