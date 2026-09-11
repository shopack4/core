<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m260908_195739_aaa_change_walRemainedAmount_to_signed extends Migration
{
	public function safeUp()
	{
		$this->execute(
			<<<SQL
ALTER TABLE `tbl_AAA_Wallet`
	CHANGE COLUMN `walRemainedAmount` `walRemainedAmount` DOUBLE NOT NULL AFTER `walIsDefault`;
SQL
		);

		$this->execute(
			<<<SQL
ALTER TABLE `tbl_AAA_WalletTransaction`
	CHANGE COLUMN `wtrAmount` `wtrDipositAmount` DOUBLE NULL DEFAULT NULL AFTER `wtrOfflinePaymentID`,
	ADD COLUMN `wtrWithdrawalAmount` DOUBLE UNSIGNED NULL DEFAULT NULL AFTER `wtrDipositAmount`;
SQL
		);

		$this->execute("DROP TRIGGER IF EXISTS trg_updatelog_tbl_AAA_WalletTransaction;");
		$this->execute(
			<<<SQL
DELIMITER ;;
CREATE TRIGGER trg_updatelog_tbl_AAA_WalletTransaction AFTER UPDATE ON tbl_AAA_WalletTransaction FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.wtrUUID) != ISNULL(NEW.wtrUUID) OR OLD.wtrUUID != NEW.wtrUUID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("wtrUUID", IF(ISNULL(OLD.wtrUUID), NULL, OLD.wtrUUID))); END IF;
  IF ISNULL(OLD.wtrWalletID) != ISNULL(NEW.wtrWalletID) OR OLD.wtrWalletID != NEW.wtrWalletID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("wtrWalletID", IF(ISNULL(OLD.wtrWalletID), NULL, OLD.wtrWalletID))); END IF;
  IF ISNULL(OLD.wtrVoucherID) != ISNULL(NEW.wtrVoucherID) OR OLD.wtrVoucherID != NEW.wtrVoucherID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("wtrVoucherID", IF(ISNULL(OLD.wtrVoucherID), NULL, OLD.wtrVoucherID))); END IF;
  IF ISNULL(OLD.wtrOnlinePaymentID) != ISNULL(NEW.wtrOnlinePaymentID) OR OLD.wtrOnlinePaymentID != NEW.wtrOnlinePaymentID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("wtrOnlinePaymentID", IF(ISNULL(OLD.wtrOnlinePaymentID), NULL, OLD.wtrOnlinePaymentID))); END IF;
  IF ISNULL(OLD.wtrOfflinePaymentID) != ISNULL(NEW.wtrOfflinePaymentID) OR OLD.wtrOfflinePaymentID != NEW.wtrOfflinePaymentID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("wtrOfflinePaymentID", IF(ISNULL(OLD.wtrOfflinePaymentID), NULL, OLD.wtrOfflinePaymentID))); END IF;
  IF ISNULL(OLD.wtrDipositAmount) != ISNULL(NEW.wtrDipositAmount) OR OLD.wtrDipositAmount != NEW.wtrDipositAmount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("wtrDipositAmount", IF(ISNULL(OLD.wtrDipositAmount), NULL, OLD.wtrDipositAmount))); END IF;
  IF ISNULL(OLD.wtrWithdrawalAmount) != ISNULL(NEW.wtrWithdrawalAmount) OR OLD.wtrWithdrawalAmount != NEW.wtrWithdrawalAmount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("wtrWithdrawalAmount", IF(ISNULL(OLD.wtrWithdrawalAmount), NULL, OLD.wtrWithdrawalAmount))); END IF;
  IF ISNULL(OLD.wtrStatus) != ISNULL(NEW.wtrStatus) OR OLD.wtrStatus != NEW.wtrStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("wtrStatus", IF(ISNULL(OLD.wtrStatus), NULL, OLD.wtrStatus))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.wtrUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
        SET atlBy     = NEW.wtrUpdatedBy
          , atlAction = "UPDATE"
          , atlTarget = "tbl_AAA_WalletTransaction"
          , atlInfo   = JSON_OBJECT("wtrID", OLD.wtrID, "old", Changes);
  END IF;
END;;
DELIMITER ;
SQL
		);

		$this->execute(
			<<<SQL
UPDATE tbl_AAA_WalletTransaction
	SET wtrWithdrawalAmount = ABS(wtrDipositAmount)
	WHERE wtrDipositAmount < 0;
SQL
		);

		$this->execute(
			<<<SQL
ALTER TABLE `tbl_AAA_WalletTransaction`
	CHANGE COLUMN `wtrDipositAmount` `wtrDipositAmount` DOUBLE UNSIGNED NULL DEFAULT NULL AFTER `wtrOfflinePaymentID`;
SQL
		);
	}

	public function safeDown()
	{
		echo "m260908_195739_aaa_change_walRemainedAmount_to_signed cannot be reverted.\n";
		return false;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{
	}

	public function down()
	{
		echo "m260908_195739_aaa_change_walRemainedAmount_to_signed cannot be reverted.\n";
		return false;
	}
	*/
}
