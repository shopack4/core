<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m240816_114253_aaa_convert_all_datetimes_to_timestamp extends Migration
{
	public function safeUp()
	{
		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_AccessGroup`
	CHANGE COLUMN `agpCreatedAt` `agpCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `agpStatus`,
	CHANGE COLUMN `agpUpdatedAt` `agpUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `agpCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_ApprovalRequest`
	CHANGE COLUMN `aprLastRequestAt` `aprLastRequestAt` TIMESTAMP NOT NULL AFTER `aprCode`,
	CHANGE COLUMN `aprExpireAt` `aprExpireAt` TIMESTAMP NOT NULL AFTER `aprLastRequestAt`,
	CHANGE COLUMN `aprSentAt` `aprSentAt` TIMESTAMP NULL DEFAULT NULL AFTER `aprExpireAt`,
	CHANGE COLUMN `aprApplyAt` `aprApplyAt` TIMESTAMP NULL DEFAULT NULL AFTER `aprSentAt`,
	CHANGE COLUMN `aprCreatedAt` `aprCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `aprStatus`,
	CHANGE COLUMN `aprUpdatedAt` `aprUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `aprCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_BasicDefinition`
	CHANGE COLUMN `bdfCreatedAt` `bdfCreatedAt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `bdfStatus`,
	CHANGE COLUMN `bdfUpdatedAt` `bdfUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `bdfCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_DeliveryMethod`
	CHANGE COLUMN `dlvCreatedAt` `dlvCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `dlvStatus`,
	CHANGE COLUMN `dlvUpdatedAt` `dlvUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `dlvCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_ForgotPasswordRequest`
	CHANGE COLUMN `fprLastRequestAt` `fprLastRequestAt` TIMESTAMP NOT NULL AFTER `fprCode`,
	CHANGE COLUMN `fprExpireAt` `fprExpireAt` TIMESTAMP NOT NULL AFTER `fprLastRequestAt`,
	CHANGE COLUMN `fprSentAt` `fprSentAt` TIMESTAMP NULL DEFAULT NULL AFTER `fprExpireAt`,
	CHANGE COLUMN `fprApplyAt` `fprApplyAt` TIMESTAMP NULL DEFAULT NULL AFTER `fprSentAt`,
	CHANGE COLUMN `fprCreatedAt` `fprCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `fprStatus`,
	CHANGE COLUMN `fprUpdatedAt` `fprUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `fprCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_Gateway`
	CHANGE COLUMN `gtwCreatedAt` `gtwCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `gtwStatus`,
	CHANGE COLUMN `gtwUpdatedAt` `gtwUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `gtwCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_GeoCityOrVillage`
	CHANGE COLUMN `ctvCreatedAt` `ctvCreatedAt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `ctvType`,
	CHANGE COLUMN `ctvUpdatedAt` `ctvUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `ctvCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_GeoCountry`
	CHANGE COLUMN `cntrCreatedAt` `cntrCreatedAt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `cntrName`,
	CHANGE COLUMN `cntrUpdatedAt` `cntrUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `cntrCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_GeoState`
	CHANGE COLUMN `sttCreatedAt` `sttCreatedAt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `sttCountryID`,
	CHANGE COLUMN `sttUpdatedAt` `sttUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `sttCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_GeoTown`
	CHANGE COLUMN `twnCreatedAt` `twnCreatedAt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `twnCityID`,
	CHANGE COLUMN `twnUpdatedAt` `twnUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `twnCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_Message`
	CHANGE COLUMN `msgLockedAt` `msgLockedAt` TIMESTAMP NULL DEFAULT NULL AFTER `msgIssuer`,
	CHANGE COLUMN `msgLastTryAt` `msgLastTryAt` TIMESTAMP NULL DEFAULT NULL AFTER `msgLockedBy`,
	CHANGE COLUMN `msgSentAt` `msgSentAt` TIMESTAMP NULL DEFAULT NULL AFTER `msgLastTryAt`,
	CHANGE COLUMN `msgCreatedAt` `msgCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `msgStatus`,
	CHANGE COLUMN `msgUpdatedAt` `msgUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `msgCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_MessageTemplate`
	CHANGE COLUMN `mstCreatedAt` `mstCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `mstStatus`,
	CHANGE COLUMN `mstUpdatedAt` `mstUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `mstCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_OfflinePayment`
	CHANGE COLUMN `ofpPayDate` `ofpPayDate` TIMESTAMP NOT NULL AFTER `ofpAmount`,
	CHANGE COLUMN `ofpCreatedAt` `ofpCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `ofpStatus`,
	CHANGE COLUMN `ofpUpdatedAt` `ofpUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `ofpCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_OnlinePayment`
	CHANGE COLUMN `onpCreatedAt` `onpCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `onpStatus`,
	CHANGE COLUMN `onpUpdatedAt` `onpUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `onpCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_Role`
	CHANGE COLUMN `rolCreatedAt` `rolCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `rolPrivs`,
	CHANGE COLUMN `rolUpdatedAt` `rolUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `rolCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_Session`
	CHANGE COLUMN `ssnTokenExpireAt` `ssnTokenExpireAt` TIMESTAMP NULL DEFAULT NULL AFTER `ssnJWTMD5`,
	CHANGE COLUMN `ssnSessionExpireAt` `ssnSessionExpireAt` TIMESTAMP NULL DEFAULT NULL AFTER `ssnTokenExpireAt`,
	CHANGE COLUMN `ssnRefreshedAt` `ssnRefreshedAt` TIMESTAMP NULL DEFAULT NULL AFTER `ssnOldJwt`,
	CHANGE COLUMN `ssnLockedAt` `ssnLockedAt` TIMESTAMP NULL DEFAULT NULL AFTER `ssnRefreshCount`,
	CHANGE COLUMN `ssnCreatedAt` `ssnCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `ssnStatus`,
	CHANGE COLUMN `ssnUpdatedAt` `ssnUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `ssnCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_UploadFile`
	CHANGE COLUMN `uflCreatedAt` `uflCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `uflStatus`,
	CHANGE COLUMN `uflUpdatedAt` `uflUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `uflCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_UploadQueue`
	CHANGE COLUMN `uquCreatedAt` `uquCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `uquStatus`,
	CHANGE COLUMN `uquUpdatedAt` `uquUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `uquCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_User`
	CHANGE COLUMN `usrEmailApprovedAt` `usrEmailApprovedAt` TIMESTAMP NULL DEFAULT NULL AFTER `usrEmail`,
	CHANGE COLUMN `usrMobileApprovedAt` `usrMobileApprovedAt` TIMESTAMP NULL DEFAULT NULL AFTER `usrMobile`,
	CHANGE COLUMN `usrPasswordCreatedAt` `usrPasswordCreatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `usrPasswordHash`,
	CHANGE COLUMN `usrCreatedAt` `usrCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `usrStatus`,
	CHANGE COLUMN `usrUpdatedAt` `usrUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `usrCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_User_AccessGroup`
	CHANGE COLUMN `usragpStartAt` `usragpStartAt` TIMESTAMP NULL DEFAULT NULL AFTER `usragpAccessGroupID`,
	CHANGE COLUMN `usragpEndAt` `usragpEndAt` TIMESTAMP NULL DEFAULT NULL AFTER `usragpStartAt`,
	CHANGE COLUMN `usragpCreatedAt` `usragpCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `usragpEndAt`,
	CHANGE COLUMN `usragpUpdatedAt` `usragpUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `usragpCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_Voucher`
	CHANGE COLUMN `vchCreatedAt` `vchCreatedAt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `vchStatus`,
	CHANGE COLUMN `vchUpdatedAt` `vchUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `vchCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_Wallet`
	CHANGE COLUMN `walCreatedAt` `walCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `walStatus`,
	CHANGE COLUMN `walUpdatedAt` `walUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `walCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_AAA_WalletTransaction`
	CHANGE COLUMN `wtrCreatedAt` `wtrCreatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `wtrStatus`,
	CHANGE COLUMN `wtrUpdatedAt` `wtrUpdatedAt` TIMESTAMP NULL DEFAULT NULL AFTER `wtrCreatedBy`;
SQL
		);

		$this->execute(<<<SQL
ALTER TABLE `tbl_SYS_ActionLogs`
	CHANGE COLUMN `atlAt` `atlAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `atlInfo`;
SQL
		);

    $this->execute('DROP PROCEDURE IF EXISTS spUpdateTableTriggerAndCols;');
		$this->execute(<<<SQL
CREATE PROCEDURE `spUpdateTableTriggerAndCols`(
	IN `iSchema` VARCHAR(50),
	IN `iTable` VARCHAR(50),
	IN `iFK` BOOLEAN,
	OUT `oQueryStr` TEXT
)
Proc: BEGIN
  -- DECLARE QueryStr VARCHAR(21000) DEFAULT '';
  DECLARE TempQueryStr TEXT;
  DECLARE TriggerName VARCHAR(100);
  DECLARE vPrefix VARCHAR(50);
  DECLARE vi INTEGER DEFAULT 0;

  SET oQueryStr = '';

  CALL spFindTablePrefix(iTable, vPrefix);
  IF vPrefix IS NULL OR vPrefix = '' THEN
	  SET oQueryStr = 'Err:spFindTablePrefix';
    LEAVE Proc;
  END IF;

  /* CreatedAt */
  SELECT COUNT(1)
    INTO vi
    FROM information_schema.COLUMNS
    WHERE information_schema.COLUMNS.TABLE_SCHEMA = iSchema
      AND information_schema.COLUMNS.TABLE_NAME = iTable
      AND information_schema.COLUMNS.COLUMN_NAME = CONCAT(vPrefix, 'CreatedAt');

  IF vi = 0 THEN
    SET oQueryStr = CONCAT(oQueryStr, 'ALTER TABLE ', iTable, '
  ADD COLUMN ', vPrefix, 'CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ADD INDEX  ', vPrefix, 'CreatedAt (', vPrefix, 'CreatedAt)
;

');
  END IF;

  /* CreatedBy */
  SELECT COUNT(1)
    INTO vi
    FROM information_schema.COLUMNS
    WHERE information_schema.COLUMNS.TABLE_SCHEMA = iSchema
      AND information_schema.COLUMNS.TABLE_NAME = iTable
      AND information_schema.COLUMNS.COLUMN_NAME = CONCAT(vPrefix, 'CreatedBy');

  IF vi = 0 THEN
    SET oQueryStr = CONCAT(oQueryStr, 'ALTER TABLE ', iTable, '
  ADD COLUMN ', vPrefix, 'CreatedBy BIGINT UNSIGNED NULL DEFAULT NULL AFTER ', vPrefix, 'CreatedAt
;

');
  END IF;

  /* UpdatedAt */
  SELECT COUNT(1)
    INTO vi
    FROM information_schema.COLUMNS
    WHERE information_schema.COLUMNS.TABLE_SCHEMA = iSchema
      AND information_schema.COLUMNS.TABLE_NAME = iTable
      AND information_schema.COLUMNS.COLUMN_NAME = CONCAT(vPrefix, 'UpdatedAt');

  IF vi = 0 THEN
    SET oQueryStr = CONCAT(oQueryStr, 'ALTER TABLE ', iTable, '
  ADD COLUMN ', vPrefix, 'UpdatedAt TIMESTAMP NULL DEFAULT NULL AFTER ', vPrefix, 'CreatedBy
;

');
  END IF;

  /* UpdatedBy */
  SELECT COUNT(1)
    INTO vi
    FROM information_schema.COLUMNS
    WHERE information_schema.COLUMNS.TABLE_SCHEMA = iSchema
      AND information_schema.COLUMNS.TABLE_NAME = iTable
      AND information_schema.COLUMNS.COLUMN_NAME = CONCAT(vPrefix, 'UpdatedBy');

  IF vi = 0 THEN
    SET oQueryStr = CONCAT(oQueryStr, 'ALTER TABLE ', iTable, '
  ADD COLUMN ', vPrefix, 'UpdatedBy BIGINT UNSIGNED NULL DEFAULT NULL AFTER ', vPrefix, 'UpdatedAt
;

');
  END IF;

  /* RemovedAt */
  SELECT COUNT(1)
    INTO vi
    FROM information_schema.COLUMNS
    WHERE information_schema.COLUMNS.TABLE_SCHEMA = iSchema
      AND information_schema.COLUMNS.TABLE_NAME = iTable
      AND information_schema.COLUMNS.COLUMN_NAME = CONCAT(vPrefix, 'RemovedAt');

  IF vi = 0 THEN
    SET oQueryStr = CONCAT(oQueryStr, 'ALTER TABLE ', iTable, '
  ADD COLUMN ', vPrefix, 'RemovedAt INT UNSIGNED NOT NULL DEFAULT 0 AFTER ', vPrefix, 'UpdatedBy
;

');
  END IF;

  /* RemovedBy */
  SELECT COUNT(1)
    INTO vi
    FROM information_schema.COLUMNS
    WHERE information_schema.COLUMNS.TABLE_SCHEMA = iSchema
      AND information_schema.COLUMNS.TABLE_NAME = iTable
      AND information_schema.COLUMNS.COLUMN_NAME = CONCAT(vPrefix, 'RemovedBy');

  IF vi = 0 THEN
    SET oQueryStr = CONCAT(oQueryStr, 'ALTER TABLE ', iTable, '
  ADD COLUMN ', vPrefix, 'RemovedBy BIGINT UNSIGNED NULL DEFAULT NULL AFTER ', vPrefix, 'RemovedAt
;

');
  END IF;

/*
    IF (iFK = 1) THEN
      SET oQueryStr = CONCAT(oQueryStr, '
        ADD CONSTRAINT FKA_', iTable,'_tbl_AAA_User_creator FOREIGN KEY (', vPrefix, 'CreatedBy) REFERENCES tbl_AAA_User (usrID) ON UPDATE CASCADE ON DELETE RESTRICT,
        ADD CONSTRAINT FKA_', iTable,'_tbl_AAA_User_modifier FOREIGN KEY (', vPrefix, 'UpdatedBy) REFERENCES tbl_AAA_User (usrID) ON UPDATE CASCADE ON DELETE RESTRICT
        ');
    ELSE
      SET oQueryStr = CONCAT(oQueryStr, '
        ADD INDEX ', vPrefix, 'CreatedBy (', vPrefix, 'CreatedBy) ,
        ADD INDEX ', vPrefix, 'UpdatedBy (', vPrefix, 'UpdatedBy)
        ');
    END IF;

    SET @SQL := oQueryStr;
    PREPARE stmt FROM @SQL;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  ELSEIF oQueryStr < 3 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Some essential columns not found';
  END IF;
*/
  SET TriggerName = CONCAT('trg_updatelog_', iTable);
  SET oQueryStr = CONCAT(oQueryStr, 'DROP TRIGGER IF EXISTS ', TriggerName, ';
DELIMITER ;;
CREATE TRIGGER ', TriggerName, ' AFTER UPDATE ON ', iTable, ' FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

');

  SET SESSION group_concat_max_len = 1000000;

  SELECT GROUP_CONCAT(CONCAT('  IF ISNULL(OLD.', COLUMN_NAME, ') != ISNULL(NEW.', COLUMN_NAME, ')',
          ' OR OLD.', COLUMN_NAME, ' != NEW.', COLUMN_NAME,
          ' THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("', COLUMN_NAME, '", IF(ISNULL(OLD.', COLUMN_NAME, '), NULL, OLD.', COLUMN_NAME, ')));',
          ' END IF;')
          SEPARATOR '
') INTO TempQueryStr
    FROM information_schema.COLUMNS
    WHERE information_schema.COLUMNS.TABLE_SCHEMA = iSchema
      AND information_schema.COLUMNS.TABLE_NAME = iTable
      AND information_schema.COLUMNS.COLUMN_KEY != 'PRI'
      AND information_schema.COLUMNS.COLUMN_NAME NOT IN (
          CONCAT(vPrefix, 'CreatedAt'),
          CONCAT(vPrefix, 'CreatedBy'),
          CONCAT(vPrefix, 'UpdatedAt'),
          CONCAT(vPrefix, 'UpdatedBy'),
          CONCAT(vPrefix, 'RemovedAt'),
          CONCAT(vPrefix, 'RemovedBy')
          )
ORDER BY ORDINAL_POSITION ASC;

  SET oQueryStr = CONCAT(oQueryStr, TempQueryStr, '
');

  SELECT GROUP_CONCAT(CONCAT('"', COLUMN_NAME, '", OLD.', COLUMN_NAME) SEPARATOR ', ')
    INTO TempQueryStr /**/
    FROM information_schema.COLUMNS
    WHERE information_schema.COLUMNS.TABLE_SCHEMA = iSchema
      AND information_schema.COLUMNS.TABLE_NAME = iTable
      AND information_schema.COLUMNS.COLUMN_KEY = 'PRI'
    ORDER BY ORDINAL_POSITION ASC;

  SET oQueryStr = CONCAT(oQueryStr, '
  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.', CONCAT(vPrefix, 'UpdatedBy'), ') THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
        SET atlBy     = NEW.', CONCAT(vPrefix, 'UpdatedBy'), '
          , atlAction = "UPDATE"
          , atlTarget = "', iTable, '"
          , atlInfo   = JSON_OBJECT(', TempQueryStr, ', "old", Changes);
  END IF;
END;;
DELIMITER ;');

--  SELECT oQueryStr;
  /*
  SET @SQL := oQueryStr;
  PREPARE stmt FROM @SQL;
  EXECUTE stmt;
  DEALLOCATE PREPARE stmt;
  */
END
SQL
		);

	}

	public function safeDown()
	{
		echo "m240816_114253_aaa_convert_all_datetimes_to_timestamp cannot be reverted.\n";
		return false;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{
	}

	public function down()
	{
		echo "m240816_114253_aaa_convert_all_datetimes_to_timestamp cannot be reverted.\n";
		return false;
	}
	*/

}
