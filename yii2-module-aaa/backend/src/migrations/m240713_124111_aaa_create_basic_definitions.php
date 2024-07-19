<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m240713_124111_aaa_create_basic_definitions extends Migration
{
	public function safeUp()
	{
    $this->execute(<<<SQL
CREATE TABLE `tbl_AAA_BasicDefinition` (
	`bdfID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`bdfUUID` VARCHAR(38) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`bdfType` CHAR(1) NOT NULL COMMENT 'O:Offline Payment Reject Reason' COLLATE 'utf8mb4_unicode_ci',
	`bdfName` VARCHAR(64) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`bdfStatus` CHAR(1) NOT NULL DEFAULT 'A' COMMENT 'A:Active, D:Disable, R:Removed' COLLATE 'utf8mb4_unicode_ci',
	`bdfCreatedAt` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
	`bdfCreatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`bdfUpdatedAt` DATETIME NULL DEFAULT NULL,
	`bdfUpdatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`bdfRemovedAt` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`bdfRemovedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`bdfID`) USING BTREE,
	UNIQUE INDEX `bdfUUID` (`bdfUUID`) USING BTREE,
	INDEX `bdfCreatedAt` (`bdfCreatedAt`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
		);

    $this->execute(<<<SQL
ALTER TABLE `tbl_AAA_BasicDefinition`
	ADD COLUMN `bdfI18NData` JSON NULL AFTER `bdfName`;
SQL
		);

    $this->alterColumn('tbl_AAA_BasicDefinition', 'bdfI18NData', $this->json());

    $this->execute(<<<SQL
INSERT INTO `tbl_AAA_BasicDefinition` (bdfUUID, bdfType, bdfName, bdfI18NData)
     VALUES (UUID(), 'O', 'Invalid Image',            '{"fa": {"bdfName": "تصویر اشتباه است"}}')
          , (UUID(), 'O', 'Invalid Target Bank',      '{"fa": {"bdfName": "بانک مقصد اشتباه است"}}')
          , (UUID(), 'O', 'Invalid Track Number',     '{"fa": {"bdfName": "شماره پیگیری اشتباه است"}}')
          , (UUID(), 'O', 'Invalid Reference Number', '{"fa": {"bdfName": "شماره مرجع اشتباه است"}}')
          , (UUID(), 'O', 'Invalid Amount',           '{"fa": {"bdfName": "مبلغ اشتباه است"}}')
          , (UUID(), 'O', 'Invalid Pay Date',         '{"fa": {"bdfName": "تاریخ پرداخت اشتباه است"}}')
;
SQL
		);

		$this->execute("DROP TRIGGER IF EXISTS `trg_updatelog_tbl_AAA_BasicDefinition`;");
    $this->execute(<<<SQL
CREATE TRIGGER trg_updatelog_tbl_AAA_BasicDefinition AFTER UPDATE ON tbl_AAA_BasicDefinition FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.bdfUUID) != ISNULL(NEW.bdfUUID) OR OLD.bdfUUID != NEW.bdfUUID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("bdfUUID", IF(ISNULL(OLD.bdfUUID), NULL, OLD.bdfUUID))); END IF;
  IF ISNULL(OLD.bdfType) != ISNULL(NEW.bdfType) OR OLD.bdfType != NEW.bdfType THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("bdfType", IF(ISNULL(OLD.bdfType), NULL, OLD.bdfType))); END IF;
  IF ISNULL(OLD.bdfName) != ISNULL(NEW.bdfName) OR OLD.bdfName != NEW.bdfName THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("bdfName", IF(ISNULL(OLD.bdfName), NULL, OLD.bdfName))); END IF;
  IF ISNULL(OLD.bdfI18NData) != ISNULL(NEW.bdfI18NData) OR OLD.bdfI18NData != NEW.bdfI18NData THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("bdfI18NData", IF(ISNULL(OLD.bdfI18NData), NULL, OLD.bdfI18NData))); END IF;
  IF ISNULL(OLD.bdfStatus) != ISNULL(NEW.bdfStatus) OR OLD.bdfStatus != NEW.bdfStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("bdfStatus", IF(ISNULL(OLD.bdfStatus), NULL, OLD.bdfStatus))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.bdfUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
        SET atlBy     = NEW.bdfUpdatedBy
          , atlAction = "UPDATE"
          , atlTarget = "tbl_AAA_BasicDefinition"
          , atlInfo   = JSON_OBJECT("bdfID", OLD.bdfID, "old", Changes);
  END IF;
END
SQL
		);

	}

	public function safeDown()
	{
		echo "m240713_124111_aaa_create_basic_definitions cannot be reverted.\n";
		return false;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{
	}

	public function down()
	{
		echo "m240713_124111_aaa_create_basic_definitions cannot be reverted.\n";
		return false;
	}
	*/

}
