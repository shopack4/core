<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m230324_135000_aaa_add_uuid_to_users extends Migration
{
	public function safeUp()
	{
    $this->execute(<<<SQL
ALTER TABLE `tbl_AAA_User`
	ADD COLUMN `usrUUID` VARCHAR(38) NULL AFTER `usrID`;
SQL
		);

    $this->execute(<<<SQL
UPDATE tbl_AAA_User
	SET usrUUID = UUID()
	WHERE usrUUID IS NULL;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_User`
	CHANGE COLUMN `usrUUID` `usrUUID` VARCHAR(38) NOT NULL COLLATE 'utf8mb4_unicode_ci' AFTER `usrID`;
SQL
		);
	}

	public function safeDown()
	{
		echo "m230324_135000_aaa_add_uuid_to_users cannot be reverted.\n";
		return false;
	}

}
