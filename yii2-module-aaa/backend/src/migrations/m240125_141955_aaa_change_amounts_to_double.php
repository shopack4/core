<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m240125_141955_aaa_change_amounts_to_double extends Migration
{
  public function safeUp()
  {
    $this->execute(<<<SQL
ALTER TABLE `tbl_AAA_DeliveryMethod`
	CHANGE COLUMN `dlvAmount` `dlvAmount` DOUBLE UNSIGNED NULL DEFAULT NULL AFTER `dlvType`,
	CHANGE COLUMN `dlvTotalUsedAmount` `dlvTotalUsedAmount` DOUBLE UNSIGNED NOT NULL AFTER `dlvTotalUsedCount`;
SQL
    );

    $this->execute(<<<SQL
ALTER TABLE `tbl_AAA_Voucher`
	CHANGE COLUMN `vchDeliveryAmount` `vchDeliveryAmount` DOUBLE UNSIGNED NULL DEFAULT NULL AFTER `vchDeliveryMethodID`,
	CHANGE COLUMN `vchTotalAmount` `vchTotalAmount` DOUBLE UNSIGNED NOT NULL AFTER `vchDeliveryAmount`;
SQL
    );

  }

  public function safeDown()
  {
    echo "m240125_141955_aaa_change_amounts_to_double cannot be reverted.\n";
    return false;
  }

}
