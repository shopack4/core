<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m231106_083526_aaa_add_discount_to_voucher extends Migration
{
  public function safeUp()
  {
    $this->execute(<<<SQL
ALTER TABLE `tbl_AAA_Voucher`
	ADD COLUMN `vchDiscountAmount` DOUBLE UNSIGNED NULL AFTER `vchAmount`;
SQL
    );

    $this->execute("DROP TRIGGER IF EXISTS trg_updatelog_tbl_AAA_Voucher;");
    $this->execute(<<<SQL
CREATE TRIGGER trg_updatelog_tbl_AAA_Voucher AFTER UPDATE ON tbl_AAA_Voucher FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.vchAmount) != ISNULL(NEW.vchAmount) OR OLD.vchAmount != NEW.vchAmount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchAmount", IF(ISNULL(OLD.vchAmount), NULL, OLD.vchAmount))); END IF;
  IF ISNULL(OLD.vchDeliveryAmount) != ISNULL(NEW.vchDeliveryAmount) OR OLD.vchDeliveryAmount != NEW.vchDeliveryAmount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchDeliveryAmount", IF(ISNULL(OLD.vchDeliveryAmount), NULL, OLD.vchDeliveryAmount))); END IF;
  IF ISNULL(OLD.vchDeliveryMethodID) != ISNULL(NEW.vchDeliveryMethodID) OR OLD.vchDeliveryMethodID != NEW.vchDeliveryMethodID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchDeliveryMethodID", IF(ISNULL(OLD.vchDeliveryMethodID), NULL, OLD.vchDeliveryMethodID))); END IF;
  IF ISNULL(OLD.vchDiscountAmount) != ISNULL(NEW.vchDiscountAmount) OR OLD.vchDiscountAmount != NEW.vchDiscountAmount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchDiscountAmount", IF(ISNULL(OLD.vchDiscountAmount), NULL, OLD.vchDiscountAmount))); END IF;
  IF ISNULL(OLD.vchItems) != ISNULL(NEW.vchItems) OR OLD.vchItems != NEW.vchItems THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchItems", IF(ISNULL(OLD.vchItems), NULL, OLD.vchItems))); END IF;
  IF ISNULL(OLD.vchOfflinePaid) != ISNULL(NEW.vchOfflinePaid) OR OLD.vchOfflinePaid != NEW.vchOfflinePaid THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchOfflinePaid", IF(ISNULL(OLD.vchOfflinePaid), NULL, OLD.vchOfflinePaid))); END IF;
  IF ISNULL(OLD.vchOnlinePaid) != ISNULL(NEW.vchOnlinePaid) OR OLD.vchOnlinePaid != NEW.vchOnlinePaid THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchOnlinePaid", IF(ISNULL(OLD.vchOnlinePaid), NULL, OLD.vchOnlinePaid))); END IF;
  IF ISNULL(OLD.vchOwnerUserID) != ISNULL(NEW.vchOwnerUserID) OR OLD.vchOwnerUserID != NEW.vchOwnerUserID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchOwnerUserID", IF(ISNULL(OLD.vchOwnerUserID), NULL, OLD.vchOwnerUserID))); END IF;
  IF ISNULL(OLD.vchPaidByWallet) != ISNULL(NEW.vchPaidByWallet) OR OLD.vchPaidByWallet != NEW.vchPaidByWallet THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchPaidByWallet", IF(ISNULL(OLD.vchPaidByWallet), NULL, OLD.vchPaidByWallet))); END IF;
  IF ISNULL(OLD.vchStatus) != ISNULL(NEW.vchStatus) OR OLD.vchStatus != NEW.vchStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchStatus", IF(ISNULL(OLD.vchStatus), NULL, OLD.vchStatus))); END IF;
  IF ISNULL(OLD.vchTotalAmount) != ISNULL(NEW.vchTotalAmount) OR OLD.vchTotalAmount != NEW.vchTotalAmount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchTotalAmount", IF(ISNULL(OLD.vchTotalAmount), NULL, OLD.vchTotalAmount))); END IF;
  IF ISNULL(OLD.vchTotalPaid) != ISNULL(NEW.vchTotalPaid) OR OLD.vchTotalPaid != NEW.vchTotalPaid THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchTotalPaid", IF(ISNULL(OLD.vchTotalPaid), NULL, OLD.vchTotalPaid))); END IF;
  IF ISNULL(OLD.vchType) != ISNULL(NEW.vchType) OR OLD.vchType != NEW.vchType THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchType", IF(ISNULL(OLD.vchType), NULL, OLD.vchType))); END IF;
  IF ISNULL(OLD.vchUUID) != ISNULL(NEW.vchUUID) OR OLD.vchUUID != NEW.vchUUID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("vchUUID", IF(ISNULL(OLD.vchUUID), NULL, OLD.vchUUID))); END IF;

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
SQL
    );

  }

  public function safeDown()
  {
    echo "m231106_083526_aaa_add_discount_to_voucher cannot be reverted.\n";
    return false;
  }

}
