<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m260805_190456_aaa_add_dead_to_user extends Migration
{
    public function safeUp()
    {
        $this->execute(
            <<<SQL
ALTER TABLE `tbl_AAA_User`
    ADD COLUMN `usrDeadAt` TIMESTAMP NULL AFTER `usrMilitaryStatus`;
SQL
        );
    }

    public function safeDown()
    {
        echo "m260805_190456_aaa_add_dead_to_user cannot be reverted.\n";
        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
    }

    public function down()
    {
        echo "m260805_190456_aaa_add_dead_to_user cannot be reverted.\n";
        return false;
    }
    */
}
