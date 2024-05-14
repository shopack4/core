<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\aaa\common\enums\enuVoucherType;
use shopack\base\common\db\Migration;

class m240513_064732_aaa_add_invoice_to_vchType extends Migration
{
	public function safeUp()
	{
    $this->execute(<<<SQL
ALTER TABLE `tbl_AAA_Voucher`
	CHANGE COLUMN `vchType` `vchType` CHAR(1) NOT NULL COMMENT 'B:Basket, I:Invoice, W:Withdrawal, M:Income, C:Credit, T:TransferTo, F:TransferFrom, Z:Prize' COLLATE 'utf8mb4_unicode_ci' AFTER `vchOwnerUserID`;
SQL
    );

    // $fnGetConst = function($value) { return $value; };
		$fnGetConstQouted = function($value) { return "'{$value}'"; };

		//convert 'B' to 'I' for not open baskets
    $this->execute(<<<SQL
UPDATE tbl_AAA_Voucher
   SET vchType    = {$fnGetConstQouted(enuVoucherType::Invoice)}
 WHERE vchType    = {$fnGetConstQouted(enuVoucherType::Basket)}
   AND vchStatus != {$fnGetConstQouted(enuVoucherStatus::New)}
;
SQL
    );










	}

	public function safeDown()
	{
		echo "m240513_064732_aaa_add_invoice_to_vchType cannot be reverted.\n";
		return false;
	}

}
