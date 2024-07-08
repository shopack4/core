<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m240708_084845_aaa_first_and_second_try_added_to_messages_and_requests extends Migration
{
	public function safeUp()
	{
    $this->execute(<<<SQL
ALTER TABLE `tbl_AAA_Message`
	CHANGE COLUMN `msgStatus` `msgStatus` CHAR(1) NOT NULL DEFAULT 'N' COMMENT 'N:New, P:Processing, S:Sent, A:First Try, B:Second Try, E:Error, R:Removed' COLLATE 'utf8mb4_unicode_ci' AFTER `msgResult`;
SQL
    );

    $this->execute(<<<SQL
UPDATE	tbl_AAA_Message
	 SET	msgStatus = 'A'
 WHERE	msgStatus = 'E'
SQL
    );

	}

	public function safeDown()
	{
		echo "m240708_084845_aaa_first_and_second_try_added_to_messages_and_requests cannot be reverted.\n";
		return false;
	}

}
