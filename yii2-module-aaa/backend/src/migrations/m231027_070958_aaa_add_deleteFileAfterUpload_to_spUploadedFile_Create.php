<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m231027_070958_aaa_add_deleteFileAfterUpload_to_spUploadedFile_Create extends Migration
{
    public function safeUp()
    {
        $this->execute(<<<SQLSTR
ALTER TABLE `tbl_AAA_UploadFile`
    ADD COLUMN `uflDeleteLocalFileAfterUpload` BIT NULL AFTER `uflLocalFullFileName`;
SQLSTR
        );

        $this->execute("DROP PROCEDURE IF EXISTS spUploadedFile_Create;");
        $this->execute(<<<SQLSTR
CREATE PROCEDURE `spUploadedFile_Create`(
	IN `iPath` VARCHAR(256),
	IN `iOriginalFileName` VARCHAR(256),
	IN `iFullTempPath` VARCHAR(512),
	IN `iSetTempFileNameToMD5` TINYINT,
	IN `iFileSize` BIGINT UNSIGNED,
	IN `iFileType` VARCHAR(64),
	IN `iMimeType` VARCHAR(128),
	IN `iOwnerUserID` BIGINT UNSIGNED,
	IN `iCreatorUserID` BIGINT UNSIGNED,
	IN `iLockedBy` VARCHAR(50),
	IN `iDeleteLocalFileAfterUpload` BIT,
	OUT `oStoredFileName` VARCHAR(256),
	OUT `oTempFileName` VARCHAR(256),
	OUT `oUploadedFileID` BIGINT UNSIGNED,
	OUT `oQueueRowsCount` INT UNSIGNED
)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN
  DECLARE vErr VARCHAR(500);
  DECLARE vUploadedFileCounter BIGINT UNSIGNED;
  DECLARE vExt VARCHAR(500);

  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    GET DIAGNOSTICS CONDITION 1 vErr = MESSAGE_TEXT;

    /****************/
    ROLLBACK;
    /****************/

    INSERT INTO tbl_SYS_ActionLogs
       SET atlBy = iCreatorUserID,
           atlTarget = 'Upload.Error',
           atlInfo = JSON_OBJECT(
            "err",                   vErr,
            "iPath",                 iPath,
            "iOriginalFileName",     iOriginalFileName,
            "iFileSize",             iFileSize,
            "iFileType",             iFileType,
            "iMimeType",             iMimeType,
            "iFullTempPath",         iFullTempPath,
            "iOwnerUserID",          iOwnerUserID,
            "iCreatorUserID",        iCreatorUserID,
            "iLockedBy",             iLockedBy,
            "iDeleteLocalFileAfterUpload", iDeleteLocalFileAfterUpload,
            "UploadedFileCounter",   vUploadedFileCounter,
            "StoredFileName",        oStoredFileName,
            "oTempFileName",         oTempFileName,
            "FileID",                oUploadedFileID,
            "QueuedCount",           oQueueRowsCount
          )
    ;

    RESIGNAL;
  END;

  /****************/
  START TRANSACTION;
  /****************/

  SET vUploadedFileCounter = NULL;

  SELECT MAX(IFNULL(uflCounter, 0))
    INTO vUploadedFileCounter
    FROM tbl_AAA_UploadFile
   WHERE IFNULL(uflPath, '') = IFNULL(iPath, '')
     AND uflOriginalFileName = iOriginalFileName
     AND uflOwnerUserID = iOwnerUserID
  ;

  IF ISNULL(vUploadedFileCounter) THEN
    SET oStoredFileName = iOriginalFileName;
  ELSE
    SET vUploadedFileCounter = vUploadedFileCounter + 1;
    SET oStoredFileName = fnApplyCounterToFileName(iOriginalFileName, vUploadedFileCounter);
  END IF;

  IF iSetTempFileNameToMD5 = 1 THEN
    SET oTempFileName = MD5(CONCAT(RAND(), UUID())); -- MD5(oStoredFileName);

    SELECT SUBSTRING_INDEX(oStoredFileName, '.', -1) INTO vExt;

    IF (LOCATE('.', oStoredFileName) != 0) AND (LENGTH(vExt)+1 != LENGTH(oStoredFileName)) THEN
      SET oTempFileName = CONCAT(oTempFileName, '.', vExt);
    END IF;
  ELSE
    SET oTempFileName = oStoredFileName;
  END IF;

  INSERT INTO tbl_AAA_UploadFile
     SET uflUUID = UUID(),
		     uflPath = iPath,
         uflOriginalFileName = iOriginalFileName,
         uflCounter = vUploadedFileCounter,
         uflStoredFileName = oStoredFileName,
         uflSize = iFileSize,
         uflFileType = iFileType,
         uflMimeType = iMimeType,
         uflLocalFullFileName = CONCAT(iFullTempPath, '/', oTempFileName),
         uflDeleteLocalFileAfterUpload = iDeleteLocalFileAfterUpload,
         uflOwnerUserID = iOwnerUserID,
         uflCreatedBy = iCreatorUserID
  ;
  SET oUploadedFileID = LAST_INSERT_ID();

  INSERT INTO tbl_AAA_UploadQueue(
         uquUUID
       , uquFileID
       , uquGatewayID
       , uquLockedAt
       , uquLockedBy
       , uquCreatedBy
         )
  SELECT UUID()
       , oUploadedFileID
       , gtwID
       , IF(iLockedBy IS NULL OR iLockedBy='', NULL, NOW())
       , IF(iLockedBy IS NULL OR iLockedBy='', NULL, iLockedBy)
       , iCreatorUserID
    FROM tbl_AAA_Gateway
   WHERE gtwPluginType = 'objectstorage'
     AND gtwStatus = 'A'
     AND (JSON_EXTRACT(gtwRestrictions, '$.AllowedFileTypes') IS NULL
      OR LOWER(JSON_EXTRACT(gtwRestrictions, '$.AllowedFileTypes')) LIKE CONCAT('%', iFileType, '%')
         )
     AND (JSON_EXTRACT(gtwRestrictions, '$.AllowedMimeTypes') IS NULL
      OR LOWER(JSON_EXTRACT(gtwRestrictions, '$.AllowedMimeTypes')) LIKE CONCAT('%', iMimeType, '%')
         )
     AND (JSON_EXTRACT(gtwRestrictions, '$.AllowedMinFileSize') IS NULL
      OR JSON_UNQUOTE(JSON_EXTRACT(gtwRestrictions, '$.AllowedMinFileSize')) <= iFileSize
         )
     AND (JSON_EXTRACT(gtwRestrictions, '$.AllowedMaxFileSize') IS NULL
      OR JSON_UNQUOTE(JSON_EXTRACT(gtwRestrictions, '$.AllowedMaxFileSize')) >= iFileSize
         )
     AND (JSON_EXTRACT(gtwRestrictions, '$.MaxFilesCount') IS NULL
      OR JSON_UNQUOTE(JSON_EXTRACT(gtwRestrictions, '$.MaxFilesCount'))
       > (IFNULL(JSON_EXTRACT(gtwUsages, '$.CreatedFilesCount'), 0)
       - IFNULL(JSON_EXTRACT(gtwUsages, '$.DeletedFilesCount'), 0)
         )
         )
     AND (JSON_EXTRACT(gtwRestrictions, '$.MaxFilesSize') IS NULL
      OR JSON_UNQUOTE(JSON_EXTRACT(gtwRestrictions, '$.MaxFilesSize'))
      >= (IFNULL(JSON_EXTRACT(gtwUsages, '$.CreatedFilesSize'), 0)
       - IFNULL(JSON_EXTRACT(gtwUsages, '$.DeletedFilesSize'), 0)
       + iFileSize
         )
         )
  ;
  SET oQueueRowsCount = ROW_COUNT();

  /* this is for next version
  IF (oQueueRowsCount > 0) THEN
    UPDATE tbl_AAA_UploadFile
       SET uflStatus = 'Q'
     WHERE uflID = oUploadedFileID
    ;
  END IF;
  */

  /****************/
  COMMIT;
  /****************/
END
SQLSTR
        );
    }

    public function safeDown()
    {
        echo "m231027_070958_aaa_add_deleteFileAfterUpload_to_spUploadedFile_Create cannot be reverted.\n";
        return false;
    }

}
