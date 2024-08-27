<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m240827_094328_aaa_add_key_to_status_fields extends Migration
{
	public function safeUp()
	{
		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_AccessGroup`
	ADD INDEX `agpStatus` (`agpStatus`);
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_ApprovalRequest`
	ADD INDEX `aprStatus` (`aprStatus`);
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_BasicDefinition`
	ADD INDEX `bdfStatus` (`bdfStatus`);
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_ForgotPasswordRequest`
	ADD INDEX `fprStatus` (`fprStatus`);
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_Gateway`
	ADD INDEX `gtwStatus` (`gtwStatus`);
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_Message`
	ADD INDEX `msgStatus` (`msgStatus`),
	ADD INDEX `msgLockedAt` (`msgLockedAt`),
	ADD INDEX `msgLastTryAt` (`msgLastTryAt`),
	ADD INDEX `msgTypeKey` (`msgTypeKey`),
	ADD INDEX `msgTarget` (`msgTarget`);
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_MessageTemplate`
	ADD INDEX `mstStatus` (`mstStatus`);
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_OfflinePayment`
	ADD INDEX `ofpStatus` (`ofpStatus`);
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_OnlinePayment`
	ADD INDEX `onpStatus` (`onpStatus`);
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_Session`
	ADD INDEX `ssnTokenExpireAt` (`ssnTokenExpireAt`),
	ADD INDEX `ssnSessionExpireAt` (`ssnSessionExpireAt`),
	ADD INDEX `ssnStatus` (`ssnStatus`);
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_UploadFile`
	ADD INDEX `uflStatus` (`uflStatus`);
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_UploadQueue`
	ADD INDEX `uquStatus` (`uquStatus`);
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_User`
	ADD INDEX `usrStatus` (`usrStatus`);
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_Voucher`
	ADD INDEX `vchType` (`vchType`),
	ADD INDEX `vchStatus` (`vchStatus`);
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_Wallet`
	ADD INDEX `walStatus` (`walStatus`);
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_WalletTransaction`
	ADD INDEX `wtrStatus` (`wtrStatus`);
SQL
		);

	}

	public function safeDown()
	{
		echo "m240827_094328_aaa_add_key_to_status_fields cannot be reverted.\n";
		return false;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{
	}

	public function down()
	{
		echo "m240827_094328_aaa_add_key_to_status_fields cannot be reverted.\n";
		return false;
	}
	*/

}
