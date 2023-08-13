<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m230527_113001_aaa_add_guid_column_to_all extends Migration
{
	public function addUUIDTo($tableName, $prefix, $idFieldSuffix = 'ID')
	{
		$this->execute(<<<SQLSTR
ALTER TABLE `{$tableName}`
	ADD COLUMN `{$prefix}UUID` VARCHAR(38) NULL AFTER `{$prefix}{$idFieldSuffix}`;
SQLSTR
		);

		$this->execute(<<<SQLSTR
UPDATE `{$tableName}`
	SET `{$prefix}UUID` = LOWER(UUID())
	WHERE `{$prefix}UUID` IS NULL;
SQLSTR
		);

		$this->execute(<<<SQLSTR
ALTER TABLE `{$tableName}`
	CHANGE COLUMN `{$prefix}UUID` `{$prefix}UUID` VARCHAR(38) NOT NULL COLLATE 'utf8mb4_unicode_ci' AFTER `{$prefix}{$idFieldSuffix}`,
	ADD UNIQUE INDEX `{$prefix}UUID` (`{$prefix}UUID`);
SQLSTR
		);
	}

	public function safeUp()
	{
		$this->addUUIDTo('tbl_AAA_ApprovalRequest',				'apr');
		$this->addUUIDTo('tbl_AAA_ForgotPasswordRequest',	'fpr');

		//** tbl_AAA_Gateway
		$this->execute(<<<SQLSTR
ALTER TABLE `tbl_AAA_Gateway`
	DROP INDEX `gtwKey_gtwRemovedAt`;
SQLSTR
		);

		$this->execute(<<<SQLSTR
ALTER TABLE `tbl_AAA_Gateway`
	CHANGE COLUMN `gtwKey` `gtwUUID` VARCHAR(38) NOT NULL COLLATE 'utf8mb4_unicode_ci' AFTER `gtwID`,
	ADD UNIQUE INDEX `gtwUUID` (`gtwUUID`);
SQLSTR
		);

		$this->execute(<<<SQLSTR
DROP TRIGGER IF EXISTS `trg_updatelog_tbl_AAA_Gateway`;
SQLSTR
		);

		$this->execute(<<<SQLSTR
CREATE TRIGGER `trg_updatelog_tbl_AAA_Gateway` AFTER UPDATE ON `tbl_AAA_Gateway` FOR EACH ROW BEGIN
	DECLARE Changes JSON DEFAULT JSON_OBJECT();

	IF ISNULL(OLD.gtwName) != ISNULL(NEW.gtwName) OR OLD.gtwName != NEW.gtwName THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("gtwName", IF(ISNULL(OLD.gtwName), NULL, OLD.gtwName))); END IF;
	IF ISNULL(OLD.gtwUUID) != ISNULL(NEW.gtwUUID) OR OLD.gtwUUID != NEW.gtwUUID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("gtwUUID", IF(ISNULL(OLD.gtwUUID), NULL, OLD.gtwUUID))); END IF;
	IF ISNULL(OLD.gtwPluginType) != ISNULL(NEW.gtwPluginType) OR OLD.gtwPluginType != NEW.gtwPluginType THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("gtwPluginType", IF(ISNULL(OLD.gtwPluginType), NULL, OLD.gtwPluginType))); END IF;
	IF ISNULL(OLD.gtwPluginName) != ISNULL(NEW.gtwPluginName) OR OLD.gtwPluginName != NEW.gtwPluginName THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("gtwPluginName", IF(ISNULL(OLD.gtwPluginName), NULL, OLD.gtwPluginName))); END IF;
	IF ISNULL(OLD.gtwPluginParameters) != ISNULL(NEW.gtwPluginParameters) OR OLD.gtwPluginParameters != NEW.gtwPluginParameters THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("gtwPluginParameters", IF(ISNULL(OLD.gtwPluginParameters), NULL, OLD.gtwPluginParameters))); END IF;
	IF ISNULL(OLD.gtwStatus) != ISNULL(NEW.gtwStatus) OR OLD.gtwStatus != NEW.gtwStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("gtwStatus", IF(ISNULL(OLD.gtwStatus), NULL, OLD.gtwStatus))); END IF;

	IF JSON_LENGTH(Changes) > 0 THEN
/*
		IF ISNULL(NEW.gtwUpdatedBy) THEN
			SIGNAL SQLSTATE "45401"
					SET MESSAGE_TEXT = "UpdatedBy is not set";
		END IF;
*/
		INSERT INTO tbl_SYS_ActionLogs
				SET atlBy     = NEW.gtwUpdatedBy
					, atlAction = "UPDATE"
					, atlTarget = "tbl_AAA_Gateway"
					, atlInfo   = JSON_OBJECT("gtwID", OLD.gtwID, "old", Changes);
	END IF;
END ;
SQLSTR
		);

		//------------------
		$this->addUUIDTo('tbl_AAA_GeoCityOrVillage',	'ctv');
		$this->addUUIDTo('tbl_AAA_GeoCountry',				'cntr');
		$this->addUUIDTo('tbl_AAA_GeoState',					'stt');
		$this->addUUIDTo('tbl_AAA_GeoTown',						'twn');
		$this->addUUIDTo('tbl_AAA_Message',						'msg');
		$this->addUUIDTo('tbl_AAA_MessageTemplate',		'mst');

		//** tbl_AAA_OnlinePayment
		$this->execute(<<<SQLSTR
ALTER TABLE `tbl_AAA_OnlinePayment`
	ADD UNIQUE INDEX `onpUUID` (`onpUUID`);
SQLSTR
		);

		$this->addUUIDTo('tbl_AAA_Role',				'rol');
		$this->addUUIDTo('tbl_AAA_Session',			'ssn');
		$this->addUUIDTo('tbl_AAA_UploadFile',	'ufl');
		$this->addUUIDTo('tbl_AAA_UploadQueue',	'uqu');

		//** tbl_AAA_User
		$this->execute(<<<SQLSTR
ALTER TABLE `tbl_AAA_User`
	ADD UNIQUE INDEX `usrUUID` (`usrUUID`);
SQLSTR
		);

		$this->addUUIDTo('tbl_AAA_Voucher',						'vch');
		$this->addUUIDTo('tbl_AAA_Wallet',						'wal');
		$this->addUUIDTo('tbl_AAA_WalletTransaction',	'wtr');
	}

	public function safeDown()
	{
		echo "m230527_113001_aaa_add_guid_column_to_all cannot be reverted.\n";
		return false;
	}

}
