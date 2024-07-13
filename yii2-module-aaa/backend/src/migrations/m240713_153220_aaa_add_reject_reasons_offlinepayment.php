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
     VALUES (UUID(), 'O', 'تصویر اشتباه است', '{"en": {"bdfName": "Invalid Image"}}')
          , (UUID(), 'O', 'بانک مقصد اشتباه است', '{"en": {"bdfName": "Invalid Target Bank"}}')
          , (UUID(), 'O', 'شماره پیگیری اشتباه است', '{"en": {"bdfName": "Invalid Track Number"}}')
          , (UUID(), 'O', 'شماره مرجع اشتباه است', '{"en": {"bdfName": "Invalid Reference Number"}}')
          , (UUID(), 'O', 'مبلغ اشتباه است', '{"en": {"bdfName": "Invalid Amount"}}')
          , (UUID(), 'O', 'تاریخ پرداخت اشتباه است', '{"en": {"bdfName": "Invalid Pay Date"}}')
;
SQL
		);

    $this->execute(<<<SQL
ALTER TABLE `tbl_AAA_OfflinePayment`
	ADD COLUMN `ofpRejectReasonIDs` JSON NULL AFTER `ofpComment`;
SQL
		);

    $this->alterColumn('tbl_AAA_OfflinePayment', 'ofpRejectReasonIDs', $this->json());

    $this->execute(<<<SQL
SQL
		);

    $this->execute(<<<SQL
SQL
		);

    $this->execute(<<<SQL
ALTER TABLE `tbl_AAA_OfflinePayment`
	ADD COLUMN `ofpUniqueMD5` CHAR(32) AS (IF(`ofpStatus`='A',MD5(CONCAT_WS('_',IFNULL(`ofpTrackNumber`,'-'),IFNULL(`ofpReferenceNumber`,'-'),`ofpStatus`)),REPLACE(`ofpUUID`,'-',''))) VIRTUAL AFTER `ofpRemovedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_OfflinePayment`
	ADD UNIQUE INDEX `ofpUniqueMD5` (`ofpUniqueMD5`) USING BTREE;
SQL
    );

	}

	public function safeDown()
	{
		echo "m240713_153220_aaa_add_unique_key_to_offlinepayment cannot be reverted.\n";
		return false;
	}

}
