<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m231101_074125_aaa_create_useraccessgroup extends Migration
{
  public function safeUp()
  {
		$this->execute(<<<SQLSTR
CREATE TABLE `tbl_AAA_AccessGroup` (
	`agpID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`agpUUID` VARCHAR(38) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`agpName` VARCHAR(64) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`agpPrivs` JSON NULL DEFAULT NULL,
	`agpI18NData` JSON NULL DEFAULT NULL,
	`agpStatus` CHAR(1) NOT NULL DEFAULT 'A' COMMENT 'A:Active, R:Removed' COLLATE 'utf8mb4_unicode_ci',
	`agpCreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`agpCreatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`agpUpdatedAt` DATETIME NULL DEFAULT NULL,
	`agpUpdatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`agpRemovedAt` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`agpRemovedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`agpID`) USING BTREE,
	UNIQUE INDEX `agpUUID` (`agpUUID`) USING BTREE,
	INDEX `FK_tbl_AAA_AccessGroup_tbl_AAA_User_creator` (`agpCreatedBy`) USING BTREE,
	INDEX `FK_tbl_AAA_AccessGroup_tbl_AAA_User_modifier` (`agpUpdatedBy`) USING BTREE,
	CONSTRAINT `FK_tbl_AAA_AccessGroup_tbl_AAA_User_creator` FOREIGN KEY (`agpCreatedBy`) REFERENCES `tbl_AAA_User` (`usrID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_tbl_AAA_AccessGroup_tbl_AAA_User_modifier` FOREIGN KEY (`agpUpdatedBy`) REFERENCES `tbl_AAA_User` (`usrID`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;
SQLSTR
    );
    $this->alterColumn('tbl_AAA_AccessGroup', 'agpPrivs', $this->json());
    $this->alterColumn('tbl_AAA_AccessGroup', 'agpI18NData', $this->json());

    $this->execute(<<<SQLSTR
CREATE TABLE `tbl_AAA_User_AccessGroup` (
	`usragpID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`usragpUUID` VARCHAR(38) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`usragpUserID` BIGINT(20) UNSIGNED NOT NULL,
	`usragpAccessGroupID` INT(10) UNSIGNED NOT NULL,
	`usragpStartAt` DATETIME NULL DEFAULT NULL,
	`usragpEndAt` DATETIME NULL DEFAULT NULL,
	`usragpCreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`usragpCreatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`usragpUpdatedAt` DATETIME NULL DEFAULT NULL,
	`usragpUpdatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`usragpRemovedAt` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`usragpRemovedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`usragpID`) USING BTREE,
	UNIQUE INDEX `usragpUUID` (`usragpUUID`) USING BTREE,
	INDEX `FK_tbl_AAA_User_AccessGroup_tbl_AAA_User_creator` (`usragpCreatedBy`) USING BTREE,
	INDEX `FK_tbl_AAA_User_AccessGroup_tbl_AAA_User_modifier` (`usragpUpdatedBy`) USING BTREE,
	INDEX `FK_tbl_AAA_User_AccessGroup_tbl_AAA_User` (`usragpUserID`) USING BTREE,
	INDEX `FK_tbl_AAA_User_AccessGroup_tbl_AAA_AccessGroup` (`usragpAccessGroupID`) USING BTREE,
	CONSTRAINT `FK_tbl_AAA_User_AccessGroup_tbl_AAA_AccessGroup` FOREIGN KEY (`usragpAccessGroupID`) REFERENCES `tbl_AAA_AccessGroup` (`agpID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_tbl_AAA_User_AccessGroup_tbl_AAA_User` FOREIGN KEY (`usragpUserID`) REFERENCES `tbl_AAA_User` (`usrID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_tbl_AAA_User_AccessGroup_tbl_AAA_User_creator` FOREIGN KEY (`usragpCreatedBy`) REFERENCES `tbl_AAA_User` (`usrID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_tbl_AAA_User_AccessGroup_tbl_AAA_User_modifier` FOREIGN KEY (`usragpUpdatedBy`) REFERENCES `tbl_AAA_User` (`usrID`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;
SQLSTR
    );

    $this->execute("DROP TRIGGER IF EXISTS trg_updatelog_tbl_AAA_AccessGroup;");
    $this->execute(<<<SQLSTR
CREATE TRIGGER trg_updatelog_tbl_AAA_AccessGroup AFTER UPDATE ON tbl_AAA_AccessGroup FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.agpI18NData) != ISNULL(NEW.agpI18NData) OR OLD.agpI18NData != NEW.agpI18NData THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("agpI18NData", IF(ISNULL(OLD.agpI18NData), NULL, OLD.agpI18NData))); END IF;
  IF ISNULL(OLD.agpName) != ISNULL(NEW.agpName) OR OLD.agpName != NEW.agpName THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("agpName", IF(ISNULL(OLD.agpName), NULL, OLD.agpName))); END IF;
  IF ISNULL(OLD.agpPrivs) != ISNULL(NEW.agpPrivs) OR OLD.agpPrivs != NEW.agpPrivs THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("agpPrivs", IF(ISNULL(OLD.agpPrivs), NULL, OLD.agpPrivs))); END IF;
  IF ISNULL(OLD.agpStatus) != ISNULL(NEW.agpStatus) OR OLD.agpStatus != NEW.agpStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("agpStatus", IF(ISNULL(OLD.agpStatus), NULL, OLD.agpStatus))); END IF;
  IF ISNULL(OLD.agpUUID) != ISNULL(NEW.agpUUID) OR OLD.agpUUID != NEW.agpUUID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("agpUUID", IF(ISNULL(OLD.agpUUID), NULL, OLD.agpUUID))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.agpUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
        SET atlBy     = NEW.agpUpdatedBy
          , atlAction = "UPDATE"
          , atlTarget = "tbl_AAA_AccessGroup"
          , atlInfo   = JSON_OBJECT("agpID", OLD.agpID, "old", Changes);
  END IF;
END
SQLSTR
    );

    $this->execute("DROP TRIGGER IF EXISTS trg_updatelog_tbl_AAA_User_AccessGroup;");
    $this->execute(<<<SQLSTR
CREATE TRIGGER trg_updatelog_tbl_AAA_User_AccessGroup AFTER UPDATE ON tbl_AAA_User_AccessGroup FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.usragpAccessGroupID) != ISNULL(NEW.usragpAccessGroupID) OR OLD.usragpAccessGroupID != NEW.usragpAccessGroupID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usragpAccessGroupID", IF(ISNULL(OLD.usragpAccessGroupID), NULL, OLD.usragpAccessGroupID))); END IF;
  IF ISNULL(OLD.usragpEndAt) != ISNULL(NEW.usragpEndAt) OR OLD.usragpEndAt != NEW.usragpEndAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usragpEndAt", IF(ISNULL(OLD.usragpEndAt), NULL, OLD.usragpEndAt))); END IF;
  IF ISNULL(OLD.usragpStartAt) != ISNULL(NEW.usragpStartAt) OR OLD.usragpStartAt != NEW.usragpStartAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usragpStartAt", IF(ISNULL(OLD.usragpStartAt), NULL, OLD.usragpStartAt))); END IF;
  IF ISNULL(OLD.usragpUserID) != ISNULL(NEW.usragpUserID) OR OLD.usragpUserID != NEW.usragpUserID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usragpUserID", IF(ISNULL(OLD.usragpUserID), NULL, OLD.usragpUserID))); END IF;
  IF ISNULL(OLD.usragpUUID) != ISNULL(NEW.usragpUUID) OR OLD.usragpUUID != NEW.usragpUUID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usragpUUID", IF(ISNULL(OLD.usragpUUID), NULL, OLD.usragpUUID))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.usragpUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
        SET atlBy     = NEW.usragpUpdatedBy
          , atlAction = "UPDATE"
          , atlTarget = "tbl_AAA_User_AccessGroup"
          , atlInfo   = JSON_OBJECT("usragpID", OLD.usragpID, "old", Changes);
  END IF;
END
SQLSTR
    );

  }

  public function safeDown()
  {
    echo "m231101_074125_aaa_create_useraccessgroup cannot be reverted.\n";
    return false;
  }

}
