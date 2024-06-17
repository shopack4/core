<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\aaa\common\enums\enuVoucherType;
use shopack\base\common\db\Migration;

class m240513_064732_aaa_some_changes_to_voucher extends Migration
{
	public function safeUp()
	{
    $this->execute(<<<SQL
ALTER TABLE `tbl_AAA_Voucher`
	CHANGE COLUMN `vchType` `vchType` CHAR(1) NOT NULL COMMENT 'B:Basket, I:Invoice, W:Withdrawal, M:Income, C:Credit, T:TransferTo, F:TransferFrom, Z:Prize' COLLATE 'utf8mb4_unicode_ci' AFTER `vchOwnerUserID`,
	CHANGE COLUMN `vchStatus` `vchStatus` CHAR(1) NOT NULL DEFAULT 'N' COMMENT 'N:New, C:Canceled, W:WaitForPayment, S:Settled, F:Finshed, E:Error, R:Removed' COLLATE 'utf8mb4_unicode_ci' AFTER `vchItems`;
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

    $this->execute(<<<SQL
ALTER TABLE `tbl_AAA_Voucher`
	ADD COLUMN `vchOriginVoucherID` BIGINT(20) UNSIGNED NULL AFTER `vchOwnerUserID`,
	ADD COLUMN `vchReturnToWallet` DOUBLE UNSIGNED NULL DEFAULT NULL AFTER `vchTotalPaid`;
SQL
    );

    $this->execute(<<<SQL
ALTER TABLE `tbl_AAA_Voucher`
	ADD CONSTRAINT `FK_tbl_AAA_Voucher_tbl_AAA_Voucher` FOREIGN KEY (`vchOriginVoucherID`) REFERENCES `tbl_AAA_Voucher` (`vchID`) ON UPDATE NO ACTION ON DELETE NO ACTION;
SQL
		);




		//sys log trigger


	}

	public function safeDown()
	{
		echo "m240513_064732_aaa_some_changes_to_voucher cannot be reverted.\n";
		return false;
	}

}
