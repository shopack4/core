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

vchTotalAmount


		*/


		$this->execute(<<<SQLSTR
ALTER TABLE `tbl_AAA_Voucher`
	ADD COLUMN `vchDeliveryMethodID` INT NULL DEFAULT NULL AFTER `vchAmount`,
	ADD COLUMN `vchDeliveryAmount` INT(10) NULL DEFAULT NULL AFTER `vchDeliveryMethodID`;
SQLSTR
    );

		$this->execute(<<<SQLSTR
ALTER TABLE `tbl_AAA_Voucher`
	ADD COLUMN `vchTotalAmount` INT(10) NULL AFTER `vchDeliveryAmount`;
SQLSTR
    );

		$this->execute(<<<SQLSTR
UPDATE tbl_AAA_Voucher
	SET vchTotalAmount = vchAmount
	WHERE vchTotalAmount IS NULL;
SQLSTR
    );

    $this->execute(<<<SQLSTR
ALTER TABLE `tbl_AAA_Voucher`
	CHANGE COLUMN `vchTotalAmount` `vchTotalAmount` INT(10) NOT NULL AFTER `vchDeliveryAmount`;
SQLSTR
    );

    $this->execute(<<<SQLSTR
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
