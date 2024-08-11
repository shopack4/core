<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m240805_104352_aaa_add_lockedat_and_some_fields_to_tbl_session extends Migration
{
	public function safeUp()
	{
    $this->execute(<<<SQL
ALTER TABLE `tbl_AAA_Session`
	ADD COLUMN `ssnIPv4` INT UNSIGNED NULL DEFAULT NULL AFTER `ssnUserID`,
	ADD COLUMN `ssnInfo` JSON NULL AFTER `ssnIPv4`,
	CHANGE COLUMN `ssnExpireAt` `ssnTokenExpireAt` DATETIME NULL DEFAULT NULL AFTER `ssnJWTMD5`,
	ADD COLUMN `ssnSessionExpireAt` DATETIME NULL DEFAULT NULL AFTER `ssnTokenExpireAt`,
	ADD COLUMN `ssnOldJwt` VARCHAR(2048) NULL DEFAULT NULL AFTER `ssnSessionExpireAt`,
	ADD COLUMN `ssnRefreshedAt` DATETIME NULL DEFAULT NULL AFTER `ssnOldJwt`,
	ADD COLUMN `ssnRefreshCount` INT UNSIGNED NULL DEFAULT NULL AFTER `ssnRefreshedAt`,
	ADD COLUMN `ssnLockedAt` DATETIME NULL DEFAULT NULL AFTER `ssnRefreshCount`,
	ADD COLUMN `ssnLockedBy` VARCHAR(64) NULL DEFAULT NULL AFTER `ssnLockedAt`,
	CHANGE COLUMN `ssnStatus` `ssnStatus` CHAR(1) NOT NULL DEFAULT 'P' COMMENT 'P:Pending, A:Active, R:Removed' COLLATE 'utf8mb4_unicode_ci' AFTER `ssnLockedBy`;
SQL
		);
    $this->alterColumn('tbl_AAA_Session', 'ssnInfo', $this->json());

    $this->execute("DROP TRIGGER IF EXISTS trg_updatelog_tbl_AAA_Session;");
    $this->execute(<<<SQL
CREATE TRIGGER trg_updatelog_tbl_AAA_Session AFTER UPDATE ON tbl_AAA_Session FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.ssnUUID) != ISNULL(NEW.ssnUUID) OR OLD.ssnUUID != NEW.ssnUUID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ssnUUID", IF(ISNULL(OLD.ssnUUID), NULL, OLD.ssnUUID))); END IF;
  IF ISNULL(OLD.ssnUserID) != ISNULL(NEW.ssnUserID) OR OLD.ssnUserID != NEW.ssnUserID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ssnUserID", IF(ISNULL(OLD.ssnUserID), NULL, OLD.ssnUserID))); END IF;
  IF ISNULL(OLD.ssnIPv4) != ISNULL(NEW.ssnIPv4) OR OLD.ssnIPv4 != NEW.ssnIPv4 THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ssnIPv4", IF(ISNULL(OLD.ssnIPv4), NULL, OLD.ssnIPv4))); END IF;
  IF ISNULL(OLD.ssnInfo) != ISNULL(NEW.ssnInfo) OR OLD.ssnInfo != NEW.ssnInfo THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ssnInfo", IF(ISNULL(OLD.ssnInfo), NULL, OLD.ssnInfo))); END IF;
  IF ISNULL(OLD.ssnJWT) != ISNULL(NEW.ssnJWT) OR OLD.ssnJWT != NEW.ssnJWT THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ssnJWT", IF(ISNULL(OLD.ssnJWT), NULL, OLD.ssnJWT))); END IF;
  IF ISNULL(OLD.ssnJWTMD5) != ISNULL(NEW.ssnJWTMD5) OR OLD.ssnJWTMD5 != NEW.ssnJWTMD5 THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ssnJWTMD5", IF(ISNULL(OLD.ssnJWTMD5), NULL, OLD.ssnJWTMD5))); END IF;
  IF ISNULL(OLD.ssnTokenExpireAt) != ISNULL(NEW.ssnTokenExpireAt) OR OLD.ssnTokenExpireAt != NEW.ssnTokenExpireAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ssnTokenExpireAt", IF(ISNULL(OLD.ssnTokenExpireAt), NULL, OLD.ssnTokenExpireAt))); END IF;
  IF ISNULL(OLD.ssnSessionExpireAt) != ISNULL(NEW.ssnSessionExpireAt) OR OLD.ssnSessionExpireAt != NEW.ssnSessionExpireAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ssnSessionExpireAt", IF(ISNULL(OLD.ssnSessionExpireAt), NULL, OLD.ssnSessionExpireAt))); END IF;
  IF ISNULL(OLD.ssnOldJwt) != ISNULL(NEW.ssnOldJwt) OR OLD.ssnOldJwt != NEW.ssnOldJwt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ssnOldJwt", IF(ISNULL(OLD.ssnOldJwt), NULL, OLD.ssnOldJwt))); END IF;
  IF ISNULL(OLD.ssnRefreshedAt) != ISNULL(NEW.ssnRefreshedAt) OR OLD.ssnRefreshedAt != NEW.ssnRefreshedAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ssnRefreshedAt", IF(ISNULL(OLD.ssnRefreshedAt), NULL, OLD.ssnRefreshedAt))); END IF;
  IF ISNULL(OLD.ssnRefreshCount) != ISNULL(NEW.ssnRefreshCount) OR OLD.ssnRefreshCount != NEW.ssnRefreshCount THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ssnRefreshCount", IF(ISNULL(OLD.ssnRefreshCount), NULL, OLD.ssnRefreshCount))); END IF;
  IF ISNULL(OLD.ssnLockedAt) != ISNULL(NEW.ssnLockedAt) OR OLD.ssnLockedAt != NEW.ssnLockedAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ssnLockedAt", IF(ISNULL(OLD.ssnLockedAt), NULL, OLD.ssnLockedAt))); END IF;
  IF ISNULL(OLD.ssnLockedBy) != ISNULL(NEW.ssnLockedBy) OR OLD.ssnLockedBy != NEW.ssnLockedBy THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ssnLockedBy", IF(ISNULL(OLD.ssnLockedBy), NULL, OLD.ssnLockedBy))); END IF;
  IF ISNULL(OLD.ssnStatus) != ISNULL(NEW.ssnStatus) OR OLD.ssnStatus != NEW.ssnStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ssnStatus", IF(ISNULL(OLD.ssnStatus), NULL, OLD.ssnStatus))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.ssnUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
        SET atlBy     = NEW.ssnUpdatedBy
          , atlAction = "UPDATE"
          , atlTarget = "tbl_AAA_Session"
          , atlInfo   = JSON_OBJECT("ssnID", OLD.ssnID, "old", Changes);
  END IF;
END
SQL
		);

	}

	public function safeDown()
	{
		echo "m240805_104352_aaa_add_lockedat_and_some_fields_to_tbl_session cannot be reverted.\n";
		return false;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{
	}

	public function down()
	{
		echo "m240805_104352_aaa_add_lockedat_and_some_fields_to_tbl_session cannot be reverted.\n";
		return false;
	}
	*/

}
