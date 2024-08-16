<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m240816_120116_cmn_convert_all_datetimes_to_timestamp extends Migration
{
	public function safeUp()
	{
		$this->execute(<<<SQL
ALTER TABLE `tbl_CMN_Language`
	CHANGE COLUMN `lngCreatedAt` `lngCreatedAt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `lngStatus`,
	CHANGE COLUMN `lngUpdatedAt` `lngUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `lngCreatedBy`;
SQL
		);

	}

	public function safeDown()
	{
		echo "m240816_120116_cmn_convert_all_datetimes_to_timestamp cannot be reverted.\n";
		return false;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{
	}

	public function down()
	{
		echo "m240816_120116_cmn_convert_all_datetimes_to_timestamp cannot be reverted.\n";
		return false;
	}
	*/

}
