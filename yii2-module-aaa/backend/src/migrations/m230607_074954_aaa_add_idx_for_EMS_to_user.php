<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m230607_074954_aaa_add_idx_for_EMS_to_user extends Migration
{
  public function safeUp()
  {
    $this->execute(<<<SQL
ALTER TABLE `tbl_AAA_User`
  ADD INDEX `usrEmail` (`usrEmail`),
  ADD INDEX `usrMobile` (`usrMobile`),
  ADD INDEX `usrSSID` (`usrSSID`);
SQL
    );

  }

  public function safeDown()
  {
    echo "m230607_074954_aaa_add_idx_for_EMS_to_user cannot be reverted.\n";
    return false;
  }

}
