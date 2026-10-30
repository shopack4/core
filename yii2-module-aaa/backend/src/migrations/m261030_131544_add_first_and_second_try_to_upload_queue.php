<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m261030_131544_add_first_and_second_try_to_upload_queue extends Migration
{
    public function safeUp()
    {
        $this->execute(
            <<<SQL
ALTER TABLE `tbl_AAA_UploadQueue`
    CHANGE COLUMN `uquStatus` `uquStatus` CHAR(1) NOT NULL DEFAULT 'N' COMMENT 'N:New, S:Stored, A:First Try, B:Second Try, E:Error, R:Removed\n' COLLATE 'utf8mb4_unicode_ci' AFTER `uquResult`;
SQL
        );
    }

    public function safeDown()
    {
        echo "m261030_131544_add_first_and_second_try_to_upload_queue cannot be reverted.\n";
        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
    }

    public function down()
    {
        echo "m261030_131544_add_first_and_second_try_to_upload_queue cannot be reverted.\n";
        return false;
    }
    */
}
