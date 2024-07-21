<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use yii\db\Expression;
use shopack\base\common\db\Migration;

class m240713_153200_cmn_create_tbl_language extends Migration
{
	public function safeUp()
	{
		// `lngIsPreferred` BIT(1) NOT NULL DEFAULT 0,

    $this->execute(<<<SQL
CREATE TABLE `tbl_CMN_Language` (
	`lngID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`lngUUID` VARCHAR(38) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`lngLanguageCode` CHAR(5) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`lngCountryCode` CHAR(5) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`lngName` VARCHAR(64) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`lngStatus` CHAR(1) NOT NULL DEFAULT 'A' COMMENT 'A:Active, R:Removed' COLLATE 'utf8mb4_unicode_ci',
	`lngCreatedAt` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
	`lngCreatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`lngUpdatedAt` DATETIME NULL DEFAULT NULL,
	`lngUpdatedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`lngRemovedAt` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`lngRemovedBy` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`lngID`) USING BTREE,
	UNIQUE INDEX `lngLanguageCode_lngCountryCode` (`lngLanguageCode`, `lngCountryCode`) USING BTREE,
	INDEX `lngCreatedAt` (`lngCreatedAt`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
		);

		$this->execute("DROP TRIGGER IF EXISTS `trg_tbl_CMN_Language_before_insert`;");
    $this->execute(<<<SQL
CREATE TRIGGER `trg_tbl_CMN_Language_before_insert` BEFORE INSERT ON `tbl_CMN_Language` FOR EACH ROW BEGIN
	SET NEW.lngLanguageCode = LOWER(NEW.lngLanguageCode);

	IF NEW.lngCountryCode IS NOT NULL THEN
		SET NEW.lngCountryCode = UPPER(NEW.lngCountryCode);
	END IF;
END
SQL
		);

		$this->execute("DROP TRIGGER IF EXISTS `trg_tbl_CMN_Language_before_update`;");
    $this->execute(<<<SQL
CREATE TRIGGER `trg_tbl_CMN_Language_before_update` BEFORE UPDATE ON `tbl_CMN_Language` FOR EACH ROW BEGIN
	SET NEW.lngLanguageCode = LOWER(NEW.lngLanguageCode);

	IF NEW.lngCountryCode IS NOT NULL THEN
		SET NEW.lngCountryCode = UPPER(NEW.lngCountryCode);
	END IF;
END
SQL
		);

    $this->execute(<<<SQL
ALTER TABLE `tbl_CMN_Language`
	ADD COLUMN `lngIsRTL` BIT NOT NULL DEFAULT 0 AFTER `lngName`;
SQL
		);

    $this->execute("DROP TRIGGER IF EXISTS trg_updatelog_tbl_CMN_Language;");
    $this->execute(<<<SQL
CREATE TRIGGER trg_updatelog_tbl_CMN_Language AFTER UPDATE ON tbl_CMN_Language FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.lngUUID) != ISNULL(NEW.lngUUID) OR OLD.lngUUID != NEW.lngUUID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("lngUUID", IF(ISNULL(OLD.lngUUID), NULL, OLD.lngUUID))); END IF;
  IF ISNULL(OLD.lngLanguageCode) != ISNULL(NEW.lngLanguageCode) OR OLD.lngLanguageCode != NEW.lngLanguageCode THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("lngLanguageCode", IF(ISNULL(OLD.lngLanguageCode), NULL, OLD.lngLanguageCode))); END IF;
  IF ISNULL(OLD.lngCountryCode) != ISNULL(NEW.lngCountryCode) OR OLD.lngCountryCode != NEW.lngCountryCode THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("lngCountryCode", IF(ISNULL(OLD.lngCountryCode), NULL, OLD.lngCountryCode))); END IF;
  IF ISNULL(OLD.lngName) != ISNULL(NEW.lngName) OR OLD.lngName != NEW.lngName THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("lngName", IF(ISNULL(OLD.lngName), NULL, OLD.lngName))); END IF;
  IF ISNULL(OLD.lngIsRTL) != ISNULL(NEW.lngIsRTL) OR OLD.lngIsRTL != NEW.lngIsRTL THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("lngIsRTL", IF(ISNULL(OLD.lngIsRTL), NULL, OLD.lngIsRTL))); END IF;
  IF ISNULL(OLD.lngStatus) != ISNULL(NEW.lngStatus) OR OLD.lngStatus != NEW.lngStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("lngStatus", IF(ISNULL(OLD.lngStatus), NULL, OLD.lngStatus))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.lngUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
        SET atlBy     = NEW.lngUpdatedBy
          , atlAction = "UPDATE"
          , atlTarget = "tbl_CMN_Language"
          , atlInfo   = JSON_OBJECT("lngID", OLD.lngID, "old", Changes);
  END IF;
END
SQL
		);

    $this->batchInsertIgnore('tbl_CMN_Language', [
      'lngUUID',
      'lngLanguageCode',
			'lngCountryCode',
			'lngName',
			'lngIsRTL',
			// 'lngIsPreferred'
    ], [
      [
				/* lngUUID         */ new Expression('UUID()'),
				/* lngLanguageCode */ 'en',
				/* lngCountryCode  */ NULL,
				/* lngName         */ 'English',
				/* lngIsRTL        */ 0,
				// /* lngIsPreferred  */ 1,
      ],
      [
				/* lngUUID         */ new Expression('UUID()'),
				/* lngLanguageCode */ 'fa',
				/* lngCountryCode  */ NULL,
				/* lngName         */ 'فارسی',
				/* lngIsRTL        */ 1,
				// /* lngIsPreferred  */ 0,
      ],
		]);

	}

	public function safeDown()
	{
		echo "m240715_073602_aaa_create_tbl_language cannot be reverted.\n";
		return false;
	}

}
