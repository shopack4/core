<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m231021_122609_aaa_create_deliverymethod extends Migration
{
	public function safeUp()
	{

		/*

tbl_AAA_DeliveryMethod

vchDeliveryMethodID
vchDeliveryAmount

		*/


		$this->execute(<<<SQLSTR
ALTER TABLE `tbl_AAA_Voucher`
	ADD COLUMN `vchDeliveryMethodID` INT NULL DEFAULT NULL AFTER `vchItems`,
	ADD COLUMN `vchDeliveryAmount` INT(10) NULL DEFAULT NULL AFTER `vchDeliveryMethodID`;
SQLSTR
    );

		$this->execute(<<<SQLSTR
SQLSTR
    );

		$this->execute(<<<SQLSTR
SQLSTR
    );

    $this->execute("DROP TRIGGER IF EXISTS ;");
    $this->execute(<<<SQLSTR
SQLSTR
    );
	}

	public function safeDown()
	{
		echo "m231021_122609_aaa_create_deliverymethod cannot be reverted.\n";
		return false;
	}

}
