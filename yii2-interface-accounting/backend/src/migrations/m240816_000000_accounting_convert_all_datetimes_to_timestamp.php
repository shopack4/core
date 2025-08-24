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
ALTER TABLE `tbl_convert`
	CHANGE COLUMN `at` `at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `lastID`;
SQL
		);
/*
		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_AssetUsage`
	CHANGE COLUMN `usgLastDateTime` `usgLastDateTime` TIMESTAMP NOT NULL AFTER `usgResolution`,
	CHANGE COLUMN `usgCreatedAt` `usgCreatedAt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `usgUniqueMD5`,
	CHANGE COLUMN `usgUpdatedAt` `usgUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `usgCreatedBy`;
SQL
		);
*/
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

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_BasicDefinition`
	CHANGE COLUMN `bdfCreatedAt` `bdfCreatedAt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `bdfStatus`,
	CHANGE COLUMN `bdfUpdatedAt` `bdfUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `bdfCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Document`
	CHANGE COLUMN `docCreatedAt` `docCreatedAt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `docStatus`,
	CHANGE COLUMN `docUpdatedAt` `docUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `docCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Kanoon`
	CHANGE COLUMN `knnCreatedAt` `knnCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `knnStatus`,
	CHANGE COLUMN `knnUpdatedAt` `knnUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `knnCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_MasterInsurer`
	CHANGE COLUMN `minsCreatedAt` `minsCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `minsStatus`,
	CHANGE COLUMN `minsUpdatedAt` `minsUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `minsCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_MasterInsurerType`
	CHANGE COLUMN `minstypCreatedAt` `minstypCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `minstypStatus`,
	CHANGE COLUMN `minstypUpdatedAt` `minstypUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `minstypCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Member`
	CHANGE COLUMN `mbrAcceptedAt` `mbrAcceptedAt` TIMESTAMP NULL DEFAULT NULL AFTER `mbrRegisterCode`,
	CHANGE COLUMN `mbrCreatedAt` `mbrCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `mbrStatus`,
	CHANGE COLUMN `mbrUpdatedAt` `mbrUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `mbrCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_MemberGroup`
	CHANGE COLUMN `mgpCreatedAt` `mgpCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `mgpStatus`,
	CHANGE COLUMN `mgpUpdatedAt` `mgpUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `mgpCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_MemberMasterInsDoc`
	CHANGE COLUMN `mbrminsdocDocDate` `mbrminsdocDocDate` DATE NULL DEFAULT NULL AFTER `mbrminsdocDocNumber`,
	CHANGE COLUMN `mbrminsdocCreatedAt` `mbrminsdocCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `mbrminsdocStatus`,
	CHANGE COLUMN `mbrminsdocUpdatedAt` `mbrminsdocUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `mbrminsdocCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_MemberMasterInsuranceHistory`
	CHANGE COLUMN `mbrminshstIssuanceDate` `mbrminshstIssuanceDate` DATE NULL DEFAULT NULL AFTER `mbrminshstCoName`,
	CHANGE COLUMN `mbrminshstCreatedAt` `mbrminshstCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `mbrminshstIssuanceDate`,
	CHANGE COLUMN `mbrminshstUpdatedAt` `mbrminshstUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `mbrminshstCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_MemberSponsorship`
	CHANGE COLUMN `mbrspsBirthDate` `mbrspsBirthDate` DATE NULL DEFAULT NULL AFTER `mbrspsFatherName`,
	CHANGE COLUMN `mbrspsCreatedAt` `mbrspsCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `mbrspsInsuranceCode`,
	CHANGE COLUMN `mbrspsUpdatedAt` `mbrspsUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `mbrspsCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_MemberSupplementaryInsDoc`
	CHANGE COLUMN `mbrsinsdocDocDate` `mbrsinsdocDocDate` DATE NULL DEFAULT NULL AFTER `mbrsinsdocDocNumber`,
	CHANGE COLUMN `mbrsinsdocCreatedAt` `mbrsinsdocCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `mbrsinsdocStatus`,
	CHANGE COLUMN `mbrsinsdocUpdatedAt` `mbrsinsdocUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `mbrsinsdocCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Member_Document`
	CHANGE COLUMN `mbrdocCreatedAt` `mbrdocCreatedAt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `mbrdocStatus`,
	CHANGE COLUMN `mbrdocUpdatedAt` `mbrdocUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `mbrdocCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Member_Kanoon`
	CHANGE COLUMN `mbrknnAcceptedAt` `mbrknnAcceptedAt` TIMESTAMP NULL DEFAULT NULL AFTER `mbrknnHistory`,
	CHANGE COLUMN `mbrknnCreatedAt` `mbrknnCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `mbrknnStatus`,
	CHANGE COLUMN `mbrknnUpdatedAt` `mbrknnUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `mbrknnCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Member_MemberGroup`
	CHANGE COLUMN `mbrmgpStartAt` `mbrmgpStartAt` TIMESTAMP NULL DEFAULT NULL AFTER `mbrmgpMemberGroupID`,
	CHANGE COLUMN `mbrmgpEndAt` `mbrmgpEndAt` TIMESTAMP NULL DEFAULT NULL AFTER `mbrmgpStartAt`,
	CHANGE COLUMN `mbrmgpCreatedAt` `mbrmgpCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `mbrmgpEndAt`,
	CHANGE COLUMN `mbrmgpUpdatedAt` `mbrmgpUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `mbrmgpCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Member_Specialty`
	CHANGE COLUMN `mbrspcCreatedAt` `mbrspcCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `mbrspcDesc`,
	CHANGE COLUMN `mbrspcUpdatedAt` `mbrspcUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `mbrspcCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Report`
	CHANGE COLUMN `rptCreatedAt` `rptCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `rptStatus`,
	CHANGE COLUMN `rptUpdatedAt` `rptUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `rptCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Specialty`
	CHANGE COLUMN `spcCreatedAt` `spcCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `spcStatus`,
	CHANGE COLUMN `spcUpdatedAt` `spcUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `spcCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_SupplementaryInsurer`
	CHANGE COLUMN `sinsCreatedAt` `sinsCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `sinsStatus`,
	CHANGE COLUMN `sinsUpdatedAt` `sinsUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `sinsCreatedBy`;
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
