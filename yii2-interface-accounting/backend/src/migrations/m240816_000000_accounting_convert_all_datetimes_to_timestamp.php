<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\PerModuleMigration;

class m240816_000000_accounting_convert_all_datetimes_to_timestamp extends PerModuleMigration
{
	public function safeUp()
	{
		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_Discount`
	CHANGE COLUMN `dscValidFrom` `dscValidFrom` TIMESTAMP NULL DEFAULT NULL AFTER `dscCodeSerialLength`,
	CHANGE COLUMN `dscValidTo` `dscValidTo` TIMESTAMP NULL DEFAULT NULL AFTER `dscValidFrom`,
	CHANGE COLUMN `dscCreatedAt` `dscCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `dscStatus`,
	CHANGE COLUMN `dscUpdatedAt` `dscUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `dscCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_DiscountUsage`
	CHANGE COLUMN `dscusgCreatedAt` `dscusgCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `dscusgAmount`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_Product`
	CHANGE COLUMN `prdValidFromDate` `prdValidFromDate` TIMESTAMP NULL DEFAULT NULL AFTER `prdType`,
	CHANGE COLUMN `prdValidToDate` `prdValidToDate` TIMESTAMP NULL DEFAULT NULL AFTER `prdValidFromDate`,
	CHANGE COLUMN `prdCreatedAt` `prdCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `prdStatus`,
	CHANGE COLUMN `prdUpdatedAt` `prdUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `prdCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
UPDATE tbl_{{MODULE}}_Accounting_Saleable
   SET slbAvailableFromDate = '1991-03-21 00:00:00'
 WHERE slbAvailableFromDate = '1921-03-21 00:00:00'
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_Saleable`
	CHANGE COLUMN `slbAvailableFromDate` `slbAvailableFromDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `slbDesc`,
	CHANGE COLUMN `slbAvailableToDate` `slbAvailableToDate` TIMESTAMP NULL DEFAULT NULL AFTER `slbAvailableFromDate`,
	CHANGE COLUMN `slbCreatedAt` `slbCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `slbStatus`,
	CHANGE COLUMN `slbUpdatedAt` `slbUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `slbCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_SaleableFile`
	CHANGE COLUMN `slfCreatedAt` `slfCreatedAt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `slfMaxCount`,
	CHANGE COLUMN `slfUpdatedAt` `slfUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `slfCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_Unit`
	CHANGE COLUMN `untCreatedAt` `untCreatedAt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `untI18NData`,
	CHANGE COLUMN `untUpdatedAt` `untUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `untCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_UserAsset`
	CHANGE COLUMN `uasValidFromDate` `uasValidFromDate` TIMESTAMP NULL DEFAULT NULL AFTER `uasPrefered`,
	CHANGE COLUMN `uasValidToDate` `uasValidToDate` TIMESTAMP NULL DEFAULT NULL AFTER `uasValidFromDate`,
	CHANGE COLUMN `uasBreakedAt` `uasBreakedAt` TIMESTAMP NULL DEFAULT NULL AFTER `uasDurationMinutes`,
	CHANGE COLUMN `uasCreatedAt` `uasCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `uasStatus`,
	CHANGE COLUMN `uasUpdatedAt` `uasUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `uasCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_UserAsset_File`
	CHANGE COLUMN `uasuflCreatedAt` `uasuflCreatedAt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `uasuflFileID`,
	CHANGE COLUMN `uasuflUpdatedAt` `uasuflUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `uasuflCreatedBy`;
SQL
		);
	}

	public function safeDown()
	{
		echo "m240816_000000_accounting_convert_all_datetimes_to_timestamp cannot be reverted.\n";
		return false;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{
	}

	public function down()
	{
		echo "m240816_000000_accounting_convert_all_datetimes_to_timestamp cannot be reverted.\n";
		return false;
	}
	*/

}
