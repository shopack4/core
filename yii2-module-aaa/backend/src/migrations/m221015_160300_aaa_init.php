<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use yii\db\Expression;
use shopack\base\common\db\Migration;
use shopack\aaa\common\enums\enuUserStatus;
use shopack\aaa\common\enums\enuGender;

class m221015_160300_aaa_init extends Migration
{
  public function safeUp()
	{
    $this->execute(<<<SQL
CREATE PROCEDURE `DropIndexIfExists`(
	IN i_table_name VARCHAR(128),
	IN i_index_name VARCHAR(128)
)
BEGIN
	SET @tableName = i_table_name;
	SET @indexName = i_index_name;
	SET @indexExists = 0;

	SELECT	IFNULL(tmp._cnt,0)
		INTO	@indexExists
		FROM	(
	SELECT	TABLE_NAME
			 ,  INDEX_NAME
			 ,  COUNT(*) AS _cnt
		FROM	INFORMATION_SCHEMA.STATISTICS
	 WHERE	TABLE_NAME = @tableName
		 AND	INDEX_NAME = @indexName
		 AND	TABLE_SCHEMA = DATABASE()
GROUP BY	TABLE_NAME,INDEX_NAME
					) tmp
	 WHERE	IFNULL(tmp._cnt,0) > 0
	;

	SET @query = CONCAT('DROP INDEX `', @indexName, '` ON `', @tableName, '`');
	IF (@indexExists > 0) THEN
		PREPARE stmt FROM @query;
		EXECUTE stmt;
		DEALLOCATE PREPARE stmt;
	END IF;
END ;
SQL
		);

    $this->execute(<<<SQL
CREATE TABLE IF NOT EXISTS `tbl_AAA_GeoCountry` (
  `cntrID` smallint unsigned NOT NULL AUTO_INCREMENT,
  `cntrUUID` VARCHAR(38) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cntrName` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cntrCreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `cntrCreatedBy` bigint unsigned DEFAULT NULL,
  `cntrUpdatedAt` datetime DEFAULT NULL,
  `cntrUpdatedBy` bigint unsigned DEFAULT NULL,
  `cntrRemovedAt` int unsigned NOT NULL DEFAULT '0',
  `cntrRemovedBy` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`cntrID`),
  KEY `cntrCreatedAt` (`cntrCreatedAt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
    );

    $this->execute(<<<SQL
CREATE TABLE IF NOT EXISTS `tbl_AAA_GeoState` (
  `sttID` mediumint unsigned NOT NULL AUTO_INCREMENT,
  `sttUUID` VARCHAR(38) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sttName` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sttCountryID` smallint unsigned NOT NULL,
  `sttCreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `sttCreatedBy` bigint unsigned DEFAULT NULL,
  `sttUpdatedAt` datetime DEFAULT NULL,
  `sttUpdatedBy` bigint unsigned DEFAULT NULL,
  `sttRemovedAt` int unsigned NOT NULL DEFAULT '0',
  `sttRemovedBy` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`sttID`) USING BTREE,
  KEY `FK_tblGeoState_tblGeoCountry` (`sttCountryID`) USING BTREE,
  KEY `sttCreatedAt` (`sttCreatedAt`),
  CONSTRAINT `FK_tblGeoState_tblGeoCountry` FOREIGN KEY (`sttCountryID`) REFERENCES `tbl_AAA_GeoCountry` (`cntrID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
		);

    $this->execute(<<<SQL
CREATE TABLE IF NOT EXISTS `tbl_AAA_GeoCityOrVillage` (
  `ctvID` mediumint unsigned NOT NULL AUTO_INCREMENT,
  `ctvUUID` VARCHAR(38) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ctvName` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ctvStateID` mediumint unsigned NOT NULL,
  `ctvType` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'C' COMMENT 'C:City, V:Village',
  `ctvCreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `ctvCreatedBy` bigint unsigned DEFAULT NULL,
  `ctvUpdatedAt` datetime DEFAULT NULL,
  `ctvUpdatedBy` bigint unsigned DEFAULT NULL,
  `ctvRemovedAt` int unsigned NOT NULL DEFAULT '0',
  `ctvRemovedBy` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`ctvID`) USING BTREE,
  KEY `FK_tblGeoCityOrVillage_tblGeoState` (`ctvStateID`) USING BTREE,
  KEY `ctvCreatedAt` (`ctvCreatedAt`),
  CONSTRAINT `FK_tblGeoCityOrVillage_tblGeoState` FOREIGN KEY (`ctvStateID`) REFERENCES `tbl_AAA_GeoState` (`sttID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
		);

    $this->execute(<<<SQL
CREATE TABLE IF NOT EXISTS `tbl_AAA_GeoTown` (
  `twnID` mediumint unsigned NOT NULL AUTO_INCREMENT,
  `twnUUID` VARCHAR(38) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `twnName` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `twnCityID` mediumint unsigned NOT NULL,
  `twnCreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `twnCreatedBy` bigint unsigned DEFAULT NULL,
  `twnUpdatedAt` datetime DEFAULT NULL,
  `twnUpdatedBy` bigint unsigned DEFAULT NULL,
  `twnRemovedAt` int unsigned NOT NULL DEFAULT '0',
  `twnRemovedBy` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`twnID`) USING BTREE,
  KEY `FK_tblGeoTown_tblGeoCityOrVillage` (`twnCityID`) USING BTREE,
  KEY `twnCreatedAt` (`twnCreatedAt`),
  CONSTRAINT `FK_tblGeoTown_tblGeoCityOrVillage` FOREIGN KEY (`twnCityID`) REFERENCES `tbl_AAA_GeoCityOrVillage` (`ctvID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
		);

    $this->execute(<<<SQL
CREATE TABLE IF NOT EXISTS `tbl_AAA_User` (
  `usrID` bigint unsigned NOT NULL AUTO_INCREMENT,
  `usrGender` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'M:Male, F:Female, N:Not Set',
  `usrFirstName` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrFirstName_en` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrLastName` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrLastName_en` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrEmail` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrEmailApprovedAt` datetime DEFAULT NULL,
  `usrMobile` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrMobileApprovedAt` datetime DEFAULT NULL,
  `usrSSID` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrRoleID` int unsigned DEFAULT NULL,
  `usrPrivs` JSON DEFAULT NULL,
  `usrPasswordHash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usrPasswordCreatedAt` datetime DEFAULT NULL,
  `usrStatus` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'A' COMMENT 'A:Active, D:Disable, R:Removed',
  `usrCreatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usrCreatedBy` bigint unsigned DEFAULT NULL,
  `usrUpdatedAt` datetime DEFAULT NULL,
  `usrUpdatedBy` bigint unsigned DEFAULT NULL,
  `usrRemovedAt` int unsigned NOT NULL DEFAULT '0',
  `usrRemovedBy` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`usrID`) USING BTREE,
  UNIQUE KEY `usrEmail_usrRemovedAt` (`usrEmail`,`usrRemovedAt`),
  UNIQUE KEY `usrMobile_usrRemovedAt` (`usrMobile`,`usrRemovedAt`),
  UNIQUE KEY `usrSSID_usrRemovedAt` (`usrSSID`,`usrRemovedAt`),
  KEY `FK_tbl_AAA_User_tbl_AAA_User_remover` (`usrRemovedBy`),
  KEY `FK_tbl_AAA_User_tbl_AAA_User_creator` (`usrCreatedBy`) USING BTREE,
  KEY `FK_tbl_AAA_User_tbl_AAA_User_modifier` (`usrUpdatedBy`) USING BTREE,
  CONSTRAINT `FK_tbl_AAA_User_tbl_AAA_User_creator` FOREIGN KEY (`usrCreatedBy`) REFERENCES `tbl_AAA_User` (`usrID`),
  CONSTRAINT `FK_tbl_AAA_User_tbl_AAA_User_modifier` FOREIGN KEY (`usrUpdatedBy`) REFERENCES `tbl_AAA_User` (`usrID`),
  CONSTRAINT `FK_tbl_AAA_User_tbl_AAA_User_remover` FOREIGN KEY (`usrRemovedBy`) REFERENCES `tbl_AAA_User` (`usrID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
		);
    $this->alterColumn('{{%AAA_User}}', 'usrPrivs', $this->json());

    $this->execute(<<<SQL
CREATE TABLE IF NOT EXISTS `tbl_AAA_UserExtraInfo` (
  `uexUserID` bigint unsigned NOT NULL,
  `uexBirthDate` date DEFAULT NULL,
  `uexCountryID` smallint unsigned DEFAULT NULL,
  `uexStateID` mediumint unsigned DEFAULT NULL,
  `uexCityOrVillageID` mediumint unsigned DEFAULT NULL,
  `uexTownID` mediumint unsigned DEFAULT NULL,
  `uexHomeAddress` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uexZipCode` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uexImage` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uexCreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `uexCreatedBy` bigint unsigned DEFAULT NULL,
  `uexUpdatedAt` datetime DEFAULT NULL,
  `uexUpdatedBy` bigint unsigned DEFAULT NULL,
  `uexRemovedAt` int unsigned NOT NULL DEFAULT '0',
  `uexRemovedBy` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`uexUserID`) USING BTREE,
  KEY `FK_tbl_AAA_UserExtraInfo_tbl_AAA_GeoCountry` (`uexCountryID`) USING BTREE,
  KEY `FK_tbl_AAA_UserExtraInfo_tbl_AAA_GeoState` (`uexStateID`) USING BTREE,
  KEY `FK_tbl_AAA_UserExtraInfo_tbl_AAA_GeoCityOrVillage` (`uexCityOrVillageID`) USING BTREE,
  KEY `FK_tbl_AAA_UserExtraInfo_tbl_AAA_GeoTown` (`uexTownID`) USING BTREE,
  KEY `uexCreatedAt` (`uexCreatedAt`),
  CONSTRAINT `FK_tbl_AAA_UserExtraInfo_tbl_AAA_GeoCityOrVillage` FOREIGN KEY (`uexCityOrVillageID`) REFERENCES `tbl_AAA_GeoCityOrVillage` (`ctvID`),
  CONSTRAINT `FK_tbl_AAA_UserExtraInfo_tbl_AAA_GeoCountry` FOREIGN KEY (`uexCountryID`) REFERENCES `tbl_AAA_GeoCountry` (`cntrID`),
  CONSTRAINT `FK_tbl_AAA_UserExtraInfo_tbl_AAA_GeoState` FOREIGN KEY (`uexStateID`) REFERENCES `tbl_AAA_GeoState` (`sttID`),
  CONSTRAINT `FK_tbl_AAA_UserExtraInfo_tbl_AAA_GeoTown` FOREIGN KEY (`uexTownID`) REFERENCES `tbl_AAA_GeoTown` (`twnID`),
  CONSTRAINT `FK_tbl_AAA_UserExtraInfo_tbl_AAA_User` FOREIGN KEY (`uexUserID`) REFERENCES `tbl_AAA_User` (`usrID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
		);

    $this->execute(<<<SQL
CREATE TABLE IF NOT EXISTS `tbl_AAA_Role` (
  `rolID` int unsigned NOT NULL AUTO_INCREMENT,
  `rolName` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rolParentID` int unsigned DEFAULT NULL,
  `rolPrivs` JSON NOT NULL,
  `rolCreatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rolCreatedBy` bigint unsigned DEFAULT NULL,
  `rolUpdatedAt` datetime DEFAULT NULL,
  `rolUpdatedBy` bigint unsigned DEFAULT NULL,
  `rolRemovedAt` int unsigned NOT NULL DEFAULT '0',
  `rolRemovedBy` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`rolID`),
  KEY `FK_tbl_AAA_Role_tbl_AAA_User_creator` (`rolCreatedBy`),
  KEY `FK_tbl_AAA_Role_tbl_AAA_User_modifier` (`rolUpdatedBy`),
  CONSTRAINT `FK_tbl_AAA_Role_tbl_AAA_User_creator` FOREIGN KEY (`rolCreatedBy`) REFERENCES `tbl_AAA_User` (`usrID`),
  CONSTRAINT `FK_tbl_AAA_Role_tbl_AAA_User_modifier` FOREIGN KEY (`rolUpdatedBy`) REFERENCES `tbl_AAA_User` (`usrID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
		);
    $this->alterColumn('{{%AAA_Role}}', 'rolPrivs', $this->json());

    $this->execute(<<<SQL
ALTER TABLE `tbl_AAA_User`
  ADD CONSTRAINT `FK_tbl_AAA_User_tbl_AAA_Role` FOREIGN KEY (`usrRoleID`) REFERENCES `tbl_AAA_Role` (`rolID`) ON UPDATE NO ACTION ON DELETE NO ACTION;
SQL
    );

    $this->execute(<<<SQL
CREATE TABLE IF NOT EXISTS `tbl_AAA_ApprovalRequest` (
  `aprID` bigint unsigned NOT NULL AUTO_INCREMENT,
  `aprUserID` bigint unsigned DEFAULT NULL,
  `aprKeyType` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'E:Email, M:Mobile',
  `aprKey` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `aprCode` varchar(48) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `aprLastRequestAt` datetime NOT NULL,
  `aprExpireAt` datetime NOT NULL,
  `aprSentAt` datetime DEFAULT NULL,
  `aprApplyAt` datetime DEFAULT NULL,
  `aprStatus` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'N:New, S:Sent, A:Applied, E:Expired',
  `aprCreatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `aprCreatedBy` bigint unsigned DEFAULT NULL,
  `aprUpdatedAt` datetime DEFAULT NULL,
  `aprUpdatedBy` bigint unsigned DEFAULT NULL,
  `aprRemovedAt` int unsigned NOT NULL DEFAULT '0',
  `aprRemovedBy` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`aprID`),
  KEY `FK_tbl_AAA_ApprovalRequest_tbl_AAA_User` (`aprUserID`),
  CONSTRAINT `FK_tbl_AAA_ApprovalRequest_tbl_AAA_User` FOREIGN KEY (`aprUserID`) REFERENCES `tbl_AAA_User` (`usrID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
		);

    $this->execute(<<<SQL
CREATE TABLE IF NOT EXISTS `tbl_AAA_ForgotPasswordRequest` (
  `fprID` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fprUserID` bigint unsigned NOT NULL,
  `fprRequestedBy` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'E:Email, M:Mobile',
  `fprCode` varchar(48) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fprLastRequestAt` datetime NOT NULL,
  `fprExpireAt` datetime NOT NULL,
  `fprSentAt` datetime DEFAULT NULL,
  `fprApplyAt` datetime DEFAULT NULL,
  `fprStatus` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'N:New, S:Sent, A:Applied, E:Expired',
  `fprCreatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fprCreatedBy` bigint unsigned DEFAULT NULL,
  `fprUpdatedAt` datetime DEFAULT NULL,
  `fprUpdatedBy` bigint unsigned DEFAULT NULL,
  `fprRemovedAt` int unsigned NOT NULL DEFAULT '0',
  `fprRemovedBy` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`fprID`),
  KEY `FK_tbl_AAA_ForgotPasswordRequest_tbl_AAA_User` (`fprUserID`),
  CONSTRAINT `FK_tbl_AAA_ForgotPasswordRequest_tbl_AAA_User` FOREIGN KEY (`fprUserID`) REFERENCES `tbl_AAA_User` (`usrID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
		);

    $this->execute(<<<SQL
CREATE TABLE IF NOT EXISTS `tbl_AAA_AlertType` (
  `altID` int unsigned NOT NULL AUTO_INCREMENT,
  `altKey` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `altType` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'E:Email, M:Mobile',
  `altBody` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `altCreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `altCreatedBy` bigint unsigned DEFAULT NULL,
  `altUpdatedAt` datetime DEFAULT NULL,
  `altUpdatedBy` bigint unsigned DEFAULT NULL,
  `altRemovedAt` int unsigned NOT NULL DEFAULT '0',
  `altRemovedBy` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`altID`),
  UNIQUE KEY `altKey` (`altKey`),
  KEY `altCreatedAt` (`altCreatedAt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
		);

    $this->execute(<<<SQL
CREATE TABLE IF NOT EXISTS `tbl_AAA_Alert` (
  `alrID` bigint unsigned NOT NULL AUTO_INCREMENT,
  `alrUserID` bigint unsigned DEFAULT NULL,
  `alrApprovalRequestID` bigint unsigned DEFAULT NULL,
  `alrForgotPasswordRequestID` bigint unsigned DEFAULT NULL,
  `alrTypeKey` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alrTarget` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alrInfo` JSON NOT NULL,
  `alrLockedAt` datetime DEFAULT NULL,
  `alrLockedBy` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alrLastTryAt` datetime DEFAULT NULL,
  `alrSentAt` datetime DEFAULT NULL,
  `alrResult` JSON DEFAULT NULL,
  `alrStatus` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'N:New, P:Processing, S:Sent, E:Error, R:Removed',
  `alrCreatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `alrCreatedBy` bigint unsigned DEFAULT NULL,
  `alrUpdatedAt` datetime DEFAULT NULL,
  `alrUpdatedBy` bigint unsigned DEFAULT NULL,
  `alrRemovedAt` int unsigned NOT NULL DEFAULT '0',
  `alrRemovedBy` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`alrID`),
  KEY `FK_tbl_AAA_Alert_tbl_AAA_User` (`alrUserID`),
  KEY `FK_tbl_AAA_Alert_tbl_AAA_User_creator` (`alrCreatedBy`),
  KEY `FK_tbl_AAA_Alert_tbl_AAA_ApprovalRequest` (`alrApprovalRequestID`),
  KEY `FK_tbl_AAA_Alert_tbl_AAA_ForgotPasswordRequest` (`alrForgotPasswordRequestID`),
  CONSTRAINT `FK_tbl_AAA_Alert_tbl_AAA_ApprovalRequest` FOREIGN KEY (`alrApprovalRequestID`) REFERENCES `tbl_AAA_ApprovalRequest` (`aprID`) ON DELETE CASCADE,
  CONSTRAINT `FK_tbl_AAA_Alert_tbl_AAA_ForgotPasswordRequest` FOREIGN KEY (`alrForgotPasswordRequestID`) REFERENCES `tbl_AAA_ForgotPasswordRequest` (`fprID`) ON DELETE CASCADE,
  CONSTRAINT `FK_tbl_AAA_Alert_tbl_AAA_User` FOREIGN KEY (`alrUserID`) REFERENCES `tbl_AAA_User` (`usrID`) ON DELETE CASCADE,
  CONSTRAINT `FK_tbl_AAA_Alert_tbl_AAA_User_creator` FOREIGN KEY (`alrCreatedBy`) REFERENCES `tbl_AAA_User` (`usrID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
		);
    $this->alterColumn('{{%AAA_Alert}}', 'alrInfo', $this->json());
    $this->alterColumn('{{%AAA_Alert}}', 'alrResult', $this->json());

    $this->execute(<<<SQL
CREATE TABLE IF NOT EXISTS `tbl_AAA_Gateway` (
  `gtwID` int unsigned NOT NULL AUTO_INCREMENT,
  `gtwName` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gtwKey` varchar(48) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gtwPluginType` varchar(48) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gtwPluginName` varchar(48) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gtwPluginParameters` JSON NOT NULL,
  `gtwStatus` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'A' COMMENT 'A:Active, D:Disable, R:Removed',
  `gtwCreatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `gtwCreatedBy` bigint unsigned DEFAULT NULL,
  `gtwUpdatedAt` datetime DEFAULT NULL,
  `gtwUpdatedBy` bigint unsigned DEFAULT NULL,
  `gtwRemovedAt` int unsigned NOT NULL DEFAULT '0',
  `gtwRemovedBy` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`gtwID`) USING BTREE,
  UNIQUE KEY `gtwKey_gtwRemovedAt` (`gtwKey`,`gtwRemovedAt`) USING BTREE,
  KEY `FK_tbl_AAA_Gateway_tbl_AAA_User_creator` (`gtwCreatedBy`) USING BTREE,
  KEY `FK_tbl_AAA_Gateway_tbl_AAA_User_modifier` (`gtwUpdatedBy`) USING BTREE,
  KEY `FK_tbl_AAA_Gateway_tbl_AAA_User_remover` (`gtwRemovedBy`) USING BTREE,
  CONSTRAINT `FK_tbl_AAA_Gateway_tbl_AAA_User_creator` FOREIGN KEY (`gtwCreatedBy`) REFERENCES `tbl_AAA_User` (`usrID`),
  CONSTRAINT `FK_tbl_AAA_Gateway_tbl_AAA_User_modifier` FOREIGN KEY (`gtwUpdatedBy`) REFERENCES `tbl_AAA_User` (`usrID`),
  CONSTRAINT `FK_tbl_AAA_Gateway_tbl_AAA_User_remover` FOREIGN KEY (`gtwRemovedBy`) REFERENCES `tbl_AAA_User` (`usrID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
		);
    $this->alterColumn('{{%AAA_Gateway}}', 'gtwPluginParameters', $this->json());

    $this->execute(<<<SQL
CREATE TABLE IF NOT EXISTS `tbl_AAA_Session` (
  `ssnID` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ssnUserID` bigint unsigned NOT NULL,
  `ssnJWT` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ssnJWTMD5` varchar(32) COLLATE utf8mb4_unicode_ci GENERATED ALWAYS AS (md5(`ssnJWT`)) VIRTUAL,
  `ssnStatus` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'P' COMMENT 'P:Pending, A:Active, R:Removed',
  `ssnExpireAt` datetime DEFAULT NULL,
  `ssnCreatedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ssnCreatedBy` bigint unsigned DEFAULT NULL,
  `ssnUpdatedAt` datetime DEFAULT NULL,
  `ssnUpdatedBy` bigint unsigned DEFAULT NULL,
  `ssnRemovedAt` int unsigned NOT NULL DEFAULT '0',
  `ssnRemovedBy` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`ssnID`),
  UNIQUE KEY `ssnMd5JWT` (`ssnJWTMD5`) USING BTREE,
  KEY `FK_tblSession_tblUser_modifier` (`ssnUpdatedBy`),
  KEY `FK_tblSession_tblUser` (`ssnUserID`) USING BTREE,
  CONSTRAINT `FK_tblSession_tblUser` FOREIGN KEY (`ssnUserID`) REFERENCES `tbl_AAA_User` (`usrID`) ON DELETE CASCADE,
  CONSTRAINT `FK_tblSession_tblUser_modifier` FOREIGN KEY (`ssnUpdatedBy`) REFERENCES `tbl_AAA_User` (`usrID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
		);

    $this->execute(<<<SQL
CREATE TABLE IF NOT EXISTS `tbl_SYS_ActionLogs` (
  `atlID` bigint unsigned NOT NULL AUTO_INCREMENT,
  `atlAction` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `atlTarget` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `atlInfo` JSON DEFAULT NULL,
  `atlAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atlBy` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`atlID`),
  KEY `atlAt` (`atlAt`),
  KEY `FK_tbl_SYS_ActionLogs_tbl_AAA_User` (`atlBy`),
  KEY `atlType` (`atlAction`) USING BTREE,
  CONSTRAINT `FK_tbl_SYS_ActionLogs_tbl_AAA_User` FOREIGN KEY (`atlBy`) REFERENCES `tbl_AAA_User` (`usrID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
		);
    $this->alterColumn('{{%SYS_ActionLogs}}', 'atlInfo', $this->json());

		$this->execute(<<<SQL
CREATE PROCEDURE `spAutoUpdateTableTriggerAndCols`()
BEGIN
  DECLARE vTableName VARCHAR(200);
  DECLARE vFinished INTEGER DEFAULT 0;
  DECLARE vQueryStr LONGTEXT DEFAULT '';
  DECLARE vTempQueryStr TEXT;

  DECLARE curTables CURSOR FOR
    SELECT TABLE_NAME
      FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME != 'tbl_SYS_ActionLogs'
  ORDER BY TABLE_NAME
  ;

  DECLARE CONTINUE HANDLER FOR NOT FOUND SET vFinished = 1;

  OPEN curTables;

  RunScope: LOOP
    FETCH curTables
     INTO vTableName
    ;

    IF vFinished = 1 THEN
      LEAVE RunScope;
    END IF;

		SET vTempQueryStr = '';
    CALL spUpdateTableTriggerAndCols(DATABASE(), vTableName, TRUE, vTempQueryStr);

    SET vQueryStr = CONCAT(vQueryStr, '\n\n', vTempQueryStr);

  END LOOP RunScope;

  CLOSE curTables;

	SELECT vQueryStr;

END ;
SQL
		);

		$this->execute(<<<SQL
CREATE PROCEDURE `spFindTablePrefix`(
	IN `iTable` VARCHAR(128),
	OUT `oPrefix` VARCHAR(64)
)
Proc: BEGIN
  DECLARE vColumnCount INTEGER;
  DECLARE vFirstColumnName VARCHAR(200);
  DECLARE vLastColumnName VARCHAR(200);
  DECLARE vMinColumnLen INTEGER;
  DECLARE vi INTEGER;

  SET oPrefix = NULL;

  SELECT COUNT(*)
    INTO vColumnCount
    FROM information_schema.COLUMNS
   WHERE information_schema.COLUMNS.TABLE_SCHEMA = DATABASE()
     AND information_schema.COLUMNS.TABLE_NAME = iTable
  ;

  IF vColumnCount = 0 THEN
    LEAVE Proc;
  END IF;

  SELECT COLUMN_NAME
    INTO vFirstColumnName
    FROM information_schema.COLUMNS
   WHERE information_schema.COLUMNS.TABLE_SCHEMA = DATABASE()
     AND information_schema.COLUMNS.TABLE_NAME = iTable
ORDER BY COLUMN_NAME
   LIMIT 1,1
  ;

  IF vColumnCount = 1 THEN
    SET oPrefix = vFirstColumnName;
    LEAVE Proc;
  END IF;

  SELECT COLUMN_NAME
    INTO vLastColumnName
    FROM information_schema.COLUMNS
   WHERE information_schema.COLUMNS.TABLE_SCHEMA = DATABASE()
     AND information_schema.COLUMNS.TABLE_NAME = iTable
ORDER BY COLUMN_NAME DESC
   LIMIT 1,1
  ;

  SET vMinColumnLen = LEAST(LENGTH(vFirstColumnName), LENGTH(vLastColumnName));

  SET vi = 0;
  WHILE vi < vMinColumnLen AND SUBSTRING(vFirstColumnName, vi+1, 1) = SUBSTRING(vLastColumnName, vi+1, 1) DO
    SET vi = vi + 1;
  END WHILE;

  IF vi > 0 THEN
    SET oPrefix = SUBSTRING(vFirstColumnName, 1, vi);
  END IF;

END ;
SQL
		);

		$this->execute(<<<SQL
CREATE PROCEDURE `spUpdateTableTriggerAndCols`(
	IN `iSchema` VARCHAR(50),
	IN `iTable` VARCHAR(50),
	IN `iFK` BOOLEAN,
	OUT `oQueryStr` TEXT
)
Proc: BEGIN
  -- DECLARE QueryStr VARCHAR(21000) DEFAULT '';
  DECLARE TempQueryStr VARCHAR(5000) DEFAULT 0;
  DECLARE TriggerName VARCHAR(100);
	DECLARE vPrefix VARCHAR(50);
  DECLARE vi INTEGER DEFAULT 0;

	SET oQueryStr = '';

	CALL spFindTablePrefix(iTable, vPrefix);
	IF vPrefix IS NULL OR vPrefix = '' THEN
		LEAVE Proc;
	END IF;

  -- CreatedAt
  SELECT COUNT(1)
    INTO vi
    FROM information_schema.COLUMNS
   WHERE information_schema.COLUMNS.TABLE_SCHEMA = iSchema
     AND information_schema.COLUMNS.TABLE_NAME = iTable
     AND information_schema.COLUMNS.COLUMN_NAME = CONCAT(vPrefix, 'CreatedAt');

  IF vi = 0 THEN
    SET oQueryStr = CONCAT(oQueryStr, 'ALTER TABLE ', iTable, '
  ADD COLUMN ', vPrefix, 'CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
  ADD INDEX  ', vPrefix, 'CreatedAt (', vPrefix, 'CreatedAt)
;

');
  END IF;

  -- CreatedBy
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

  -- UpdatedAt
  SELECT COUNT(1)
    INTO vi
    FROM information_schema.COLUMNS
   WHERE information_schema.COLUMNS.TABLE_SCHEMA = iSchema
     AND information_schema.COLUMNS.TABLE_NAME = iTable
     AND information_schema.COLUMNS.COLUMN_NAME = CONCAT(vPrefix, 'UpdatedAt');

  IF vi = 0 THEN
    SET oQueryStr = CONCAT(oQueryStr, 'ALTER TABLE ', iTable, '
  ADD COLUMN ', vPrefix, 'UpdatedAt DATETIME NULL DEFAULT NULL AFTER ', vPrefix, 'CreatedBy
;

');
  END IF;

  -- UpdatedBy
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

  -- RemovedAt
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

  -- RemovedBy
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

--    IF (iFK = 1) THEN
--      SET oQueryStr = CONCAT(oQueryStr, '
--        ADD CONSTRAINT FKA_', iTable,'_tbl_AAA_User_creator FOREIGN KEY (', vPrefix, 'CreatedBy) REFERENCES tbl_AAA_User (usrID) ON UPDATE CASCADE ON DELETE RESTRICT,
--        ADD CONSTRAINT FKA_', iTable,'_tbl_AAA_User_modifier FOREIGN KEY (', vPrefix, 'UpdatedBy) REFERENCES tbl_AAA_User (usrID) ON UPDATE CASCADE ON DELETE RESTRICT
--        ');
--    ELSE
--      SET oQueryStr = CONCAT(oQueryStr, '
--        ADD INDEX ', vPrefix, 'CreatedBy (', vPrefix, 'CreatedBy) ,
--        ADD INDEX ', vPrefix, 'UpdatedBy (', vPrefix, 'UpdatedBy)
--        ');
--    END IF;
--
--    SET @SQL := oQueryStr;
--    PREPARE stmt FROM @SQL;
--    EXECUTE stmt;
--    DEALLOCATE PREPARE stmt;
--  ELSEIF oQueryStr < 3 THEN
--    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Some essential columns not found';
--  END IF;

  SET TriggerName = CONCAT('trg_updatelog_', iTable);
  SET oQueryStr = CONCAT(oQueryStr, 'DROP TRIGGER IF EXISTS ', TriggerName, ';
DELIMITER ;;
CREATE TRIGGER ', TriggerName, ' AFTER UPDATE ON ', iTable, ' FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();\n\n');

  SET SESSION group_concat_max_len = 1000000;

  SELECT GROUP_CONCAT(CONCAT('  IF ISNULL(OLD.', COLUMN_NAME, ') != ISNULL(NEW.', COLUMN_NAME, ')',
          ' OR OLD.', COLUMN_NAME, ' != NEW.', COLUMN_NAME,
					' THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("', COLUMN_NAME, '", IF(ISNULL(OLD.', COLUMN_NAME, '), NULL, OLD.', COLUMN_NAME, ')));',
					' END IF;')
          SEPARATOR '\n') INTO TempQueryStr
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

  SET oQueryStr = CONCAT(oQueryStr, TempQueryStr, '\n');

  SELECT GROUP_CONCAT(CONCAT('"', COLUMN_NAME, '", OLD.', COLUMN_NAME) SEPARATOR ', ')
    INTO TempQueryStr
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

--  SET @SQL := oQueryStr;
--  PREPARE stmt FROM @SQL;
--  EXECUTE stmt;
--  DEALLOCATE PREPARE stmt;

END ;
SQL
		);

		$this->execute(<<<SQL
CREATE TRIGGER `trg_updatelog_tbl_AAA_Alert` AFTER UPDATE ON `tbl_AAA_Alert` FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.alrUserID) != ISNULL(NEW.alrUserID) OR OLD.alrUserID != NEW.alrUserID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("alrUserID", IF(ISNULL(OLD.alrUserID), NULL, OLD.alrUserID))); END IF;
  IF ISNULL(OLD.alrApprovalRequestID) != ISNULL(NEW.alrApprovalRequestID) OR OLD.alrApprovalRequestID != NEW.alrApprovalRequestID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("alrApprovalRequestID", IF(ISNULL(OLD.alrApprovalRequestID), NULL, OLD.alrApprovalRequestID))); END IF;
  IF ISNULL(OLD.alrForgotPasswordRequestID) != ISNULL(NEW.alrForgotPasswordRequestID) OR OLD.alrForgotPasswordRequestID != NEW.alrForgotPasswordRequestID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("alrForgotPasswordRequestID", IF(ISNULL(OLD.alrForgotPasswordRequestID), NULL, OLD.alrForgotPasswordRequestID))); END IF;
  IF ISNULL(OLD.alrTypeKey) != ISNULL(NEW.alrTypeKey) OR OLD.alrTypeKey != NEW.alrTypeKey THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("alrTypeKey", IF(ISNULL(OLD.alrTypeKey), NULL, OLD.alrTypeKey))); END IF;
  IF ISNULL(OLD.alrTarget) != ISNULL(NEW.alrTarget) OR OLD.alrTarget != NEW.alrTarget THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("alrTarget", IF(ISNULL(OLD.alrTarget), NULL, OLD.alrTarget))); END IF;
  IF ISNULL(OLD.alrInfo) != ISNULL(NEW.alrInfo) OR OLD.alrInfo != NEW.alrInfo THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("alrInfo", IF(ISNULL(OLD.alrInfo), NULL, OLD.alrInfo))); END IF;
  IF ISNULL(OLD.alrLockedAt) != ISNULL(NEW.alrLockedAt) OR OLD.alrLockedAt != NEW.alrLockedAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("alrLockedAt", IF(ISNULL(OLD.alrLockedAt), NULL, OLD.alrLockedAt))); END IF;
  IF ISNULL(OLD.alrLockedBy) != ISNULL(NEW.alrLockedBy) OR OLD.alrLockedBy != NEW.alrLockedBy THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("alrLockedBy", IF(ISNULL(OLD.alrLockedBy), NULL, OLD.alrLockedBy))); END IF;
  IF ISNULL(OLD.alrLastTryAt) != ISNULL(NEW.alrLastTryAt) OR OLD.alrLastTryAt != NEW.alrLastTryAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("alrLastTryAt", IF(ISNULL(OLD.alrLastTryAt), NULL, OLD.alrLastTryAt))); END IF;
  IF ISNULL(OLD.alrSentAt) != ISNULL(NEW.alrSentAt) OR OLD.alrSentAt != NEW.alrSentAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("alrSentAt", IF(ISNULL(OLD.alrSentAt), NULL, OLD.alrSentAt))); END IF;
  IF ISNULL(OLD.alrResult) != ISNULL(NEW.alrResult) OR OLD.alrResult != NEW.alrResult THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("alrResult", IF(ISNULL(OLD.alrResult), NULL, OLD.alrResult))); END IF;
  IF ISNULL(OLD.alrStatus) != ISNULL(NEW.alrStatus) OR OLD.alrStatus != NEW.alrStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("alrStatus", IF(ISNULL(OLD.alrStatus), NULL, OLD.alrStatus))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.alrUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
       SET atlBy     = NEW.alrUpdatedBy
         , atlAction = "UPDATE"
         , atlTarget = "tbl_AAA_Alert"
         , atlInfo   = JSON_OBJECT("alrID", OLD.alrID, "old", Changes);
  END IF;
END ;
SQL
		);

		$this->execute(<<<SQL
CREATE TRIGGER `trg_updatelog_tbl_AAA_AlertType` AFTER UPDATE ON `tbl_AAA_AlertType` FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.altKey) != ISNULL(NEW.altKey) OR OLD.altKey != NEW.altKey THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("altKey", IF(ISNULL(OLD.altKey), NULL, OLD.altKey))); END IF;
  IF ISNULL(OLD.altType) != ISNULL(NEW.altType) OR OLD.altType != NEW.altType THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("altType", IF(ISNULL(OLD.altType), NULL, OLD.altType))); END IF;
  IF ISNULL(OLD.altBody) != ISNULL(NEW.altBody) OR OLD.altBody != NEW.altBody THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("altBody", IF(ISNULL(OLD.altBody), NULL, OLD.altBody))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.altUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
       SET atlBy     = NEW.altUpdatedBy
         , atlAction = "UPDATE"
         , atlTarget = "tbl_AAA_AlertType"
         , atlInfo   = JSON_OBJECT("altID", OLD.altID, "old", Changes);
  END IF;
END ;
SQL
		);

		$this->execute(<<<SQL
CREATE TRIGGER `trg_updatelog_tbl_AAA_ApprovalRequest` AFTER UPDATE ON `tbl_AAA_ApprovalRequest` FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.aprUserID) != ISNULL(NEW.aprUserID) OR OLD.aprUserID != NEW.aprUserID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("aprUserID", IF(ISNULL(OLD.aprUserID), NULL, OLD.aprUserID))); END IF;
  IF ISNULL(OLD.aprKeyType) != ISNULL(NEW.aprKeyType) OR OLD.aprKeyType != NEW.aprKeyType THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("aprKeyType", IF(ISNULL(OLD.aprKeyType), NULL, OLD.aprKeyType))); END IF;
  IF ISNULL(OLD.aprKey) != ISNULL(NEW.aprKey) OR OLD.aprKey != NEW.aprKey THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("aprKey", IF(ISNULL(OLD.aprKey), NULL, OLD.aprKey))); END IF;
  IF ISNULL(OLD.aprCode) != ISNULL(NEW.aprCode) OR OLD.aprCode != NEW.aprCode THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("aprCode", IF(ISNULL(OLD.aprCode), NULL, OLD.aprCode))); END IF;
  IF ISNULL(OLD.aprLastRequestAt) != ISNULL(NEW.aprLastRequestAt) OR OLD.aprLastRequestAt != NEW.aprLastRequestAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("aprLastRequestAt", IF(ISNULL(OLD.aprLastRequestAt), NULL, OLD.aprLastRequestAt))); END IF;
  IF ISNULL(OLD.aprExpireAt) != ISNULL(NEW.aprExpireAt) OR OLD.aprExpireAt != NEW.aprExpireAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("aprExpireAt", IF(ISNULL(OLD.aprExpireAt), NULL, OLD.aprExpireAt))); END IF;
  IF ISNULL(OLD.aprSentAt) != ISNULL(NEW.aprSentAt) OR OLD.aprSentAt != NEW.aprSentAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("aprSentAt", IF(ISNULL(OLD.aprSentAt), NULL, OLD.aprSentAt))); END IF;
  IF ISNULL(OLD.aprApplyAt) != ISNULL(NEW.aprApplyAt) OR OLD.aprApplyAt != NEW.aprApplyAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("aprApplyAt", IF(ISNULL(OLD.aprApplyAt), NULL, OLD.aprApplyAt))); END IF;
  IF ISNULL(OLD.aprStatus) != ISNULL(NEW.aprStatus) OR OLD.aprStatus != NEW.aprStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("aprStatus", IF(ISNULL(OLD.aprStatus), NULL, OLD.aprStatus))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.aprUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
       SET atlBy     = NEW.aprUpdatedBy
         , atlAction = "UPDATE"
         , atlTarget = "tbl_AAA_ApprovalRequest"
         , atlInfo   = JSON_OBJECT("aprID", OLD.aprID, "old", Changes);
  END IF;
END ;
SQL
		);

		$this->execute(<<<SQL
CREATE TRIGGER `trg_updatelog_tbl_AAA_ForgotPasswordRequest` AFTER UPDATE ON `tbl_AAA_ForgotPasswordRequest` FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.fprUserID) != ISNULL(NEW.fprUserID) OR OLD.fprUserID != NEW.fprUserID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("fprUserID", IF(ISNULL(OLD.fprUserID), NULL, OLD.fprUserID))); END IF;
  IF ISNULL(OLD.fprRequestedBy) != ISNULL(NEW.fprRequestedBy) OR OLD.fprRequestedBy != NEW.fprRequestedBy THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("fprRequestedBy", IF(ISNULL(OLD.fprRequestedBy), NULL, OLD.fprRequestedBy))); END IF;
  IF ISNULL(OLD.fprCode) != ISNULL(NEW.fprCode) OR OLD.fprCode != NEW.fprCode THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("fprCode", IF(ISNULL(OLD.fprCode), NULL, OLD.fprCode))); END IF;
  IF ISNULL(OLD.fprLastRequestAt) != ISNULL(NEW.fprLastRequestAt) OR OLD.fprLastRequestAt != NEW.fprLastRequestAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("fprLastRequestAt", IF(ISNULL(OLD.fprLastRequestAt), NULL, OLD.fprLastRequestAt))); END IF;
  IF ISNULL(OLD.fprExpireAt) != ISNULL(NEW.fprExpireAt) OR OLD.fprExpireAt != NEW.fprExpireAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("fprExpireAt", IF(ISNULL(OLD.fprExpireAt), NULL, OLD.fprExpireAt))); END IF;
  IF ISNULL(OLD.fprSentAt) != ISNULL(NEW.fprSentAt) OR OLD.fprSentAt != NEW.fprSentAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("fprSentAt", IF(ISNULL(OLD.fprSentAt), NULL, OLD.fprSentAt))); END IF;
  IF ISNULL(OLD.fprApplyAt) != ISNULL(NEW.fprApplyAt) OR OLD.fprApplyAt != NEW.fprApplyAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("fprApplyAt", IF(ISNULL(OLD.fprApplyAt), NULL, OLD.fprApplyAt))); END IF;
  IF ISNULL(OLD.fprStatus) != ISNULL(NEW.fprStatus) OR OLD.fprStatus != NEW.fprStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("fprStatus", IF(ISNULL(OLD.fprStatus), NULL, OLD.fprStatus))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.fprUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
       SET atlBy     = NEW.fprUpdatedBy
         , atlAction = "UPDATE"
         , atlTarget = "tbl_AAA_ForgotPasswordRequest"
         , atlInfo   = JSON_OBJECT("fprID", OLD.fprID, "old", Changes);
  END IF;
END ;
SQL
		);

		$this->execute(<<<SQL
CREATE TRIGGER `trg_updatelog_tbl_AAA_Gateway` AFTER UPDATE ON `tbl_AAA_Gateway` FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.gtwName) != ISNULL(NEW.gtwName) OR OLD.gtwName != NEW.gtwName THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("gtwName", IF(ISNULL(OLD.gtwName), NULL, OLD.gtwName))); END IF;
  IF ISNULL(OLD.gtwKey) != ISNULL(NEW.gtwKey) OR OLD.gtwKey != NEW.gtwKey THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("gtwKey", IF(ISNULL(OLD.gtwKey), NULL, OLD.gtwKey))); END IF;
  IF ISNULL(OLD.gtwPluginType) != ISNULL(NEW.gtwPluginType) OR OLD.gtwPluginType != NEW.gtwPluginType THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("gtwPluginType", IF(ISNULL(OLD.gtwPluginType), NULL, OLD.gtwPluginType))); END IF;
  IF ISNULL(OLD.gtwPluginName) != ISNULL(NEW.gtwPluginName) OR OLD.gtwPluginName != NEW.gtwPluginName THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("gtwPluginName", IF(ISNULL(OLD.gtwPluginName), NULL, OLD.gtwPluginName))); END IF;
  IF ISNULL(OLD.gtwPluginParameters) != ISNULL(NEW.gtwPluginParameters) OR OLD.gtwPluginParameters != NEW.gtwPluginParameters THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("gtwPluginParameters", IF(ISNULL(OLD.gtwPluginParameters), NULL, OLD.gtwPluginParameters))); END IF;
  IF ISNULL(OLD.gtwStatus) != ISNULL(NEW.gtwStatus) OR OLD.gtwStatus != NEW.gtwStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("gtwStatus", IF(ISNULL(OLD.gtwStatus), NULL, OLD.gtwStatus))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.gtwUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
       SET atlBy     = NEW.gtwUpdatedBy
         , atlAction = "UPDATE"
         , atlTarget = "tbl_AAA_Gateway"
         , atlInfo   = JSON_OBJECT("gtwID", OLD.gtwID, "old", Changes);
  END IF;
END ;
SQL
		);

		$this->execute(<<<SQL
CREATE TRIGGER `trg_updatelog_tbl_AAA_GeoCityOrVillage` AFTER UPDATE ON `tbl_AAA_GeoCityOrVillage` FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.ctvName) != ISNULL(NEW.ctvName) OR OLD.ctvName != NEW.ctvName THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ctvName", IF(ISNULL(OLD.ctvName), NULL, OLD.ctvName))); END IF;
  IF ISNULL(OLD.ctvStateID) != ISNULL(NEW.ctvStateID) OR OLD.ctvStateID != NEW.ctvStateID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ctvStateID", IF(ISNULL(OLD.ctvStateID), NULL, OLD.ctvStateID))); END IF;
  IF ISNULL(OLD.ctvType) != ISNULL(NEW.ctvType) OR OLD.ctvType != NEW.ctvType THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ctvType", IF(ISNULL(OLD.ctvType), NULL, OLD.ctvType))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.ctvUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
       SET atlBy     = NEW.ctvUpdatedBy
         , atlAction = "UPDATE"
         , atlTarget = "tbl_AAA_GeoCityOrVillage"
         , atlInfo   = JSON_OBJECT("ctvID", OLD.ctvID, "old", Changes);
  END IF;
END ;
SQL
		);

		$this->execute(<<<SQL
CREATE TRIGGER `trg_updatelog_tbl_AAA_GeoCountry` AFTER UPDATE ON `tbl_AAA_GeoCountry` FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.cntrName) != ISNULL(NEW.cntrName) OR OLD.cntrName != NEW.cntrName THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("cntrName", IF(ISNULL(OLD.cntrName), NULL, OLD.cntrName))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.cntrUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
       SET atlBy     = NEW.cntrUpdatedBy
         , atlAction = "UPDATE"
         , atlTarget = "tbl_AAA_GeoCountry"
         , atlInfo   = JSON_OBJECT("cntrID", OLD.cntrID, "old", Changes);
  END IF;
END ;
SQL
		);

		$this->execute(<<<SQL
CREATE TRIGGER `trg_updatelog_tbl_AAA_GeoState` AFTER UPDATE ON `tbl_AAA_GeoState` FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.sttName) != ISNULL(NEW.sttName) OR OLD.sttName != NEW.sttName THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("sttName", IF(ISNULL(OLD.sttName), NULL, OLD.sttName))); END IF;
  IF ISNULL(OLD.sttCountryID) != ISNULL(NEW.sttCountryID) OR OLD.sttCountryID != NEW.sttCountryID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("sttCountryID", IF(ISNULL(OLD.sttCountryID), NULL, OLD.sttCountryID))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.sttUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
       SET atlBy     = NEW.sttUpdatedBy
         , atlAction = "UPDATE"
         , atlTarget = "tbl_AAA_GeoState"
         , atlInfo   = JSON_OBJECT("sttID", OLD.sttID, "old", Changes);
  END IF;
END ;
SQL
    );

    $this->execute(<<<SQL
CREATE TRIGGER `trg_updatelog_tbl_AAA_GeoTown` AFTER UPDATE ON `tbl_AAA_GeoTown` FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.twnName) != ISNULL(NEW.twnName) OR OLD.twnName != NEW.twnName THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("twnName", IF(ISNULL(OLD.twnName), NULL, OLD.twnName))); END IF;
  IF ISNULL(OLD.twnCityID) != ISNULL(NEW.twnCityID) OR OLD.twnCityID != NEW.twnCityID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("twnCityID", IF(ISNULL(OLD.twnCityID), NULL, OLD.twnCityID))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.twnUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
       SET atlBy     = NEW.twnUpdatedBy
         , atlAction = "UPDATE"
         , atlTarget = "tbl_AAA_GeoTown"
         , atlInfo   = JSON_OBJECT("twnID", OLD.twnID, "old", Changes);
  END IF;
END ;
SQL
    );

    $this->execute(<<<SQL
CREATE TRIGGER `trg_updatelog_tbl_AAA_Role` AFTER UPDATE ON `tbl_AAA_Role` FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.rolName) != ISNULL(NEW.rolName) OR OLD.rolName != NEW.rolName THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("rolName", IF(ISNULL(OLD.rolName), NULL, OLD.rolName))); END IF;
  IF ISNULL(OLD.rolParentID) != ISNULL(NEW.rolParentID) OR OLD.rolParentID != NEW.rolParentID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("rolParentID", IF(ISNULL(OLD.rolParentID), NULL, OLD.rolParentID))); END IF;
  IF ISNULL(OLD.rolPrivs) != ISNULL(NEW.rolPrivs) OR OLD.rolPrivs != NEW.rolPrivs THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("rolPrivs", IF(ISNULL(OLD.rolPrivs), NULL, OLD.rolPrivs))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.rolUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
       SET atlBy     = NEW.rolUpdatedBy
         , atlAction = "UPDATE"
         , atlTarget = "tbl_AAA_Role"
         , atlInfo   = JSON_OBJECT("rolID", OLD.rolID, "old", Changes);
  END IF;
END ;
SQL
    );

    $this->execute(<<<SQL
CREATE TRIGGER `trg_updatelog_tbl_AAA_Session` AFTER UPDATE ON `tbl_AAA_Session` FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.ssnUserID) != ISNULL(NEW.ssnUserID) OR OLD.ssnUserID != NEW.ssnUserID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ssnUserID", IF(ISNULL(OLD.ssnUserID), NULL, OLD.ssnUserID))); END IF;
  IF ISNULL(OLD.ssnJWT) != ISNULL(NEW.ssnJWT) OR OLD.ssnJWT != NEW.ssnJWT THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ssnJWT", IF(ISNULL(OLD.ssnJWT), NULL, OLD.ssnJWT))); END IF;
  IF ISNULL(OLD.ssnJWTMD5) != ISNULL(NEW.ssnJWTMD5) OR OLD.ssnJWTMD5 != NEW.ssnJWTMD5 THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ssnJWTMD5", IF(ISNULL(OLD.ssnJWTMD5), NULL, OLD.ssnJWTMD5))); END IF;
  IF ISNULL(OLD.ssnStatus) != ISNULL(NEW.ssnStatus) OR OLD.ssnStatus != NEW.ssnStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ssnStatus", IF(ISNULL(OLD.ssnStatus), NULL, OLD.ssnStatus))); END IF;
  IF ISNULL(OLD.ssnExpireAt) != ISNULL(NEW.ssnExpireAt) OR OLD.ssnExpireAt != NEW.ssnExpireAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("ssnExpireAt", IF(ISNULL(OLD.ssnExpireAt), NULL, OLD.ssnExpireAt))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.ssnUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
       SET atlBy     = NEW.ssnUpdatedBy
         , atlAction = "UPDATE"
         , atlTarget = "tbl_AAA_Session"
         , atlInfo   = JSON_OBJECT("ssnID", OLD.ssnID, "old", Changes);
  END IF;
END ;
SQL
    );

    $this->execute(<<<SQL
CREATE TRIGGER `trg_updatelog_tbl_AAA_User` AFTER UPDATE ON `tbl_AAA_User` FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.usrGender) != ISNULL(NEW.usrGender) OR OLD.usrGender != NEW.usrGender THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrGender", IF(ISNULL(OLD.usrGender), NULL, OLD.usrGender))); END IF;
  IF ISNULL(OLD.usrFirstName) != ISNULL(NEW.usrFirstName) OR OLD.usrFirstName != NEW.usrFirstName THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrFirstName", IF(ISNULL(OLD.usrFirstName), NULL, OLD.usrFirstName))); END IF;
  IF ISNULL(OLD.usrFirstName_en) != ISNULL(NEW.usrFirstName_en) OR OLD.usrFirstName_en != NEW.usrFirstName_en THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrFirstName_en", IF(ISNULL(OLD.usrFirstName_en), NULL, OLD.usrFirstName_en))); END IF;
  IF ISNULL(OLD.usrLastName) != ISNULL(NEW.usrLastName) OR OLD.usrLastName != NEW.usrLastName THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrLastName", IF(ISNULL(OLD.usrLastName), NULL, OLD.usrLastName))); END IF;
  IF ISNULL(OLD.usrLastName_en) != ISNULL(NEW.usrLastName_en) OR OLD.usrLastName_en != NEW.usrLastName_en THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrLastName_en", IF(ISNULL(OLD.usrLastName_en), NULL, OLD.usrLastName_en))); END IF;
  IF ISNULL(OLD.usrEmail) != ISNULL(NEW.usrEmail) OR OLD.usrEmail != NEW.usrEmail THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrEmail", IF(ISNULL(OLD.usrEmail), NULL, OLD.usrEmail))); END IF;
  IF ISNULL(OLD.usrEmailApprovedAt) != ISNULL(NEW.usrEmailApprovedAt) OR OLD.usrEmailApprovedAt != NEW.usrEmailApprovedAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrEmailApprovedAt", IF(ISNULL(OLD.usrEmailApprovedAt), NULL, OLD.usrEmailApprovedAt))); END IF;
  IF ISNULL(OLD.usrMobile) != ISNULL(NEW.usrMobile) OR OLD.usrMobile != NEW.usrMobile THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrMobile", IF(ISNULL(OLD.usrMobile), NULL, OLD.usrMobile))); END IF;
  IF ISNULL(OLD.usrMobileApprovedAt) != ISNULL(NEW.usrMobileApprovedAt) OR OLD.usrMobileApprovedAt != NEW.usrMobileApprovedAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrMobileApprovedAt", IF(ISNULL(OLD.usrMobileApprovedAt), NULL, OLD.usrMobileApprovedAt))); END IF;
  IF ISNULL(OLD.usrSSID) != ISNULL(NEW.usrSSID) OR OLD.usrSSID != NEW.usrSSID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrSSID", IF(ISNULL(OLD.usrSSID), NULL, OLD.usrSSID))); END IF;
  IF ISNULL(OLD.usrRoleID) != ISNULL(NEW.usrRoleID) OR OLD.usrRoleID != NEW.usrRoleID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrRoleID", IF(ISNULL(OLD.usrRoleID), NULL, OLD.usrRoleID))); END IF;
  IF ISNULL(OLD.usrPrivs) != ISNULL(NEW.usrPrivs) OR OLD.usrPrivs != NEW.usrPrivs THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrPrivs", IF(ISNULL(OLD.usrPrivs), NULL, OLD.usrPrivs))); END IF;
  IF ISNULL(OLD.usrPasswordHash) != ISNULL(NEW.usrPasswordHash) OR OLD.usrPasswordHash != NEW.usrPasswordHash THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrPasswordHash", IF(ISNULL(OLD.usrPasswordHash), NULL, OLD.usrPasswordHash))); END IF;
  IF ISNULL(OLD.usrPasswordCreatedAt) != ISNULL(NEW.usrPasswordCreatedAt) OR OLD.usrPasswordCreatedAt != NEW.usrPasswordCreatedAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrPasswordCreatedAt", IF(ISNULL(OLD.usrPasswordCreatedAt), NULL, OLD.usrPasswordCreatedAt))); END IF;
  IF ISNULL(OLD.usrStatus) != ISNULL(NEW.usrStatus) OR OLD.usrStatus != NEW.usrStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrStatus", IF(ISNULL(OLD.usrStatus), NULL, OLD.usrStatus))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.usrUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
       SET atlBy     = NEW.usrUpdatedBy
         , atlAction = "UPDATE"
         , atlTarget = "tbl_AAA_User"
         , atlInfo   = JSON_OBJECT("usrID", OLD.usrID, "old", Changes);
  END IF;
END ;
SQL
    );

    $this->execute(<<<SQL
CREATE TRIGGER `trg_updatelog_tbl_AAA_UserExtraInfo` AFTER UPDATE ON `tbl_AAA_UserExtraInfo` FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.uexBirthDate) != ISNULL(NEW.uexBirthDate) OR OLD.uexBirthDate != NEW.uexBirthDate THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uexBirthDate", IF(ISNULL(OLD.uexBirthDate), NULL, OLD.uexBirthDate))); END IF;
  IF ISNULL(OLD.uexCountryID) != ISNULL(NEW.uexCountryID) OR OLD.uexCountryID != NEW.uexCountryID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uexCountryID", IF(ISNULL(OLD.uexCountryID), NULL, OLD.uexCountryID))); END IF;
  IF ISNULL(OLD.uexStateID) != ISNULL(NEW.uexStateID) OR OLD.uexStateID != NEW.uexStateID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uexStateID", IF(ISNULL(OLD.uexStateID), NULL, OLD.uexStateID))); END IF;
  IF ISNULL(OLD.uexCityOrVillageID) != ISNULL(NEW.uexCityOrVillageID) OR OLD.uexCityOrVillageID != NEW.uexCityOrVillageID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uexCityOrVillageID", IF(ISNULL(OLD.uexCityOrVillageID), NULL, OLD.uexCityOrVillageID))); END IF;
  IF ISNULL(OLD.uexTownID) != ISNULL(NEW.uexTownID) OR OLD.uexTownID != NEW.uexTownID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uexTownID", IF(ISNULL(OLD.uexTownID), NULL, OLD.uexTownID))); END IF;
  IF ISNULL(OLD.uexHomeAddress) != ISNULL(NEW.uexHomeAddress) OR OLD.uexHomeAddress != NEW.uexHomeAddress THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uexHomeAddress", IF(ISNULL(OLD.uexHomeAddress), NULL, OLD.uexHomeAddress))); END IF;
  IF ISNULL(OLD.uexZipCode) != ISNULL(NEW.uexZipCode) OR OLD.uexZipCode != NEW.uexZipCode THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uexZipCode", IF(ISNULL(OLD.uexZipCode), NULL, OLD.uexZipCode))); END IF;
  IF ISNULL(OLD.uexImage) != ISNULL(NEW.uexImage) OR OLD.uexImage != NEW.uexImage THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("uexImage", IF(ISNULL(OLD.uexImage), NULL, OLD.uexImage))); END IF;

  IF JSON_LENGTH(Changes) > 0 THEN
--    IF ISNULL(NEW.uexUpdatedBy) THEN
--      SIGNAL SQLSTATE "45401"
--         SET MESSAGE_TEXT = "UpdatedBy is not set";
--    END IF;

    INSERT INTO tbl_SYS_ActionLogs
       SET atlBy     = NEW.uexUpdatedBy
         , atlAction = "UPDATE"
         , atlTarget = "tbl_AAA_UserExtraInfo"
         , atlInfo   = JSON_OBJECT("uexUserID", OLD.uexUserID, "old", Changes);
  END IF;
END ;
SQL
    );

    $this->batchInsertIgnore('{{%AAA_Role}}', ['rolID', 'rolName', 'rolParentID', 'rolPrivs'], [
      [ 1, 'Full Access', NULL, [
        "*" => 1,
      ]],
      [10, 'User',        NULL, [
        "aaa" => [
          "auth" => [
            "signup" => 1,
            "login" => 1,
            "logout" => 1,
          ],
        ],
      ]],
    ]);

    $this->execute(<<<SQL
ALTER TABLE {{%AAA_Role}} AUTO_INCREMENT=101;
SQL
		);

    $this->batchInsertIgnore('{{%AAA_GeoCountry}}', [
      'cntrID',
      'cntrName',
    ], [
      [
        /* cntrID   */ 1,
        /* cntrName */ 'ایران',
      ],
		]);

    $this->execute(<<<SQL
INSERT IGNORE INTO `tbl_AAA_GeoState` (`sttID`, `sttUUID`, `sttName`, `sttCountryID`) VALUES
	(1172, '2499289', 'اردبیل', 1),
	(1173, '7261AF8', 'اصفهان', 1),
	(1174, '0A53518', 'البرز', 1),
	(1175, 'DA816B1', 'ایلام', 1),
	(1176, '13D341E', 'آذربایجان شرقی', 1),
	(1177, '5712C8F', 'آذربایجان غربی', 1),
	(1178, 'CB4DE3B', 'بوشهر', 1),
	(1179, '39FAB2A', 'تهران', 1),
	(1214, '8086643', 'چهارمحال وبختیاری', 1),
	(1215, 'B7E0892', 'خراسان جنوبی', 1),
	(1216, '03CD7BA', 'خراسان رضوی', 1),
	(1217, 'E01080E', 'خراسان شمالی', 1),
	(1218, '2A4C755', 'خوزستان', 1),
	(1219, '8B921F7', 'زنجان', 1),
	(1220, '189A0D3', 'سمنان', 1),
	(1221, '6B6DFC8', 'سیستان وبلوچستان', 1),
	(1222, '2BC286E', 'فارس', 1),
	(1223, 'B7CE4AE', 'قزوین', 1),
	(1224, '7C771BA', 'قم', 1),
	(1225, '5812509', 'کردستان', 1),
	(1226, '50B2EF1', 'کرمان', 1),
	(1227, 'A1237EA', 'کرمانشاه', 1),
	(1228, 'C75B18F', 'کهگیلویه وبویراحمد', 1),
	(1229, '7CB3377', 'گلستان', 1),
	(1230, '1797F46', 'گیلان', 1),
	(1231, 'D630782', 'لرستان', 1),
	(1232, 'CE62E8D', 'مازندران', 1),
	(1233, '4ECA884', 'مرکزی', 1),
	(1234, 'E325F26', 'هرمزگان', 1),
	(1235, '457F9F5', 'همدان', 1),
	(1236, '158480A', 'یزد', 1)
;
SQL
		);

    $this->execute(<<<SQL
INSERT IGNORE INTO `tbl_AAA_GeoCityOrVillage` (`ctvID`, `ctvUUID`, `ctvName`, `ctvStateID`, `ctvType`) VALUES
	(161, 'CE1D1B9', 'ونک', 1173, 'C'),
	(162, '208B3E8', 'هرند', 1173, 'C'),
	(163, 'C000D75', 'اشتهارد', 1174, 'C'),
	(164, 'A4BCEC6', 'آسارا', 1174, 'C'),
	(165, '2FB3394', 'تنکمان', 1174, 'C'),
	(166, '9ED9482', 'چهارباغ', 1174, 'C'),
	(167, '885E974', 'سیف آباد', 1174, 'C'),
	(168, '0518B72', 'شهرجدیدهشتگرد', 1174, 'C'),
	(169, 'D9B8D4A', 'طالقان', 1174, 'C'),
	(170, '88C26E7', 'کرج', 1174, 'C'),
	(171, 'EB70598', 'کمال شهر', 1174, 'C'),
	(172, '037DFB3', 'کوهسار', 1174, 'C'),
	(173, '1938662', 'گرمدره', 1174, 'C'),
	(174, 'DCEC557', 'ماهدشت', 1174, 'C'),
	(175, 'BBAA01C', 'محمدشهر', 1174, 'C'),
	(176, 'D8D578A', 'مشکین دشت', 1174, 'C'),
	(177, '6EBF326', 'نظرآباد', 1174, 'C'),
	(178, 'E4AFECE', 'هشتگرد', 1174, 'C'),
	(179, 'A53B722', 'ارکواز', 1175, 'C'),
	(180, '21F0F50', 'ایلام', 1175, 'C'),
	(181, 'B3F2CC5', 'ایوان', 1175, 'C'),
	(182, 'CE45E87', 'آبدانان', 1175, 'C'),
	(183, 'A94BE83', 'آسمان آباد', 1175, 'C'),
	(184, '33F03F1', 'بدره', 1175, 'C'),
	(185, 'DA0B148', 'پهله', 1175, 'C'),
	(186, '04D6E3A', 'توحید', 1175, 'C'),
	(187, '4F440EF', 'چوار', 1175, 'C'),
	(188, 'B787D59', 'دره شهر', 1175, 'C'),
	(189, '7807891', 'دلگشا', 1175, 'C'),
	(190, 'B42523C', 'دهلران', 1175, 'C'),
	(191, 'CA9F536', 'زرنه', 1175, 'C'),
	(192, '2F7BE9A', 'سراب باغ', 1175, 'C'),
	(193, '5B09AB7', 'سرابله', 1175, 'C'),
	(194, '23287ED', 'صالح آباد', 1175, 'C'),
	(195, '7CBA563', 'لومار', 1175, 'C'),
	(196, 'E694D61', 'مورموری', 1175, 'C'),
	(197, 'C9D3CEE', 'موسیان', 1175, 'C'),
	(198, 'BDEF275', 'مهران', 1175, 'C'),
	(199, '33E4148', 'میمه', 1175, 'C'),
	(200, 'E1A73E4', 'اسکو', 1176, 'C'),
	(201, 'CDE9520', 'اهر', 1176, 'C'),
	(202, 'D1C1101', 'ایلخچی', 1176, 'C'),
	(203, '0DAEC10', 'آبش احمد', 1176, 'C'),
	(204, '833B6E2', 'آذرشهر', 1176, 'C'),
	(205, '2DC1028', 'آقکند', 1176, 'C'),
	(206, 'BF95185', 'باسمنج', 1176, 'C'),
	(207, '18FD51E', 'بخشایش', 1176, 'C'),
	(208, 'E31C883', 'بستان آباد', 1176, 'C'),
	(209, '6844E7C', 'بناب', 1176, 'C'),
	(210, '83EA52C', 'بناب جدید', 1176, 'C'),
	(211, '7479DB6', 'تبریز', 1176, 'C'),
	(212, '8B0E42C', 'ترک', 1176, 'C'),
	(213, '55E4EE5', 'ترکمانچای', 1176, 'C'),
	(214, '259753A', 'تسوج', 1176, 'C'),
	(215, 'DB6D42C', 'تیکمه داش', 1176, 'C'),
	(216, 'D26A043', 'جلفا', 1176, 'C'),
	(217, 'B149E68', 'خاروانا', 1176, 'C'),
	(218, '80179EA', 'خامنه', 1176, 'C'),
	(219, '154CDDD', 'خراجو', 1176, 'C'),
	(220, 'EB1A09E', 'خسروشهر', 1176, 'C'),
	(221, 'F8C635D', 'خمارلو', 1176, 'C'),
	(222, '636E440', 'خواجه', 1176, 'C'),
	(223, '9EDC847', 'دوزدوزان', 1176, 'C'),
	(224, 'DFECA61', 'زرنق', 1176, 'C'),
	(225, '47A23E8', 'زنوز', 1176, 'C'),
	(226, '22EE23D', 'سراب', 1176, 'C'),
	(227, 'F98360B', 'سردرود', 1176, 'C'),
	(228, '2E09506', 'سیس', 1176, 'C'),
	(229, '087444C', 'سیه رود', 1176, 'C'),
	(230, 'CE1C9EE', 'شبستر', 1176, 'C'),
	(231, '194DCD5', 'شربیان', 1176, 'C'),
	(232, '0E16A09', 'شرفخانه', 1176, 'C'),
	(233, 'E809BF8', 'شندآباد', 1176, 'C'),
	(234, '4E432F8', 'شهرجدیدسهند', 1176, 'C'),
	(235, '0BF2418', 'صوفیان', 1176, 'C'),
	(236, 'B980E60', 'عجب شیر', 1176, 'C'),
	(237, 'C6285C4', 'قره آغاج', 1176, 'C'),
	(238, '3E5A6A0', 'کشکسرای', 1176, 'C'),
	(239, '864F9CE', 'کلوانق', 1176, 'C'),
	(240, 'F36C1C3', 'کلیبر', 1176, 'C'),
	(241, 'BDFD5FB', 'کوزه کنان', 1176, 'C'),
	(242, 'F8DBDA4', 'گوگان', 1176, 'C'),
	(243, '0F5A957', 'لیلان', 1176, 'C'),
	(244, 'EAEE6D7', 'مراغه', 1176, 'C'),
	(245, '8382B72', 'مرند', 1176, 'C'),
	(246, 'C3BE1C8', 'ملکان', 1176, 'C'),
	(247, '2D85B9D', 'ممقان', 1176, 'C'),
	(248, '6DD869B', 'مهربان', 1176, 'C'),
	(249, 'BD15630', 'میانه', 1176, 'C'),
	(250, '56A246E', 'نظرکهریزی', 1176, 'C'),
	(251, 'E30F3C6', 'وایقان', 1176, 'C'),
	(252, 'A6EAF85', 'ورزقان', 1176, 'C'),
	(253, 'BD07D31', 'هادیشهر', 1176, 'C'),
	(254, '6587A52', 'هریس', 1176, 'C'),
	(255, '7669BBF', 'هشترود', 1176, 'C'),
	(256, 'E9E67B1', 'هوراند', 1176, 'C'),
	(257, '1524575', 'یامچی', 1176, 'C'),
	(258, 'B9880A4', 'ارومیه', 1177, 'C'),
	(259, 'D8514C8', 'اشنویه', 1177, 'C'),
	(260, '6FED2EE', 'ایواوغلی', 1177, 'C'),
	(261, '94FCF54', 'آواجیق', 1177, 'C'),
	(262, '9A2D708', 'باروق', 1177, 'C'),
	(263, '03D7531', 'بازرگان', 1177, 'C'),
	(264, '4405719', 'بوکان', 1177, 'C'),
	(265, '3AB1677', 'پلدشت', 1177, 'C'),
	(266, '602B8E3', 'پیرانشهر', 1177, 'C'),
	(267, '4510854', 'تازه شهر', 1177, 'C'),
	(268, 'AF55139', 'تکاب', 1177, 'C'),
	(269, '2592E90', 'چهاربرج', 1177, 'C'),
	(270, '4655318', 'خلیفان', 1177, 'C'),
	(271, 'A6D1F65', 'خوی', 1177, 'C'),
	(272, '66FE28B', 'دیزج دیز', 1177, 'C'),
	(273, '452E4AB', 'ربط', 1177, 'C'),
	(274, '0170E9D', 'سردشت', 1177, 'C'),
	(275, 'EC70970', 'سرو', 1177, 'C'),
	(276, 'B6A19A3', 'سلماس', 1177, 'C'),
	(277, '16DBF69', 'سیلوانه', 1177, 'C'),
	(278, '789FB21', 'سیمینه', 1177, 'C'),
	(279, 'F83F168', 'سیه چشمه', 1177, 'C'),
	(280, 'FF1287E', 'شاهین دژ', 1177, 'C'),
	(281, '275F902', 'شوط', 1177, 'C'),
	(282, '4922F16', 'فیرورق', 1177, 'C'),
	(283, 'E8ADF2E', 'قره ضیاءالدین', 1177, 'C'),
	(284, '484B780', 'قطور', 1177, 'C'),
	(285, 'C108371', 'قوشچی', 1177, 'C'),
	(286, 'A13D6B6', 'کشاورز', 1177, 'C'),
	(287, '617CA4C', 'گردکشانه', 1177, 'C'),
	(288, 'BD5F516', 'ماکو', 1177, 'C'),
	(289, '484883E', 'محمدیار', 1177, 'C'),
	(290, '2EF0B4B', 'محمودآباد', 1177, 'C'),
	(291, '3030775', 'مهاباد', 1177, 'C'),
	(292, '9DD4BA9', 'میاندوآب', 1177, 'C'),
	(293, '9A2DCF1', 'میرآباد', 1177, 'C'),
	(294, '9A15F90', 'نالوس', 1177, 'C'),
	(295, '982839B', 'نقده', 1177, 'C'),
	(296, 'E76AC35', 'نوشین', 1177, 'C'),
	(297, 'F4323D8', 'امام حسن', 1178, 'C'),
	(298, '016E051', 'انارستان', 1178, 'C'),
	(299, '2678BF1', 'اهرم', 1178, 'C'),
	(300, '8F73189', 'آبپخش', 1178, 'C'),
	(301, '4DC9E36', 'آبدان', 1178, 'C'),
	(302, 'F1118E8', 'برازجان', 1178, 'C'),
	(303, 'AA93989', 'بردخون', 1178, 'C'),
	(304, 'A5BA061', 'بردستان', 1178, 'C'),
	(305, 'E5F615B', 'بندردیر', 1178, 'C'),
	(306, '09E42E2', 'بندردیلم', 1178, 'C'),
	(307, '1E23EDC', 'بندرریگ', 1178, 'C'),
	(308, '3814012', 'بندرکنگان', 1178, 'C'),
	(309, 'B56059A', 'بندرگناوه', 1178, 'C'),
	(310, '7E9462F', 'بنک', 1178, 'C'),
	(311, '86A57CE', 'بوشهر', 1178, 'C'),
	(312, '162C67F', 'تنگ ارم', 1178, 'C'),
	(313, 'BF2B3FC', 'جم', 1178, 'C'),
	(314, '355B504', 'چغادک', 1178, 'C'),
	(315, 'E8E0E13', 'خارک', 1178, 'C'),
	(316, '3B861C5', 'خورموج', 1178, 'C'),
	(317, '0C925B1', 'دالکی', 1178, 'C'),
	(318, 'B181237', 'دلوار', 1178, 'C'),
	(319, 'A4033DA', 'ریز', 1178, 'C'),
	(320, 'D3B24B1', 'سعدآباد', 1178, 'C'),
	(321, '7E06DE7', 'سیراف', 1178, 'C'),
	(322, '403B7BB', 'شبانکاره', 1178, 'C'),
	(323, '4C25D1B', 'شنبه', 1178, 'C'),
	(324, 'D169C46', 'عسلویه', 1178, 'C'),
	(325, 'E905956', 'کاکی', 1178, 'C'),
	(326, '76E6AAC', 'کلمه', 1178, 'C'),
	(327, '1AC7AC8', 'نخل تقی', 1178, 'C'),
	(328, 'BE737AD', 'وحدتیه', 1178, 'C'),
	(329, 'BB34F91', 'ارجمند', 1179, 'C'),
	(330, '3DAD75F', 'اسلامشهر', 1179, 'C'),
	(331, '3F1C19F', 'اندیشه', 1179, 'C'),
	(332, 'C0C64C3', 'آبسرد', 1179, 'C'),
	(333, '86C38E1', 'آبعلی', 1179, 'C'),
	(334, 'D02D931', 'باغستان', 1179, 'C'),
	(335, '37E6A01', 'باقرشهر', 1179, 'C'),
	(336, 'F8D3206', 'بومهن', 1179, 'C'),
	(337, 'BB33583', 'پاکدشت', 1179, 'C'),
	(338, '04E2391', 'پردیس', 1179, 'C'),
	(339, '17C1E56', 'پیشوا', 1179, 'C'),
	(340, '410DA97', 'تجریش', 1179, 'C'),
	(341, '49F8E21', 'تهران', 1179, 'C'),
	(342, '538BB73', 'جوادآباد', 1179, 'C'),
	(343, '9803290', 'چهاردانگه', 1179, 'C'),
	(344, '83BBC94', 'حسن آباد', 1179, 'C'),
	(345, '282BC78', 'دماوند', 1179, 'C'),
	(346, '1601AF5', 'رباط کریم', 1179, 'C'),
	(347, '20FD2F7', 'رودهن', 1179, 'C'),
	(348, '4EAF927', 'ری', 1179, 'C'),
	(349, '92A2AB3', 'شاهدشهر', 1179, 'C'),
	(350, '4CCC738', 'شریف آباد', 1179, 'C'),
	(351, 'CCCEC2B', 'شهریار', 1179, 'C'),
	(352, '8F00028', 'صالح آباد', 1179, 'C'),
	(353, '7BCAAF3', 'صباشهر', 1179, 'C'),
	(354, 'DFD803C', 'صفادشت', 1179, 'C'),
	(355, '66819A2', 'فردوسیه', 1179, 'C'),
	(356, '3C8B6FE', 'فرون آباد', 1179, 'C'),
	(357, 'AB12E69', 'فشم', 1179, 'C'),
	(358, 'FDB3300', 'فیروزکوه', 1179, 'C'),
	(359, 'CF88883', 'قدس', 1179, 'C'),
	(360, '6BDDAB2', 'قرچک', 1179, 'C'),
	(361, '6B9A892', 'کهریزک', 1179, 'C'),
	(362, '2FCCFA3', 'کیلان', 1179, 'C'),
	(363, '07B27C6', 'گلستان', 1179, 'C'),
	(364, 'E1B495E', 'لواسان', 1179, 'C'),
	(365, '7667C66', 'ملارد', 1179, 'C'),
	(366, '12B352B', 'نسیم شهر', 1179, 'C'),
	(367, '6BAF07C', 'نصیرآباد', 1179, 'C'),
	(368, '4DB4E77', 'وحیدیه', 1179, 'C'),
	(369, '7812307', 'ورامین', 1179, 'C'),
	(370, '997419C', 'اردل', 1214, 'C'),
	(371, '4F87793', 'آلونی', 1214, 'C'),
	(372, '289957E', 'باباحیدر', 1214, 'C'),
	(373, '0C3FBF8', 'بروجن', 1214, 'C'),
	(374, '9A5AD01', 'بلداجی', 1214, 'C'),
	(375, 'A8B3C4E', 'بن', 1214, 'C'),
	(376, '7AEF1EA', 'جونقان', 1214, 'C'),
	(377, 'B23DC5A', 'چلگرد', 1214, 'C'),
	(378, '48FE267', 'سامان', 1214, 'C'),
	(379, 'A4FE6B2', 'سفیددشت', 1214, 'C'),
	(380, '236F0D5', 'سودجان', 1214, 'C'),
	(381, 'AE74606', 'سورشجان', 1214, 'C'),
	(382, '7EA306A', 'شلمزار', 1214, 'C'),
	(383, '23A3E67', 'شهرکرد', 1214, 'C'),
	(384, 'D176C3C', 'طاقانک', 1214, 'C'),
	(385, 'C517069', 'فارس', 1214, 'C'),
	(386, '98EE406', 'فرادنبه', 1214, 'C'),
	(387, 'B1D2ED3', 'فرخ شهر', 1214, 'C'),
	(388, '947B148', 'کیان', 1214, 'C'),
	(389, 'C10FA76', 'گندمان', 1214, 'C'),
	(390, 'DC5CDFB', 'گهرو', 1214, 'C'),
	(391, '937A660', 'لردگان', 1214, 'C'),
	(392, 'A0A6ABD', 'مال خلیفه', 1214, 'C'),
	(393, '554E47E', 'ناغان', 1214, 'C'),
	(394, '6ED9C21', 'نافچ', 1214, 'C'),
	(395, '50C55BD', 'نقنه', 1214, 'C'),
	(396, 'BC251CE', 'هفشجان', 1214, 'C'),
	(397, '34B5A6A', 'ارسک', 1215, 'C'),
	(398, 'B4EA56C', 'اسدیه', 1215, 'C'),
	(399, '73CC99C', 'اسفدن', 1215, 'C'),
	(400, '09352AE', 'اسلامیه', 1215, 'C'),
	(401, '3534479', 'آرین شهر', 1215, 'C'),
	(402, '894519E', 'آیسک', 1215, 'C'),
	(403, '0024F15', 'بشرویه', 1215, 'C'),
	(404, 'B68A439', 'بیرجند', 1215, 'C'),
	(405, 'B591F42', 'حاجی آباد', 1215, 'C'),
	(406, 'F9980C6', 'خضری دشت بیاض', 1215, 'C'),
	(407, 'E141DC8', 'خوسف', 1215, 'C'),
	(408, '1E93A9E', 'زهان', 1215, 'C'),
	(409, 'D0D697B', 'سرایان', 1215, 'C'),
	(410, '09E2DB6', 'سربیشه', 1215, 'C'),
	(411, '6EB23E6', 'سه قلعه', 1215, 'C'),
	(412, '2653DC3', 'شوسف', 1215, 'C'),
	(413, '2BA0800', 'طبس مسینا', 1215, 'C'),
	(414, '62A1015', 'فردوس', 1215, 'C'),
	(415, '6B86EFD', 'قائن', 1215, 'C'),
	(416, 'E3975B7', 'قهستان', 1215, 'C'),
	(417, '756A5D1', 'گزیک', 1215, 'C'),
	(418, 'A2F780D', 'محمد شهر', 1215, 'C'),
	(419, '9EEFED1', 'مود', 1215, 'C'),
	(420, '97463EC', 'نهبندان', 1215, 'C'),
	(421, '8737D48', 'نیمبلوک', 1215, 'C'),
	(422, '79E6C19', 'احمدآبادصولت', 1216, 'C'),
	(423, '4F9655A', 'انابد', 1216, 'C'),
	(424, '41C67E7', 'باجگیران', 1216, 'C'),
	(425, 'CA2EFE8', 'باخرز', 1216, 'C'),
	(426, 'A980513', 'بار', 1216, 'C'),
	(427, '1F6D053', 'بایگ', 1216, 'C'),
	(428, 'E90FA41', 'بجستان', 1216, 'C'),
	(429, 'F7B7C7B', 'بردسکن', 1216, 'C'),
	(430, 'F8EB708', 'بیدخت', 1216, 'C'),
	(431, '027BDB6', 'تایباد', 1216, 'C'),
	(432, '430706C', 'تربت جام', 1216, 'C'),
	(433, '3C25D37', 'تربت حیدریه', 1216, 'C'),
	(434, 'C325841', 'جغتای', 1216, 'C'),
	(435, 'F8D5FC6', 'جنگل', 1216, 'C'),
	(436, '50D3EED', 'چاپشلو', 1216, 'C'),
	(437, '8D9D8C0', 'چکنه', 1216, 'C'),
	(438, '7E3D484', 'چناران', 1216, 'C'),
	(439, '48DE0C8', 'خرو', 1216, 'C'),
	(440, '86222AA', 'خلیل آباد', 1216, 'C'),
	(441, '01A5BDE', 'خواف', 1216, 'C'),
	(442, 'F75D85A', 'داورزن', 1216, 'C'),
	(443, 'E934B18', 'درگز', 1216, 'C'),
	(444, 'BDDE77C', 'درود', 1216, 'C'),
	(445, '673F742', 'دولت آباد', 1216, 'C'),
	(446, 'E8B4037', 'رباط سنگ', 1216, 'C'),
	(447, 'EAC3921', 'رشتخوار', 1216, 'C'),
	(448, '9CF3D52', 'رضویه', 1216, 'C'),
	(449, 'F31AA6E', 'روداب', 1216, 'C'),
	(450, 'CCB38F2', 'ریوش', 1216, 'C'),
	(451, '00D888B', 'سبزوار', 1216, 'C'),
	(452, 'A146BA8', 'سرخس', 1216, 'C'),
	(453, '105D25E', 'سفیدسنگ', 1216, 'C'),
	(454, '49AA5F8', 'سلامی', 1216, 'C'),
	(455, '79779AE', 'سلطان آباد', 1216, 'C'),
	(456, '41080F9', 'سنگان', 1216, 'C'),
	(457, 'ACADBC7', 'شادمهر', 1216, 'C'),
	(458, 'D88D910', 'شاندیز', 1216, 'C'),
	(459, '5370A07', 'ششتمد', 1216, 'C'),
	(460, 'BC296CA', 'شهرآباد', 1216, 'C'),
	(461, '2FB17AB', 'شهرزو', 1216, 'C'),
	(462, '8E5C382', 'صالح آباد', 1216, 'C'),
	(463, '16B59C5', 'طرقبه', 1216, 'C'),
	(464, '0CEB52D', 'عشق آباد', 1216, 'C'),
	(465, 'DCA8E4C', 'فرهادگرد', 1216, 'C'),
	(466, 'EFF2057', 'فریمان', 1216, 'C'),
	(467, 'C49C272', 'فیروزه', 1216, 'C'),
	(468, 'B7D0A63', 'فیض آباد', 1216, 'C'),
	(469, '1AC52BA', 'قاسم آباد', 1216, 'C'),
	(470, 'D63B5EC', 'قدمگاه', 1216, 'C'),
	(471, '64FE372', 'قلندرآباد', 1216, 'C'),
	(472, '9C3793C', 'قوچان', 1216, 'C'),
	(473, '2FE10EE', 'کاخک', 1216, 'C'),
	(474, '7536381', 'کاریز', 1216, 'C'),
	(475, 'C86AD88', 'کاشمر', 1216, 'C'),
	(476, 'A78785C', 'کدکن', 1216, 'C'),
	(477, 'C6FC76C', 'کلات', 1216, 'C'),
	(478, '368D894', 'کندر', 1216, 'C'),
	(479, '36349A2', 'گلمکان', 1216, 'C'),
	(480, 'A08F834', 'گناباد', 1216, 'C'),
	(481, 'EB332CE', 'لطف آباد', 1216, 'C'),
	(482, 'D27A6E8', 'مزدآوند', 1216, 'C'),
	(483, 'C05302B', 'مشهد', 1216, 'C'),
	(484, '3DE6E3B', 'مشهدریزه', 1216, 'C'),
	(485, '5C2626D', 'ملک آباد', 1216, 'C'),
	(486, 'F5DCFC4', 'نشتیفان', 1216, 'C'),
	(487, '086329E', 'نصر آباد', 1216, 'C'),
	(488, '3E8C3F2', 'نقاب', 1216, 'C'),
	(489, '524D712', 'نوخندان', 1216, 'C'),
	(490, '988F1C3', 'نیشابور', 1216, 'C'),
	(491, '8A31BF5', 'نیل شهر', 1216, 'C'),
	(492, '20E2DEA', 'همت آباد', 1216, 'C'),
	(493, '792F410', 'یونسی', 1216, 'C'),
	(494, '28C1735', 'اسفراین', 1217, 'C'),
	(495, 'BDDA932', 'ایور', 1217, 'C'),
	(496, 'A83EBB0', 'آشخانه', 1217, 'C'),
	(497, '4F1A86F', 'بجنورد', 1217, 'C'),
	(498, 'C16371C', 'پیش قلعه', 1217, 'C'),
	(499, '35F3AE7', 'تیتکانلو', 1217, 'C'),
	(500, 'F22032A', 'جاجرم', 1217, 'C'),
	(501, '987BB0D', 'حصارگرمخان', 1217, 'C'),
	(502, '11717DF', 'درق', 1217, 'C'),
	(503, '0DA14E5', 'راز', 1217, 'C'),
	(504, '409BC3A', 'سنخواست', 1217, 'C'),
	(505, 'BBDE677', 'شوقان', 1217, 'C'),
	(506, 'A0A0036', 'شیروان', 1217, 'C'),
	(507, '608FBEE', 'صفی آباد', 1217, 'C'),
	(508, '3F6B3C5', 'فاروج', 1217, 'C'),
	(509, 'BF1294D', 'قاضی', 1217, 'C'),
	(510, '613A99E', 'گرمه', 1217, 'C'),
	(511, '187AC28', 'لوجلی', 1217, 'C'),
	(512, 'F3AB784', 'اروندکنار', 1218, 'C'),
	(513, '0F713F5', 'الوان', 1218, 'C'),
	(514, '97AB45B', 'امیدیه', 1218, 'C'),
	(515, 'BD93069', 'اندیمشک', 1218, 'C'),
	(516, '92BBC62', 'اهواز', 1218, 'C'),
	(517, '3418C51', 'ایذه', 1218, 'C'),
	(518, '210A32A', 'آبادان', 1218, 'C'),
	(519, '4815B91', 'آغاجاری', 1218, 'C'),
	(520, '9DFD347', 'باغ ملک', 1218, 'C'),
	(521, 'EDC655C', 'بستان', 1218, 'C'),
	(522, 'F363DA4', 'بندرامام خمینی', 1218, 'C'),
	(523, '90873F7', 'بندرماهشهر', 1218, 'C'),
	(524, 'EED8764', 'بهبهان', 1218, 'C'),
	(525, 'FCA92E9', 'ترکالکی', 1218, 'C'),
	(526, 'C3A2D35', 'جایزان', 1218, 'C'),
	(527, 'EFC30A4', 'جنت مکان', 1218, 'C'),
	(528, '47DEC2D', 'چغامیش', 1218, 'C'),
	(529, 'D2982EE', 'چمران', 1218, 'C'),
	(530, '2029FF4', 'چوئبده', 1218, 'C'),
	(531, 'E7C6011', 'حر', 1218, 'C'),
	(532, 'E225121', 'حسینیه', 1218, 'C'),
	(533, 'ECF8A24', 'حمزه', 1218, 'C'),
	(534, '5E7D857', 'حمیدیه', 1218, 'C'),
	(535, '1CF31B9', 'خرمشهر', 1218, 'C'),
	(536, 'E781E1B', 'دارخوین', 1218, 'C'),
	(537, '0BFE71B', 'دزآب', 1218, 'C'),
	(538, 'AFDA4F9', 'دزفول', 1218, 'C'),
	(539, '79237E5', 'دهدز', 1218, 'C'),
	(540, '353443E', 'رامشیر', 1218, 'C'),
	(541, '184044F', 'رامهرمز', 1218, 'C'),
	(542, '6085270', 'رفیع', 1218, 'C'),
	(543, '1232A17', 'زهره', 1218, 'C'),
	(544, '201E6D1', 'سالند', 1218, 'C'),
	(545, '77F1DAB', 'سردشت', 1218, 'C'),
	(546, '86875F4', 'سماله', 1218, 'C'),
	(547, '8BCF0C3', 'سوسنگرد', 1218, 'C'),
	(548, 'D101AAF', 'شادگان', 1218, 'C'),
	(549, '26825D3', 'شاوور', 1218, 'C'),
	(550, 'BF92FD3', 'شرافت', 1218, 'C'),
	(551, 'FE02BC5', 'شوش', 1218, 'C'),
	(552, '7DFBF2F', 'شوشتر', 1218, 'C'),
	(553, '46A8DDD', 'شیبان', 1218, 'C'),
	(554, '00D4BDE', 'صالح شهر', 1218, 'C'),
	(555, 'E899CD5', 'صالح مشطط', 1218, 'C'),
	(556, '9602D60', 'صفی آباد', 1218, 'C'),
	(557, '2AD9DC6', 'صیدون', 1218, 'C'),
	(558, 'C079A7F', 'قلعه تل', 1218, 'C'),
	(559, 'AB404E9', 'قلعه خواجه', 1218, 'C'),
	(560, '485A9F6', 'گتوند', 1218, 'C'),
	(561, '86F9E1B', 'گوریه', 1218, 'C'),
	(562, 'BAFF24E', 'لالی', 1218, 'C'),
	(563, 'F7BC792', 'مسجدسلیمان', 1218, 'C'),
	(564, 'C4B95ED', 'مشراگه', 1218, 'C'),
	(565, '7D5CC38', 'مقاومت', 1218, 'C'),
	(566, '8039BA8', 'ملاثانی', 1218, 'C'),
	(567, '67D04E8', 'میانرود', 1218, 'C'),
	(568, '3F0204E', 'میداود', 1218, 'C'),
	(569, 'B3B618D', 'مینوشهر', 1218, 'C'),
	(570, 'C4E8141', 'ویس', 1218, 'C'),
	(571, 'D92596D', 'هفتگل', 1218, 'C'),
	(572, '7CC5765', 'هندیجان', 1218, 'C'),
	(573, 'CDD6287', 'هویزه', 1218, 'C'),
	(574, '7FD5900', 'ابهر', 1219, 'C'),
	(575, '28FF691', 'ارمغانخانه', 1219, 'C'),
	(576, '7C4E961', 'آب بر', 1219, 'C'),
	(577, '85ABB01', 'چورزق', 1219, 'C'),
	(578, 'EB3DCD0', 'حلب', 1219, 'C'),
	(579, 'EC034B9', 'خرمدره', 1219, 'C'),
	(580, '21929C7', 'دندی', 1219, 'C'),
	(581, 'CC1BC9C', 'زرین آباد', 1219, 'C'),
	(582, '1192027', 'زرین رود', 1219, 'C'),
	(583, '554E004', 'زنجان', 1219, 'C'),
	(584, '90EF81B', 'سجاس', 1219, 'C'),
	(585, '04CFCA4', 'سلطانیه', 1219, 'C'),
	(586, 'FF912C5', 'سهرورد', 1219, 'C'),
	(587, 'D85D5A1', 'صائین قلعه', 1219, 'C'),
	(588, 'B6E8E6B', 'قیدار', 1219, 'C'),
	(589, '5A3EAB9', 'گرماب', 1219, 'C'),
	(590, 'A47D79B', 'ماه نشان', 1219, 'C'),
	(591, '8C01D95', 'هیدج', 1219, 'C'),
	(592, '88F72C3', 'امیریه', 1220, 'C'),
	(593, '5C38154', 'ایوانکی', 1220, 'C'),
	(594, 'A273364', 'آرادان', 1220, 'C'),
	(595, 'DEAE8BD', 'بسطام', 1220, 'C'),
	(596, '98FC13C', 'بیارجمند', 1220, 'C'),
	(597, 'FF2DB37', 'دامغان', 1220, 'C'),
	(598, '12C51E4', 'درجزین', 1220, 'C'),
	(599, '54D7CCF', 'دیباج', 1220, 'C'),
	(600, 'E6B876B', 'سرخه', 1220, 'C'),
	(601, '7303BA3', 'سمنان', 1220, 'C'),
	(602, '6B54F93', 'شاهرود', 1220, 'C'),
	(603, 'E878612', 'شهمیرزاد', 1220, 'C'),
	(604, '2FEE793', 'کلاته خیج', 1220, 'C'),
	(605, '3A9380C', 'گرمسار', 1220, 'C'),
	(606, 'FAEA1E5', 'مجن', 1220, 'C'),
	(607, '98EB885', 'مهدی شهر', 1220, 'C'),
	(608, '658F60D', 'میامی', 1220, 'C'),
	(609, 'E7F3523', 'ادیمی', 1221, 'C'),
	(610, '1FCBBEB', 'اسپکه', 1221, 'C'),
	(611, '1A5D269', 'ایرانشهر', 1221, 'C'),
	(612, '998ED50', 'بزمان', 1221, 'C'),
	(613, 'FBB2968', 'بمپور', 1221, 'C'),
	(614, '28BEE5D', 'بنت', 1221, 'C'),
	(615, '49371E3', 'بنجار', 1221, 'C'),
	(616, '7FA3B5A', 'پیشین', 1221, 'C'),
	(617, '939C6C7', 'جالق', 1221, 'C'),
	(618, '5884FBD', 'چاه بهار', 1221, 'C'),
	(619, '35898AB', 'خاش', 1221, 'C'),
	(620, 'A8C439E', 'دوست محمد', 1221, 'C'),
	(621, '0BAD625', 'راسک', 1221, 'C'),
	(622, '909C7AD', 'زابل', 1221, 'C'),
	(623, '2412D0A', 'زابلی', 1221, 'C'),
	(624, 'AA2A2E5', 'زاهدان', 1221, 'C'),
	(625, '2D934D6', 'زرآباد', 1221, 'C'),
	(626, 'E3D0D27', 'زهک', 1221, 'C'),
	(627, 'B1B05F9', 'سراوان', 1221, 'C'),
	(628, 'B2E8469', 'سرباز', 1221, 'C'),
	(629, 'BDC7F35', 'سوران', 1221, 'C'),
	(630, 'D82D1A3', 'سیرکان', 1221, 'C'),
	(631, 'B46BDCB', 'علی اکبر', 1221, 'C'),
	(632, '4BCCC78', 'فنوج', 1221, 'C'),
	(633, 'FF296E6', 'قصرقند', 1221, 'C'),
	(634, '842F451', 'کنارک', 1221, 'C'),
	(635, 'A23098C', 'گشت', 1221, 'C'),
	(636, '8D2021F', 'گلمورتی', 1221, 'C'),
	(637, '1C373FA', 'محمدان', 1221, 'C'),
	(638, '67A150E', 'محمد آباد', 1221, 'C'),
	(639, 'BDB0FDD', 'محمدی', 1221, 'C'),
	(640, '1E0CD9C', 'میرجاوه', 1221, 'C'),
	(641, 'F649485', 'نصرت آباد', 1221, 'C'),
	(642, 'DC9AFD4', 'نگور', 1221, 'C'),
	(643, '50E6D5B', 'نوک آباد', 1221, 'C'),
	(644, '3ECE4CE', 'نیک شهر', 1221, 'C'),
	(645, 'B96E6B2', 'هیدوج', 1221, 'C'),
	(646, 'AB00E48', 'اردکان', 1222, 'C'),
	(647, 'D14ABCE', 'ارسنجان', 1222, 'C'),
	(648, '9E05780', 'استهبان', 1222, 'C'),
	(649, 'A4194EF', 'اسیر', 1222, 'C'),
	(650, '6FC766C', 'اشکنان', 1222, 'C'),
	(651, 'E004E36', 'افزر', 1222, 'C'),
	(652, 'EEC0ECF', 'اقلید', 1222, 'C'),
	(653, '0577C73', 'امام شهر', 1222, 'C'),
	(654, '970D956', 'اوز', 1222, 'C'),
	(655, '5F787DC', 'اهل', 1222, 'C'),
	(656, '88DA525', 'ایج', 1222, 'C'),
	(657, '914DA4F', 'ایزد', 1222, 'C'),
	(658, 'F0F596B', 'آباده', 1222, 'C'),
	(659, '3749C70', 'آباده طشک', 1222, 'C'),
	(660, '5E0B93A', 'باب انار', 1222, 'C'),
	(661, '3B77709', 'بالاده', 1222, 'C'),
	(662, '08C7F05', 'بنارویه', 1222, 'C'),
	(663, '584ABD0', 'بوانات', 1222, 'C'),
	(664, 'E376CE9', 'بهمن', 1222, 'C'),
	(665, 'E5ADBA2', 'بیرم', 1222, 'C'),
	(666, '313CCD4', 'بیضا', 1222, 'C'),
	(667, '2D165A5', 'جنت شهر', 1222, 'C'),
	(668, 'F0F6E31', 'جویم', 1222, 'C'),
	(669, '4B26C23', 'جهرم', 1222, 'C'),
	(670, '57BD3DF', 'حاجی آباد', 1222, 'C'),
	(671, 'D29EA27', 'حسامی', 1222, 'C'),
	(672, 'D5C1135', 'حسن آباد', 1222, 'C'),
	(673, '572558A', 'خانه زنیان', 1222, 'C'),
	(674, '4394538', 'خاوران', 1222, 'C'),
	(675, '13D7CB7', 'خرامه', 1222, 'C'),
	(676, 'A70813F', 'خشت', 1222, 'C'),
	(677, '9E15FDC', 'خنج', 1222, 'C'),
	(678, '329F90B', 'خور', 1222, 'C'),
	(679, '76374F5', 'خومه زار', 1222, 'C'),
	(680, '55D377C', 'داراب', 1222, 'C'),
	(681, '1B3FD9E', 'داریان', 1222, 'C'),
	(682, 'EA27C60', 'دبیران', 1222, 'C'),
	(683, '24D9457', 'دژکرد', 1222, 'C'),
	(684, 'B2F510F', 'دوبرجی', 1222, 'C'),
	(685, '3917261', 'دوزه', 1222, 'C'),
	(686, '680E891', 'دهرم', 1222, 'C'),
	(687, '0833E94', 'رامجرد', 1222, 'C'),
	(688, 'DD56416', 'رونیز', 1222, 'C'),
	(689, '8398DA0', 'زاهدشهر', 1222, 'C'),
	(690, '233B6A9', 'زرقان', 1222, 'C'),
	(691, '9285C6B', 'سده', 1222, 'C'),
	(692, '91FC00C', 'سروستان', 1222, 'C'),
	(693, '5F2506F', 'سعادت شهر', 1222, 'C'),
	(694, 'B1CF09C', 'سورمق', 1222, 'C'),
	(695, '381BA3E', 'سیدان', 1222, 'C'),
	(696, 'B94458B', 'ششده', 1222, 'C'),
	(697, 'D7C049B', 'شهر جدید صدرا', 1222, 'C'),
	(698, 'EE110BE', 'شهرپیر', 1222, 'C'),
	(699, 'A5922CC', 'شیراز', 1222, 'C'),
	(700, '85211A1', 'صغاد', 1222, 'C'),
	(701, '77C79A8', 'صفاشهر', 1222, 'C'),
	(702, 'D15798A', 'علامرودشت', 1222, 'C'),
	(703, '075DF1D', 'عمادده', 1222, 'C'),
	(704, '81C3D59', 'فدامی', 1222, 'C'),
	(705, '00D2430', 'فراشبند', 1222, 'C'),
	(706, '5928A07', 'فسا', 1222, 'C'),
	(707, '81568E3', 'فیروزآباد', 1222, 'C'),
	(708, '7FFC67E', 'قادرآباد', 1222, 'C'),
	(709, '1BA7F3A', 'قائمیه', 1222, 'C'),
	(710, '9A249A1', 'قطب آباد', 1222, 'C'),
	(711, 'A57748E', 'قطرویه', 1222, 'C'),
	(712, '3836557', 'قیر', 1222, 'C'),
	(713, '09DBCF9', 'کارزین', 1222, 'C'),
	(714, 'BE3257A', 'کازرون', 1222, 'C'),
	(715, 'DA087F1', 'کامفیروز', 1222, 'C'),
	(716, '3D29E5A', 'کره ای', 1222, 'C'),
	(717, 'E63C51A', 'کنارتخته', 1222, 'C'),
	(718, '15409D3', 'کوار', 1222, 'C'),
	(719, 'F8EF7F7', 'کوهنجان', 1222, 'C'),
	(720, 'A804670', 'گراش', 1222, 'C'),
	(721, 'FCCA925', 'گله دار', 1222, 'C'),
	(722, 'F338832', 'لار', 1222, 'C'),
	(723, '81F6671', 'لامرد', 1222, 'C'),
	(724, 'B24B733', 'لپوئی', 1222, 'C'),
	(725, '8A853BB', 'لطیفی', 1222, 'C'),
	(726, '6442347', 'مبارک آباد', 1222, 'C'),
	(727, 'C29CDCD', 'مرودشت', 1222, 'C'),
	(728, 'A9C249D', 'مشکان', 1222, 'C'),
	(729, '831345A', 'مصیری', 1222, 'C'),
	(730, '6304F5E', 'مهر', 1222, 'C'),
	(731, '6D33B12', 'میمند', 1222, 'C'),
	(732, '8B51340', 'نوبندگان', 1222, 'C'),
	(733, '166F4E2', 'نوجین', 1222, 'C'),
	(734, '5AAF760', 'نودان', 1222, 'C'),
	(735, 'E718632', 'نورآباد', 1222, 'C'),
	(736, '301C40E', 'نی ریز', 1222, 'C'),
	(737, '990AC00', 'وراوی', 1222, 'C'),
	(738, 'BA1191F', 'هماشهر', 1222, 'C'),
	(739, '835D0EE', 'ارداق', 1223, 'C'),
	(740, '012BAFC', 'اسفرورین', 1223, 'C'),
	(741, 'D1CA440', 'اقبالیه', 1223, 'C'),
	(742, '546558A', 'الوند', 1223, 'C'),
	(743, 'FA6C1F2', 'آبگرم', 1223, 'C'),
	(744, 'C1D0416', 'آبیک', 1223, 'C'),
	(745, '71D24E3', 'آوج', 1223, 'C'),
	(746, '3F38BEF', 'بوئین زهرا', 1223, 'C'),
	(747, 'E20A91B', 'بیدستان', 1223, 'C'),
	(748, '48D82B0', 'تاکستان', 1223, 'C'),
	(749, 'D870BCC', 'خاکعلی', 1223, 'C'),
	(750, '1F456F0', 'خرمدشت', 1223, 'C'),
	(751, 'C225174', 'دانسفهان', 1223, 'C'),
	(752, 'DC5F5B9', 'رازمیان', 1223, 'C'),
	(753, '9BE6C0D', 'سگزآباد', 1223, 'C'),
	(754, '0B9C31B', 'سیردان', 1223, 'C'),
	(755, '8A54371', 'شال', 1223, 'C'),
	(756, '395EB40', 'شریفیه', 1223, 'C'),
	(757, 'D2C54E2', 'ضیاءآباد', 1223, 'C'),
	(758, 'C7CD6EF', 'قزوین', 1223, 'C'),
	(759, '5E61D4F', 'کوهین', 1223, 'C'),
	(760, '5C2FFED', 'محمدیه', 1223, 'C'),
	(761, '84794C1', 'محمودآبادنمونه', 1223, 'C'),
	(762, '57A9930', 'معلم کلایه', 1223, 'C'),
	(763, 'B4B1F80', 'نرجه', 1223, 'C'),
	(764, '83D3393', 'جعفریه', 1224, 'C'),
	(765, 'E9FA721', 'دستجرد', 1224, 'C'),
	(766, 'C13CBC3', 'سلفچگان', 1224, 'C'),
	(767, '0CDC78A', 'قم', 1224, 'C'),
	(768, 'DD3B652', 'قنوات', 1224, 'C'),
	(769, '85FB93D', 'کهک', 1224, 'C'),
	(770, '0426CA9', 'آرمرده', 1225, 'C'),
	(771, '767353B', 'بابارشانی', 1225, 'C'),
	(772, '908DBD5', 'بانه', 1225, 'C'),
	(773, '5FAD22B', 'بلبان آباد', 1225, 'C'),
	(774, 'C5C56D7', 'بوئین سفلی', 1225, 'C'),
	(775, '93099A4', 'بیجار', 1225, 'C'),
	(776, 'CFE9F61', 'چناره', 1225, 'C'),
	(777, '11787A1', 'دزج', 1225, 'C'),
	(778, '6F2DB7F', 'دلبران', 1225, 'C'),
	(779, '9DCA1D2', 'دهگلان', 1225, 'C'),
	(780, 'FADA2BB', 'دیواندره', 1225, 'C'),
	(781, '0320950', 'زرینه', 1225, 'C'),
	(782, 'C17124F', 'سروآباد', 1225, 'C'),
	(783, '8D43523', 'سریش آباد', 1225, 'C'),
	(784, '6A90A2C', 'سقز', 1225, 'C'),
	(785, 'AAED3B5', 'سنندج', 1225, 'C'),
	(786, '3983259', 'شویشه', 1225, 'C'),
	(787, '9DA5F5C', 'صاحب', 1225, 'C'),
	(788, 'B0538F7', 'قروه', 1225, 'C'),
	(789, '1E8BDA8', 'کامیاران', 1225, 'C'),
	(790, '64AACDF', 'کانی دینار', 1225, 'C'),
	(791, 'AF4B517', 'کانی سور', 1225, 'C'),
	(792, '1B88D51', 'مریوان', 1225, 'C'),
	(793, '8F28DC7', 'موچش', 1225, 'C'),
	(794, 'B8B74B3', 'یاسوکند', 1225, 'C'),
	(795, '2B5CC6C', 'اختیارآباد', 1226, 'C'),
	(796, 'DEB07D8', 'ارزوئیه', 1226, 'C'),
	(797, 'EF501F5', 'امین شهر', 1226, 'C'),
	(798, '935F039', 'انار', 1226, 'C'),
	(799, '3B32444', 'اندوهجرد', 1226, 'C'),
	(800, '5678616', 'باغین', 1226, 'C'),
	(801, '5E31EC7', 'بافت', 1226, 'C'),
	(802, '3D1BCBA', 'بردسیر', 1226, 'C'),
	(803, 'C239D54', 'بروات', 1226, 'C'),
	(804, '9EA38D4', 'بزنجان', 1226, 'C'),
	(805, 'D70AC71', 'بم', 1226, 'C'),
	(806, 'F09AC8E', 'بهرمان', 1226, 'C'),
	(807, '63302A1', 'پاریز', 1226, 'C'),
	(808, 'C1AC625', 'جبالبارز', 1226, 'C'),
	(809, '8A52672', 'جوپار', 1226, 'C'),
	(810, 'E8F37C4', 'جوزم', 1226, 'C'),
	(811, '6558832', 'جیرفت', 1226, 'C'),
	(812, '5525B61', 'چترود', 1226, 'C'),
	(813, 'B47DD51', 'خاتون آباد', 1226, 'C'),
	(814, '62E73FB', 'خانوک', 1226, 'C'),
	(815, '482CA86', 'خورسند', 1226, 'C'),
	(816, '1169F5D', 'درب بهشت', 1226, 'C'),
	(817, '7C6AAFF', 'دوساری', 1226, 'C'),
	(818, 'B5D77A9', 'دهج', 1226, 'C'),
	(819, 'BFD0632', 'رابر', 1226, 'C'),
	(820, '92D4430', 'راور', 1226, 'C'),
	(821, '333B3B0', 'راین', 1226, 'C'),
	(822, '305D6F4', 'رفسنجان', 1226, 'C'),
	(823, '393BE40', 'رودبار', 1226, 'C'),
	(824, 'FCCFC39', 'ریحان شهر', 1226, 'C'),
	(825, '8C9C0DB', 'زرند', 1226, 'C'),
	(826, '7D7444D', 'زنگی آباد', 1226, 'C'),
	(827, '940DA77', 'زیدآباد', 1226, 'C'),
	(828, 'E2F394C', 'سرچشمه', 1226, 'C'),
	(829, 'FE21750', 'سیرجان', 1226, 'C'),
	(830, '04A651D', 'شهداد', 1226, 'C'),
	(831, '3EC8F19', 'شهربابک', 1226, 'C'),
	(832, '8AC3B8A', 'صفائیه', 1226, 'C'),
	(833, '6C5B961', 'عنبرآباد', 1226, 'C'),
	(834, 'DFDC326', 'فاریاب', 1226, 'C'),
	(835, '5FD3751', 'فهرج', 1226, 'C'),
	(836, '940A32A', 'قلعه گنج', 1226, 'C'),
	(837, 'CCB4E11', 'کاظم آباد', 1226, 'C'),
	(838, 'E1EC8CE', 'کرمان', 1226, 'C'),
	(839, '9857D02', 'کشکوئیه', 1226, 'C'),
	(840, 'B32A483', 'کوهبنان', 1226, 'C'),
	(841, 'B9D5D38', 'کهنوج', 1226, 'C'),
	(842, '24B146F', 'کیانشهر', 1226, 'C'),
	(843, '118E864', 'گلباف', 1226, 'C'),
	(844, 'D2F7A7C', 'گلزار', 1226, 'C'),
	(845, '28E1021', 'لاله زار', 1226, 'C'),
	(846, '77F0628', 'ماهان', 1226, 'C'),
	(847, 'A8765A4', 'محمد آباد', 1226, 'C'),
	(848, 'D2A9211', 'محی آباد', 1226, 'C'),
	(849, '2D9F3B9', 'مردهک', 1226, 'C'),
	(850, '1F44419', 'منوجان', 1226, 'C'),
	(851, '548D795', 'نجف شهر', 1226, 'C'),
	(852, '7A11C2C', 'نرماشیر', 1226, 'C'),
	(853, '60F4904', 'نظام شهر', 1226, 'C'),
	(854, '36B3966', 'نگار', 1226, 'C'),
	(855, '2B045EE', 'نودژ', 1226, 'C'),
	(856, '2CE73B9', 'هجدک', 1226, 'C'),
	(857, '523435E', 'هماشهر', 1226, 'C'),
	(858, '36FBCFD', 'یزد', 1226, 'C'),
	(859, 'F48F90D', 'ازگله', 1227, 'C'),
	(860, '1A8F3BA', 'اسلام آبادغرب', 1227, 'C'),
	(861, '357D86C', 'باینگان', 1227, 'C'),
	(862, 'B1C3DB8', 'بیستون', 1227, 'C'),
	(863, 'F926635', 'پاوه', 1227, 'C'),
	(864, '569A1DB', 'تازه آباد', 1227, 'C'),
	(865, '3CCD83B', 'جوانرود', 1227, 'C'),
	(866, '538DAB3', 'حمیل', 1227, 'C'),
	(867, '2768CD2', 'رباط', 1227, 'C'),
	(868, 'DA15C7F', 'روانسر', 1227, 'C'),
	(869, 'CA9B3FC', 'سرپل ذهاب', 1227, 'C'),
	(870, 'B137196', 'سرمست', 1227, 'C'),
	(871, '0912D99', 'سطر', 1227, 'C'),
	(872, '9BDD914', 'سنقر', 1227, 'C'),
	(873, '1E8EEF7', 'سومار', 1227, 'C'),
	(874, '87ED0E2', 'شاهو', 1227, 'C'),
	(875, '1B383F8', 'صحنه', 1227, 'C'),
	(876, '65C4090', 'قصرشیرین', 1227, 'C'),
	(877, '89E9DF0', 'کرمان شاه', 1227, 'C'),
	(878, 'DBF7347', 'کرندغرب', 1227, 'C'),
	(879, '91A8C15', 'کنگاور', 1227, 'C'),
	(880, '72D15E6', 'کوزران', 1227, 'C'),
	(881, 'DA3136E', 'گهواره', 1227, 'C'),
	(882, '7F620B1', 'گیلان', 1227, 'C'),
	(883, '1AD34FA', 'میان راهان', 1227, 'C'),
	(884, '91832AC', 'نودشه', 1227, 'C'),
	(885, '0380052', 'نوسود', 1227, 'C'),
	(886, '114D506', 'هرسین', 1227, 'C'),
	(887, '25A5F4D', 'هلشی', 1227, 'C'),
	(888, '79304B0', 'باشت', 1228, 'C'),
	(889, '3091449', 'پاتاوه', 1228, 'C'),
	(890, '4C42AAE', 'چرام', 1228, 'C'),
	(891, 'BEE150E', 'چیتاب', 1228, 'C'),
	(892, '2E87B05', 'دوگنبدان', 1228, 'C'),
	(893, '2562220', 'دهدشت', 1228, 'C'),
	(894, '6E596F8', 'دیشموک', 1228, 'C'),
	(895, 'F8D93C0', 'سوق', 1228, 'C'),
	(896, 'DFAB382', 'سی سخت', 1228, 'C'),
	(897, 'A3A38A6', 'قلعه رئیسی', 1228, 'C'),
	(898, '147BF71', 'گراب سفلی', 1228, 'C'),
	(899, 'EE7F113', 'لنده', 1228, 'C'),
	(900, '77D2DF0', 'لیکک', 1228, 'C'),
	(901, 'A0C3E7F', 'مادوان', 1228, 'C'),
	(902, '4E177E5', 'مارگون', 1228, 'C'),
	(903, '345432B', 'یاسوج', 1228, 'C'),
	(904, 'DFDC384', 'انبارآلوم', 1229, 'C'),
	(905, 'CC9EB48', 'اینچه برون', 1229, 'C'),
	(906, '2AD354A', 'آزادشهر', 1229, 'C'),
	(907, 'BC149BA', 'آق قلا', 1229, 'C'),
	(908, 'AD3929C', 'بندرگز', 1229, 'C'),
	(909, '9B9B68C', 'ترکمن', 1229, 'C'),
	(910, '70CC6BF', 'جلین', 1229, 'C'),
	(911, 'F70386E', 'خان ببین', 1229, 'C'),
	(912, '5F5F652', 'دلند', 1229, 'C'),
	(913, 'BE73EF4', 'رامیان', 1229, 'C'),
	(914, 'F452084', 'سرخنکلاته', 1229, 'C'),
	(915, '4052F9C', 'سیمین شهر', 1229, 'C'),
	(916, '33D448B', 'علی آباد', 1229, 'C'),
	(917, 'DD93233', 'فاضل آباد', 1229, 'C'),
	(918, '8AE321F', 'کردکوی', 1229, 'C'),
	(919, '6D9B5EE', 'کلاله', 1229, 'C'),
	(920, 'B3BB171', 'گالیکش', 1229, 'C'),
	(921, 'E1887CF', 'گرگان', 1229, 'C'),
	(922, 'E1311A7', 'گمیش تپه', 1229, 'C'),
	(923, 'FECAEF3', 'گنبد کاووس', 1229, 'C'),
	(924, '2A61535', 'مراوه تپه', 1229, 'C'),
	(925, '0AE15C6', 'مینودشت', 1229, 'C'),
	(926, 'F7551EB', 'نگین شهر', 1229, 'C'),
	(927, 'CDEC7A6', 'نوده خاندوز', 1229, 'C'),
	(928, '3F1D20D', 'نوکنده', 1229, 'C'),
	(929, 'C994A85', 'احمدسرگوراب', 1230, 'C'),
	(930, 'E7D9987', 'اسالم', 1230, 'C'),
	(931, '4C24CB7', 'اطاقور', 1230, 'C'),
	(932, '81819DD', 'املش', 1230, 'C'),
	(933, '4E700FC', 'آستارا', 1230, 'C'),
	(934, 'F5E8BFD', 'آستانه اشرفیه', 1230, 'C'),
	(935, '708E31E', 'بازارجمعه', 1230, 'C'),
	(936, 'E30B444', 'بره سر', 1230, 'C'),
	(937, '726BD76', 'بندرانزلی', 1230, 'C'),
	(938, '8AE2D52', 'پره سر', 1230, 'C'),
	(939, '224FEE6', 'توتکابن', 1230, 'C'),
	(940, '4014975', 'جیرنده', 1230, 'C'),
	(941, 'E008A5F', 'چابکسر', 1230, 'C'),
	(942, 'A1CADCA', 'چاف وچمخاله', 1230, 'C'),
	(943, '53F5D4F', 'چوبر', 1230, 'C'),
	(944, 'E62661C', 'حویق', 1230, 'C'),
	(945, '81B2F11', 'خشکبیجار', 1230, 'C'),
	(946, 'ADBEDF4', 'خمام', 1230, 'C'),
	(947, '2D58DBB', 'دیلمان', 1230, 'C'),
	(948, '958BB13', 'رانکوه', 1230, 'C'),
	(949, '3E7A82B', 'رحیم آباد', 1230, 'C'),
	(950, '6B2D009', 'رستم آباد', 1230, 'C'),
	(951, '45C6E00', 'رشت', 1230, 'C'),
	(952, '4F7A4B2', 'رضوانشهر', 1230, 'C'),
	(953, 'E24FD21', 'رودبار', 1230, 'C'),
	(954, '003CB40', 'رودبنه', 1230, 'C'),
	(955, 'B6180DF', 'رودسر', 1230, 'C'),
	(956, '04DD5D6', 'سنگر', 1230, 'C'),
	(957, 'FB62BFB', 'سیاهکل', 1230, 'C'),
	(958, 'EDC7AD4', 'شفت', 1230, 'C'),
	(959, 'B75DC64', 'شلمان', 1230, 'C'),
	(960, '8321678', 'صومعه سرا', 1230, 'C'),
	(961, '8DD6B66', 'فومن', 1230, 'C'),
	(962, 'C89D03C', 'کلاچای', 1230, 'C'),
	(963, '113EC2E', 'کوچصفهان', 1230, 'C'),
	(964, '0C6C26A', 'کومله', 1230, 'C'),
	(965, 'A78C213', 'کیاشهر', 1230, 'C'),
	(966, 'B91E0AA', 'گوراب زرمیخ', 1230, 'C'),
	(967, '78F81FA', 'لاهیجان', 1230, 'C'),
	(968, '7D0168D', 'لشت نشاء', 1230, 'C'),
	(969, '3F65E50', 'لنگرود', 1230, 'C'),
	(970, '3A23D39', 'لوشان', 1230, 'C'),
	(971, 'E845CBD', 'لولمان', 1230, 'C'),
	(972, '653D0F8', 'لوندویل', 1230, 'C'),
	(973, '728A9C8', 'لیسار', 1230, 'C'),
	(974, '0CBC606', 'ماسال', 1230, 'C'),
	(975, '8834442', 'ماسوله', 1230, 'C'),
	(976, '08A78AE', 'مرجقل', 1230, 'C'),
	(977, '62E5C5A', 'منجیل', 1230, 'C'),
	(978, 'FED350D', 'واجارگاه', 1230, 'C'),
	(979, 'AA6DEF3', 'هشتپر', 1230, 'C'),
	(980, '1E74D31', 'ازنا', 1231, 'C'),
	(981, '888EE2C', 'اشترینان', 1231, 'C'),
	(982, '188E601', 'الشتر', 1231, 'C'),
	(983, '070FA49', 'الیگودرز', 1231, 'C'),
	(984, '07E9F7E', 'بروجرد', 1231, 'C'),
	(985, 'E892E5F', 'پلدختر', 1231, 'C'),
	(986, 'F731D83', 'چالانچولان', 1231, 'C'),
	(987, 'F7364F6', 'چغلوندی', 1231, 'C'),
	(988, '6E339B4', 'چقابل', 1231, 'C'),
	(989, '7F3BA6D', 'خرم آباد', 1231, 'C'),
	(990, 'FC86761', 'درب گنبد', 1231, 'C'),
	(991, 'D2869DD', 'دورود', 1231, 'C'),
	(992, '3419B24', 'زاغه', 1231, 'C'),
	(993, '05584C9', 'سپیددشت', 1231, 'C'),
	(994, '2992952', 'سراب دوره', 1231, 'C'),
	(995, '9FA776E', 'شول آباد', 1231, 'C'),
	(996, '04E6B11', 'فیروز آباد', 1231, 'C'),
	(997, '7F68D35', 'کونانی', 1231, 'C'),
	(998, '5360A88', 'کوهدشت', 1231, 'C'),
	(999, '5817367', 'گراب', 1231, 'C'),
	(1000, 'C2375B2', 'معمولان', 1231, 'C'),
	(1001, '1EE173A', 'مؤمن آباد', 1231, 'C'),
	(1002, '5E2DACB', 'نور آباد', 1231, 'C'),
	(1003, '5D8FCA7', 'ویسیان', 1231, 'C'),
	(1004, 'C144412', 'هفت چشمه', 1231, 'C'),
	(1005, 'FF02044', 'امیرکلا', 1232, 'C'),
	(1006, '082462A', 'ایزد', 1232, 'C'),
	(1007, 'C1C8443', 'آلاشت', 1232, 'C'),
	(1008, '7FA297C', 'آمل', 1232, 'C'),
	(1009, 'BD3C0FA', 'بابل', 1232, 'C'),
	(1010, '81E5182', 'بابلسر', 1232, 'C'),
	(1011, 'EE24D02', 'بلده', 1232, 'C'),
	(1012, '287F781', 'بهشهر', 1232, 'C'),
	(1013, '55D37BA', 'بهنمیر', 1232, 'C'),
	(1014, '8B15E8D', 'پل سفید', 1232, 'C'),
	(1015, 'D8BA364', 'پول', 1232, 'C'),
	(1016, '43E7D99', 'تنکابن', 1232, 'C'),
	(1017, 'A2C7B24', 'جویبار', 1232, 'C'),
	(1018, '976F453', 'چالوس', 1232, 'C'),
	(1019, '11AB9D1', 'چمستان', 1232, 'C'),
	(1020, '54FBD26', 'خرم آباد', 1232, 'C'),
	(1021, '0416185', 'خلیل شهر', 1232, 'C'),
	(1022, 'FA14E40', 'خوش رودپی', 1232, 'C'),
	(1023, 'E602731', 'دابودشت', 1232, 'C'),
	(1024, '7664BEA', 'رامسر', 1232, 'C'),
	(1025, 'E73428B', 'رستمکلا', 1232, 'C'),
	(1026, '22C05BB', 'رویان', 1232, 'C'),
	(1027, '3A91995', 'رینه', 1232, 'C'),
	(1028, '5CADBFB', 'زرگر محله', 1232, 'C'),
	(1029, 'A2FD0A9', 'زیرآب', 1232, 'C'),
	(1030, '4D05D65', 'ساری', 1232, 'C'),
	(1031, '0DB6EF4', 'سرخرود', 1232, 'C'),
	(1032, '8257B05', 'سلمان شهر', 1232, 'C'),
	(1033, 'E93BCB9', 'سورک', 1232, 'C'),
	(1034, '4E06A32', 'شیرگاه', 1232, 'C'),
	(1035, '82FF1C3', 'شیرود', 1232, 'C'),
	(1036, '4653C08', 'عباس آباد', 1232, 'C'),
	(1037, '708C5A1', 'فریدونکنار', 1232, 'C'),
	(1038, '11DABFF', 'فریم', 1232, 'C'),
	(1039, 'F03F447', 'قائم شهر', 1232, 'C'),
	(1040, 'DCF7150', 'کتالم وسادات شهر', 1232, 'C'),
	(1041, '4D603E3', 'کلارآباد', 1232, 'C'),
	(1042, '14BF28F', 'کلاردشت', 1232, 'C'),
	(1043, '005FA3E', 'کله بست', 1232, 'C'),
	(1044, '039BD74', 'کوهی خیل', 1232, 'C'),
	(1045, '83CE88E', 'کیاسر', 1232, 'C'),
	(1046, '459CB11', 'کیاکلا', 1232, 'C'),
	(1047, '34278E2', 'گتاب', 1232, 'C'),
	(1048, '981A552', 'گزنک', 1232, 'C'),
	(1049, '9C246F5', 'گلوگاه', 1232, 'C'),
	(1050, '7B0A3D0', 'محمود آباد', 1232, 'C'),
	(1051, '8BD7A2B', 'مرزن آباد', 1232, 'C'),
	(1052, '9487849', 'مرزیکلا', 1232, 'C'),
	(1053, '258D99C', 'نشتارود', 1232, 'C'),
	(1054, '29090F8', 'نکا', 1232, 'C'),
	(1055, 'DC503D4', 'نور', 1232, 'C'),
	(1056, '5C727AD', 'نوشهر', 1232, 'C'),
	(1057, '88D69B0', 'اراک', 1233, 'C'),
	(1058, '5214697', 'آستانه', 1233, 'C'),
	(1059, 'B890036', 'آشتیان', 1233, 'C'),
	(1060, '6887079', 'پرندک', 1233, 'C'),
	(1061, '961BDA3', 'تفرش', 1233, 'C'),
	(1062, '4631F05', 'توره', 1233, 'C'),
	(1063, '6BDDEDC', 'جاورسیان', 1233, 'C'),
	(1064, '8231EB7', 'خشکرود', 1233, 'C'),
	(1065, '026C303', 'خمین', 1233, 'C'),
	(1066, '716AC88', 'خنداب', 1233, 'C'),
	(1067, 'B68D9B1', 'داودآباد', 1233, 'C'),
	(1068, 'B7CA6D3', 'دلیجان', 1233, 'C'),
	(1069, 'E1DC178', 'رازقان', 1233, 'C'),
	(1070, '4081CDA', 'زاویه', 1233, 'C'),
	(1071, 'B911420', 'ساروق', 1233, 'C'),
	(1072, '824ACCC', 'ساوه', 1233, 'C'),
	(1073, 'FE187F5', 'سنجان', 1233, 'C'),
	(1074, 'F82634B', 'شازند', 1233, 'C'),
	(1075, '89A223B', 'شهرجدیدمهاجران', 1233, 'C'),
	(1076, 'E324B2F', 'غرق آباد', 1233, 'C'),
	(1077, '61AB9C8', 'فرمهین', 1233, 'C'),
	(1078, '29E58AD', 'قورچی باشی', 1233, 'C'),
	(1079, 'E85437C', 'کرهرود', 1233, 'C'),
	(1080, 'F33C303', 'کمیجان', 1233, 'C'),
	(1081, '68B99BC', 'مامونیه', 1233, 'C'),
	(1082, '7BF54B7', 'محلات', 1233, 'C'),
	(1083, 'B2D9180', 'میلاجرد', 1233, 'C'),
	(1084, '8A5ECFC', 'نراق', 1233, 'C'),
	(1085, '6F96B9F', 'نوبران', 1233, 'C'),
	(1086, 'FE53EFF', 'نیمور', 1233, 'C'),
	(1087, '6E62E9E', 'هندودر', 1233, 'C'),
	(1088, 'FBA4552', 'ابوموسی', 1234, 'C'),
	(1089, '913676A', 'بستک', 1234, 'C'),
	(1090, '391DBD8', 'بندرجاسک', 1234, 'C'),
	(1091, '18B37CE', 'بندرچارک', 1234, 'C'),
	(1092, 'AE01648', 'بندرعباس', 1234, 'C'),
	(1093, '3958C5E', 'بندرلنگه', 1234, 'C'),
	(1094, 'A29C9F3', 'بیکاه', 1234, 'C'),
	(1095, 'C5DBC22', 'پارسیان', 1234, 'C'),
	(1096, '99CD2F5', 'تخت', 1234, 'C'),
	(1097, '0406266', 'جناح', 1234, 'C'),
	(1098, '892D35E', 'حاجی آباد', 1234, 'C'),
	(1099, 'DDC8B85', 'خمیر', 1234, 'C'),
	(1100, 'ADB9A66', 'درگهان', 1234, 'C'),
	(1101, 'D72275F', 'دهبارز', 1234, 'C'),
	(1102, 'A704180', 'رویدر', 1234, 'C'),
	(1103, '81991A2', 'زیارتعلی', 1234, 'C'),
	(1104, '63D8F43', 'سردشت بشاگرد', 1234, 'C'),
	(1105, '3C182F9', 'سرگز', 1234, 'C'),
	(1106, '67D1362', 'سندرک', 1234, 'C'),
	(1107, 'AEF95DB', 'سوزا', 1234, 'C'),
	(1108, '4E7121E', 'سیریک', 1234, 'C'),
	(1109, '0E1739A', 'فارغان', 1234, 'C'),
	(1110, '823AAC4', 'فین', 1234, 'C'),
	(1111, 'E3ED7C7', 'قشم', 1234, 'C'),
	(1112, 'C2DA152', 'قلعه قاضی', 1234, 'C'),
	(1113, '41FA888', 'کنگ', 1234, 'C'),
	(1114, '00D71CB', 'کوشکنار', 1234, 'C'),
	(1115, '6889FBC', 'کیش', 1234, 'C'),
	(1116, 'B77BBAE', 'گوهران', 1234, 'C'),
	(1117, 'ECC2379', 'میناب', 1234, 'C'),
	(1118, 'E2589A7', 'هرمز', 1234, 'C'),
	(1119, 'F349D6F', 'هشتبندی', 1234, 'C'),
	(1120, 'D6A4086', 'ازندریان', 1235, 'C'),
	(1121, '93330ED', 'اسدآباد', 1235, 'C'),
	(1122, '260921C', 'برزول', 1235, 'C'),
	(1123, '66AC65A', 'بهار', 1235, 'C'),
	(1124, '14D92A8', 'تویسرکان', 1235, 'C'),
	(1125, '555E344', 'جورقان', 1235, 'C'),
	(1126, '45390D2', 'جوکار', 1235, 'C'),
	(1127, 'A8EEF16', 'دمق', 1235, 'C'),
	(1128, '3C90312', 'رزن', 1235, 'C'),
	(1129, 'F563864', 'زنگنه', 1235, 'C'),
	(1130, 'FEBA26A', 'سامن', 1235, 'C'),
	(1131, '9376ED4', 'سرکان', 1235, 'C'),
	(1132, 'BCF0944', 'شیرین سو', 1235, 'C'),
	(1133, '7B120F0', 'صالح آباد', 1235, 'C'),
	(1134, 'D910CF1', 'فامنین', 1235, 'C'),
	(1135, '5399E7A', 'فرسفج', 1235, 'C'),
	(1136, '0640B02', 'فیروزان', 1235, 'C'),
	(1137, '4B00D45', 'قروه در جزین', 1235, 'C'),
	(1138, '0E48A63', 'قهاوند', 1235, 'C'),
	(1139, 'AC6FBE4', 'کبودرآهنگ', 1235, 'C'),
	(1140, '328C59E', 'گل تپه', 1235, 'C'),
	(1141, '6A4F1F2', 'گیان', 1235, 'C'),
	(1142, 'AD2DEF6', 'لالجین', 1235, 'C'),
	(1143, '4B1323E', 'مریانج', 1235, 'C'),
	(1144, '4E5ACCC', 'ملایر', 1235, 'C'),
	(1145, '06BBE74', 'نهاوند', 1235, 'C'),
	(1146, '6A871A5', 'همدان', 1235, 'C'),
	(1147, '5EFCA38', 'ابرکوه', 1236, 'C'),
	(1148, 'EE68619', 'احمدآباد', 1236, 'C'),
	(1149, '64A14CA', 'اردکان', 1236, 'C'),
	(1150, 'B41333D', 'اشکذر', 1236, 'C'),
	(1151, 'F547435', 'بافق', 1236, 'C'),
	(1152, '9BC88F6', 'بفروئیه', 1236, 'C'),
	(1153, '5C9B4A0', 'بهاباد', 1236, 'C'),
	(1154, '68D6B26', 'تفت', 1236, 'C'),
	(1155, '0EE7E7C', 'حمیدیا', 1236, 'C'),
	(1156, 'A85E700', 'خضرآباد', 1236, 'C'),
	(1157, 'AA6D640', 'دیهوک', 1236, 'C'),
	(1158, '7760965', 'زارچ', 1236, 'C'),
	(1159, 'F56AFA5', 'شاهدیه', 1236, 'C'),
	(1160, '9FB98B0', 'طبس', 1236, 'C'),
	(1161, '0C14BF9', 'عشق آباد', 1236, 'C'),
	(1162, 'F165FD4', 'عقدا', 1236, 'C'),
	(1163, '7B88309', 'مروست', 1236, 'C'),
	(1164, '369F543', 'مهردشت', 1236, 'C'),
	(1165, '7A41875', 'مهریز', 1236, 'C'),
	(1166, '9FE70EA', 'میبد', 1236, 'C'),
	(1167, 'DE609CB', 'ندوشن', 1236, 'C'),
	(1168, '44F6910', 'نیر', 1236, 'C'),
	(1169, 'F356CB2', 'هرات', 1236, 'C'),
	(1170, 'C540062', 'یزد', 1236, 'C'),
	(1239, '0C9A4C8', 'اردبیل', 1172, 'C'),
	(1240, '42C71D7', 'اصلاندوز', 1172, 'C'),
	(1241, 'D6E00E7', 'آبی بیگلو', 1172, 'C'),
	(1242, '03D44C9', 'بیله سوار', 1172, 'C'),
	(1243, 'B902CB9', 'پارس آباد', 1172, 'C'),
	(1244, '78814B0', 'تازه کند', 1172, 'C'),
	(1245, '326C767', 'تازه کندانگوت', 1172, 'C'),
	(1246, 'EE62B8A', 'جعفرآباد', 1172, 'C'),
	(1247, '2433E98', 'خلخال', 1172, 'C'),
	(1248, 'FB00E31', 'رضی', 1172, 'C'),
	(1249, '5F1D593', 'سرعین', 1172, 'C'),
	(1250, '6D171D8', 'عنبران', 1172, 'C'),
	(1251, 'AB69E36', 'فخرآباد', 1172, 'C'),
	(1252, 'F41E6D1', 'کلور', 1172, 'C'),
	(1253, '7A7DA3D', 'کوراییم', 1172, 'C'),
	(1254, '77B08BE', 'گرمی', 1172, 'C'),
	(1255, '872E19C', 'گیوی', 1172, 'C'),
	(1256, 'F05A8DA', 'لاهرود', 1172, 'C'),
	(1257, '9C74951', 'مرادلو', 1172, 'C'),
	(1258, '7540EBE', 'مشگین شهر', 1172, 'C'),
	(1259, '402442B', 'نمین', 1172, 'C'),
	(1260, '45EB72A', 'نیر', 1172, 'C'),
	(1261, '2C0AC11', 'هشتجین', 1172, 'C'),
	(1262, '3362CCE', 'هیر', 1172, 'C'),
	(1263, '5FC0BBA', 'ابریشم', 1173, 'C'),
	(1264, '99BD01F', 'ابوزیدآباد', 1173, 'C'),
	(1265, 'AA87844', 'اردستان', 1173, 'C'),
	(1266, 'E85F960', 'اژیه', 1173, 'C'),
	(1267, 'A1F09FB', 'اصفهان', 1173, 'C'),
	(1268, '7B34F01', 'افوس', 1173, 'C'),
	(1269, '1A723A2', 'انارک', 1173, 'C'),
	(1270, '7D91FED', 'ایمانشهر', 1173, 'C'),
	(1271, '3646B1D', 'آران وبیدگل', 1173, 'C'),
	(1272, '1419DDD', 'بادرود', 1173, 'C'),
	(1273, 'E249009', 'باغ بهادران', 1173, 'C'),
	(1274, '01787F9', 'بافران', 1173, 'C'),
	(1275, 'DB326E7', 'برزک', 1173, 'C'),
	(1276, '453C40F', 'برف انبار', 1173, 'C'),
	(1277, '9C5E60C', 'بوئین ومیاندشت', 1173, 'C'),
	(1278, '3B0E3DD', 'بهاران شهر', 1173, 'C'),
	(1279, 'CF3529E', 'بهارستان', 1173, 'C'),
	(1280, '20CB0EF', 'پیربکران', 1173, 'C'),
	(1281, 'AFDC33D', 'تودشک', 1173, 'C'),
	(1282, 'F211D27', 'تیران', 1173, 'C'),
	(1283, '3E90B12', 'جندق', 1173, 'C'),
	(1284, '4A92ED5', 'جوزدان', 1173, 'C'),
	(1285, 'FB267BC', 'جوشقان وکامو', 1173, 'C'),
	(1286, 'CAF1316', 'چادگان', 1173, 'C'),
	(1287, 'ADEA21F', 'چرمهین', 1173, 'C'),
	(1288, 'AD6CC0F', 'چمگردان', 1173, 'C'),
	(1289, 'A0AD0DD', 'حبیب آباد', 1173, 'C'),
	(1290, 'E475074', 'حسن آباد', 1173, 'C'),
	(1291, '233E7F5', 'حنا', 1173, 'C'),
	(1292, 'B0C8E70', 'خالدآباد', 1173, 'C'),
	(1293, '726CF89', 'خمینی شهر', 1173, 'C'),
	(1294, 'AE4928B', 'خوانسار', 1173, 'C'),
	(1295, 'F493E19', 'خور', 1173, 'C'),
	(1296, '4E4751A', 'خوراسگان', 1173, 'C'),
	(1297, 'D7CF293', 'خورزوق', 1173, 'C'),
	(1298, 'B94BA48', 'داران', 1173, 'C'),
	(1299, '3E58ACF', 'دامنه', 1173, 'C'),
	(1300, 'E69E4CB', 'درچه پیاز', 1173, 'C'),
	(1301, '1033AAB', 'دستگرد', 1173, 'C'),
	(1302, 'D4DA8F9', 'دولت آباد', 1173, 'C'),
	(1303, '63253A7', 'دهاقان', 1173, 'C'),
	(1304, '93B1439', 'دهق', 1173, 'C'),
	(1305, 'E4C5A30', 'دیزیچه', 1173, 'C'),
	(1306, '6F47E3C', 'رزوه', 1173, 'C'),
	(1307, 'C20E080', 'رضوانشهر', 1173, 'C'),
	(1308, 'F169692', 'زاینده رود', 1173, 'C'),
	(1309, '10BA982', 'زرین شهر', 1173, 'C'),
	(1310, '70C2127', 'زواره', 1173, 'C'),
	(1311, '855E26F', 'زیباشهر', 1173, 'C'),
	(1312, 'ECDECFA', 'سده لنجان', 1173, 'C'),
	(1313, '2FB16A0', 'سفیدشهر', 1173, 'C'),
	(1314, '32CDCB9', 'سگزی', 1173, 'C'),
	(1315, '0DC1295', 'سمیرم', 1173, 'C'),
	(1316, '2923DD8', 'شاپورآباد', 1173, 'C'),
	(1317, '4BD2E20', 'شاهین شهر', 1173, 'C'),
	(1318, 'B58D344', 'شهرضا', 1173, 'C'),
	(1319, '3C70B4B', 'طالخونچه', 1173, 'C'),
	(1320, '7D66E4E', 'عسگران', 1173, 'C'),
	(1321, 'F498FB0', 'علویچه', 1173, 'C'),
	(1322, '849534C', 'فرخی', 1173, 'C'),
	(1323, '83B894D', 'فریدونشهر', 1173, 'C'),
	(1324, 'E402EB2', 'فلاورجان', 1173, 'C'),
	(1325, '8F568DB', 'فولادشهر', 1173, 'C'),
	(1326, '2D28570', 'قم', 1173, 'C'),
	(1327, '9FBD046', 'قهجاورستان', 1173, 'C'),
	(1328, '5ED79E9', 'قهدریجان', 1173, 'C'),
	(1329, '6D0F481', 'کاشان', 1173, 'C'),
	(1330, '2BC70D1', 'کرکوند', 1173, 'C'),
	(1331, '898E7B3', 'کلیشادوسودرجان', 1173, 'C'),
	(1332, '3AF2346', 'کمشچه', 1173, 'C'),
	(1333, '60CDE6A', 'کمه', 1173, 'C'),
	(1334, 'DB927EE', 'کوشک', 1173, 'C'),
	(1335, 'FD8820D', 'کوهپایه', 1173, 'C'),
	(1336, 'E5FD2DB', 'کهریزسنگ', 1173, 'C'),
	(1337, 'BCD5A32', 'گرگاب', 1173, 'C'),
	(1338, 'D4B652C', 'گزبرخوار', 1173, 'C'),
	(1339, 'D890B6E', 'گلپایگان', 1173, 'C'),
	(1340, 'B41287D', 'گلدشت', 1173, 'C'),
	(1341, '84380ED', 'گلشن', 1173, 'C'),
	(1342, '83B0D06', 'گلشهر', 1173, 'C'),
	(1343, 'B532FAC', 'گوگد', 1173, 'C'),
	(1344, 'BAB3BD3', 'لای بید', 1173, 'C'),
	(1345, 'BCBDBD9', 'مبارکه', 1173, 'C'),
	(1346, '51B380B', 'محمدآباد', 1173, 'C'),
	(1347, '3359933', 'مشکات', 1173, 'C'),
	(1348, '0B9A9A3', 'منظریه', 1173, 'C'),
	(1349, 'EAF0944', 'مهاباد', 1173, 'C'),
	(1350, '4406051', 'میمه', 1173, 'C'),
	(1351, 'D80F23E', 'نائین', 1173, 'C'),
	(1352, '1DB2729', 'نجف آباد', 1173, 'C'),
	(1353, 'BE60DEF', 'نصرآباد', 1173, 'C'),
	(1354, '7FD45CA', 'نطنز', 1173, 'C'),
	(1355, '4024204', 'نوش آباد', 1173, 'C'),
	(1356, '59786BA', 'نیاسر', 1173, 'C'),
	(1357, '11214E7', 'نیک آباد', 1173, 'C'),
	(1358, '2E9D52D', 'ورزنه', 1173, 'C'),
	(1359, 'B2D6D38', 'ورنامخواست', 1173, 'C'),
	(1360, 'F33B981', 'وزوان', 1173, 'C')
;
SQL
		);

    $this->batchInsertIgnore('{{%AAA_User}}', [
      'usrID',
      'usrRoleID',
      'usrEmail',
      'usrEmailApprovedAt',
      'usrMobile',
      'usrMobileApprovedAt',
      'usrGender',
      'usrFirstName',
      'usrLastName',
      'usrStatus',
    ], [
      [
        /* usrID               */ 1,
        /* usrRoleID           */ NULL,
        /* usrEmail            */ 'system@site.dom',
        /* usrEmailApprovedAt  */ NULL,
        /* usrMobile           */ NULL,
        /* usrMobileApprovedAt */ NULL,
        /* usrGender           */ NULL,
        /* usrFirstName        */ NULL,
        /* usrLastName         */ NULL,
        /* usrStatus           */ enuUserStatus::Inactive,
      ],
      [
        /* usrID               */ 52,
        /* usrRoleID           */ 1,
        /* usrEmail            */ 'kambizzandi@gmail.com',
        /* usrEmailApprovedAt  */ new Expression('NOW()'),
        /* usrMobile           */ '+989122983610',
        /* usrMobileApprovedAt */ new Expression('NOW()'),
        /* usrGender           */ enuGender::Male,
        /* usrFirstName        */ 'Kambiz',
        /* usrLastName         */ 'Zandi',
        /* usrStatus           */ enuUserStatus::Active,
      ],
		]);

    $this->execute(<<<SQL
ALTER TABLE {{%AAA_User}} AUTO_INCREMENT=101;
SQL
		);

	}

  public function safeDown()
  {
    echo "m221015_160300_aaa_init cannot be reverted.\n";

    return false;
  }

}
