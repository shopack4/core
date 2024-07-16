<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use yii\db\Expression;
use shopack\base\common\db\Migration;

class m240713_153200_aaa_create_tbl_language extends Migration
{
	public function safeUp()
	{
    $this->execute(<<<SQL
CREATE TABLE `{{%CMN_Language}}` (
	`lngID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`lngUUID` VARCHAR(38) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`lngLanguageCode` CHAR(5) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`lngCountryCode` CHAR(5) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`lngName` VARCHAR(64) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`lngIsPreferred` BIT(1) NOT NULL DEFAULT 0,
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
CREATE TRIGGER `trg_tbl_CMN_Language_before_insert` BEFORE INSERT ON `{{%CMN_Language}}` FOR EACH ROW BEGIN
	SET NEW.lngLanguageCode = LOWER(NEW.lngLanguageCode);

	IF NEW.lngCountryCode IS NOT NULL THEN
		SET NEW.lngCountryCode = UPPER(NEW.lngCountryCode);
	END IF;
END
SQL
		);

		$this->execute("DROP TRIGGER IF EXISTS `trg_tbl_CMN_Language_before_update`;");
    $this->execute(<<<SQL
CREATE TRIGGER `trg_tbl_CMN_Language_before_update` BEFORE UPDATE ON `{{%CMN_Language}}` FOR EACH ROW BEGIN
	SET NEW.lngLanguageCode = LOWER(NEW.lngLanguageCode);

	IF NEW.lngCountryCode IS NOT NULL THEN
		SET NEW.lngCountryCode = UPPER(NEW.lngCountryCode);
	END IF;
END
SQL
		);

    $this->batchInsertIgnore('{{%CMN_Language}}', [
      'lngUUID',
      'lngLanguageCode',
			'lngCountryCode',
			'lngName',
			'lngIsPreferred'
    ], [
      [
				/* lngUUID         */ new Expression('UUID()'),
				/* lngLanguageCode */ 'en',
				/* lngCountryCode  */ NULL,
				/* lngName         */ 'English',
				/* lngIsPreferred  */ 1,
      ],
      [
				/* lngUUID         */ new Expression('UUID()'),
				/* lngLanguageCode */ 'fa',
				/* lngCountryCode  */ NULL,
				/* lngName         */ 'فارسی',
				/* lngIsPreferred  */ 0,
      ],
		]);

	}

	public function safeDown()
	{
		echo "m240715_073602_aaa_create_tbl_language cannot be reverted.\n";
		return false;
	}

}
