<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m240502_131927_aaa_create_2fa extends Migration
{
	public function safeUp()
	{
		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_User`
	ADD COLUMN `usr2FA` JSON NULL AFTER `usrMustChangePassword`;
SQL
		);
    $this->alterColumn('tbl_AAA_User', 'usr2FA', $this->json());

		$this->execute(<<<SQL

SQL
		);

		$this->execute(<<<SQL

SQL
		);

}

	public function safeDown()
	{
		echo "m240502_131927_aaa_create_2fa cannot be reverted.\n";
		return false;
	}

}
