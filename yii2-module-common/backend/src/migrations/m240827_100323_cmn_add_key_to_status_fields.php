<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m240827_100323_cmn_add_key_to_status_fields extends Migration
{
	public function safeUp()
	{
		$this->execute(<<<SQL
ALTER TABLE `tbl_CMN_Language`
	ADD INDEX `lngStatus` (`lngStatus`);
SQL
		);

	}

	public function safeDown()
	{
		echo "m240827_100323_cmn_add_key_to_status_fields cannot be reverted.\n";
		return false;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{
	}

	public function down()
	{
		echo "m240827_100323_cmn_add_key_to_status_fields cannot be reverted.\n";
		return false;
	}
	*/

}
