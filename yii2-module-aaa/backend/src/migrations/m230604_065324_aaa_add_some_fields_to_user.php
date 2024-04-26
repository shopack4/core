<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

/**
 * Class m230604_065324_aaa_add_some_fields_to_user
 */
class m230604_065324_aaa_add_some_fields_to_user extends Migration
{
	public function safeUp()
	{
		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_User`
	ADD COLUMN `usrFatherName` VARCHAR(128) NULL DEFAULT NULL AFTER `usrLastName_en`,
	ADD COLUMN `usrFatherName_en` VARCHAR(128) NULL DEFAULT NULL AFTER `usrFatherName`,
	ADD COLUMN `usrBirthCertID` VARCHAR(16) NULL DEFAULT NULL AFTER `usrSSID`,
	ADD COLUMN `usrBirthCityID` MEDIUMINT UNSIGNED NULL DEFAULT NULL AFTER `usrBirthDate`,
	ADD COLUMN `usrPhones` VARCHAR(1024) NULL DEFAULT NULL AFTER `usrZipCode`,
	ADD COLUMN `usrWorkAddress` VARCHAR(2048) NULL DEFAULT NULL AFTER `usrPhones`,
	ADD COLUMN `usrWorkPhones` VARCHAR(1024) NULL DEFAULT NULL AFTER `usrWorkAddress`,
	ADD COLUMN `usrWebsite` VARCHAR(1024) NULL DEFAULT NULL AFTER `usrWorkPhones`;
SQL
		);

	}

	public function safeDown()
	{
		echo "m230604_065324_aaa_add_some_fields_to_user cannot be reverted.\n";
		return false;
	}

}
