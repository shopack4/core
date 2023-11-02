<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m231101_074125_aaa_create_useraccessgroup extends Migration
{
  public function safeUp()
  {

		throw new \Exception("not finished yet");








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
	CONSTRAINT `FK_tbl_AAA_User_AccessGroup_tbl_AAA_User` FOREIGN KEY (`usragpUserID`) REFERENCES `tbl_AAA_User` (`usrID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_tbl_AAA_User_AccessGroup_tbl_AAA_AccessGroup` FOREIGN KEY (`usragpAccessGroupID`) REFERENCES `tbl_AAA_AccessGroup` (`agpID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_tbl_AAA_User_AccessGroup_tbl_AAA_User_creator` FOREIGN KEY (`usragpCreatedBy`) REFERENCES `tbl_AAA_User` (`usrID`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_tbl_AAA_User_AccessGroup_tbl_AAA_User_modifier` FOREIGN KEY (`usragpUpdatedBy`) REFERENCES `tbl_AAA_User` (`usrID`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;
SQLSTR
    );

    $this->execute("DROP TRIGGER IF EXISTS ???????????????????????;");
    $this->execute(<<<SQLSTR
SQLSTR
    );

  }

  public function safeDown()
  {
    echo "m231101_074125_aaa_create_useraccessgroup cannot be reverted.\n";
    return false;
  }

}
