<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m230902_120820_aaa_add_somefields_to_user extends Migration
{
    public function safeUp()
    {
        $this->execute(<<<SQLSTR
ALTER TABLE `tbl_AAA_User`
	ADD COLUMN `usrEducationLevel` CHAR(1) NULL DEFAULT NULL COMMENT 'A:AAA' AFTER `usrImageFileID`,
	ADD COLUMN `usrFieldOfStudy` VARCHAR(128) NULL DEFAULT NULL AFTER `usrEducationLevel`,
	ADD COLUMN `usrYearOfGraduation` SMALLINT NULL DEFAULT NULL AFTER `usrFieldOfStudy`,
	ADD COLUMN `usrEducationPlace` VARCHAR(128) NULL DEFAULT NULL AFTER `usrYearOfGraduation`,
	ADD COLUMN `usrMaritalStatus` CHAR(1) NULL DEFAULT NULL COMMENT 'A:AAA' AFTER `usrEducationPlace`,
	ADD COLUMN `usrMilitaryStatus` CHAR(1) NULL DEFAULT NULL COMMENT 'A:AAA' AFTER `usrMaritalStatus`;
SQLSTR
        );

        $this->execute("DROP TRIGGER IF EXISTS trg_updatelog_tbl_AAA_User;");
        $this->execute(<<<SQLSTR
CREATE TRIGGER trg_updatelog_tbl_AAA_User AFTER UPDATE ON tbl_AAA_User FOR EACH ROW BEGIN
  DECLARE Changes JSON DEFAULT JSON_OBJECT();

  IF ISNULL(OLD.usrUUID) != ISNULL(NEW.usrUUID) OR OLD.usrUUID != NEW.usrUUID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrUUID", IF(ISNULL(OLD.usrUUID), NULL, OLD.usrUUID))); END IF;
  IF ISNULL(OLD.usrGender) != ISNULL(NEW.usrGender) OR OLD.usrGender != NEW.usrGender THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrGender", IF(ISNULL(OLD.usrGender), NULL, OLD.usrGender))); END IF;
  IF ISNULL(OLD.usrFirstName) != ISNULL(NEW.usrFirstName) OR OLD.usrFirstName != NEW.usrFirstName THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrFirstName", IF(ISNULL(OLD.usrFirstName), NULL, OLD.usrFirstName))); END IF;
  IF ISNULL(OLD.usrFirstName_en) != ISNULL(NEW.usrFirstName_en) OR OLD.usrFirstName_en != NEW.usrFirstName_en THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrFirstName_en", IF(ISNULL(OLD.usrFirstName_en), NULL, OLD.usrFirstName_en))); END IF;
  IF ISNULL(OLD.usrLastName) != ISNULL(NEW.usrLastName) OR OLD.usrLastName != NEW.usrLastName THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrLastName", IF(ISNULL(OLD.usrLastName), NULL, OLD.usrLastName))); END IF;
  IF ISNULL(OLD.usrLastName_en) != ISNULL(NEW.usrLastName_en) OR OLD.usrLastName_en != NEW.usrLastName_en THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrLastName_en", IF(ISNULL(OLD.usrLastName_en), NULL, OLD.usrLastName_en))); END IF;
  IF ISNULL(OLD.usrFatherName) != ISNULL(NEW.usrFatherName) OR OLD.usrFatherName != NEW.usrFatherName THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrFatherName", IF(ISNULL(OLD.usrFatherName), NULL, OLD.usrFatherName))); END IF;
  IF ISNULL(OLD.usrFatherName_en) != ISNULL(NEW.usrFatherName_en) OR OLD.usrFatherName_en != NEW.usrFatherName_en THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrFatherName_en", IF(ISNULL(OLD.usrFatherName_en), NULL, OLD.usrFatherName_en))); END IF;
  IF ISNULL(OLD.usrEmail) != ISNULL(NEW.usrEmail) OR OLD.usrEmail != NEW.usrEmail THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrEmail", IF(ISNULL(OLD.usrEmail), NULL, OLD.usrEmail))); END IF;
  IF ISNULL(OLD.usrEmailApprovedAt) != ISNULL(NEW.usrEmailApprovedAt) OR OLD.usrEmailApprovedAt != NEW.usrEmailApprovedAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrEmailApprovedAt", IF(ISNULL(OLD.usrEmailApprovedAt), NULL, OLD.usrEmailApprovedAt))); END IF;
  IF ISNULL(OLD.usrMobile) != ISNULL(NEW.usrMobile) OR OLD.usrMobile != NEW.usrMobile THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrMobile", IF(ISNULL(OLD.usrMobile), NULL, OLD.usrMobile))); END IF;
  IF ISNULL(OLD.usrMobileApprovedAt) != ISNULL(NEW.usrMobileApprovedAt) OR OLD.usrMobileApprovedAt != NEW.usrMobileApprovedAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrMobileApprovedAt", IF(ISNULL(OLD.usrMobileApprovedAt), NULL, OLD.usrMobileApprovedAt))); END IF;
  IF ISNULL(OLD.usrSSID) != ISNULL(NEW.usrSSID) OR OLD.usrSSID != NEW.usrSSID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrSSID", IF(ISNULL(OLD.usrSSID), NULL, OLD.usrSSID))); END IF;
  IF ISNULL(OLD.usrBirthCertID) != ISNULL(NEW.usrBirthCertID) OR OLD.usrBirthCertID != NEW.usrBirthCertID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrBirthCertID", IF(ISNULL(OLD.usrBirthCertID), NULL, OLD.usrBirthCertID))); END IF;
  IF ISNULL(OLD.usrRoleID) != ISNULL(NEW.usrRoleID) OR OLD.usrRoleID != NEW.usrRoleID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrRoleID", IF(ISNULL(OLD.usrRoleID), NULL, OLD.usrRoleID))); END IF;
  IF ISNULL(OLD.usrPrivs) != ISNULL(NEW.usrPrivs) OR OLD.usrPrivs != NEW.usrPrivs THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrPrivs", IF(ISNULL(OLD.usrPrivs), NULL, OLD.usrPrivs))); END IF;
  IF ISNULL(OLD.usrPasswordHash) != ISNULL(NEW.usrPasswordHash) OR OLD.usrPasswordHash != NEW.usrPasswordHash THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrPasswordHash", IF(ISNULL(OLD.usrPasswordHash), NULL, OLD.usrPasswordHash))); END IF;
  IF ISNULL(OLD.usrPasswordCreatedAt) != ISNULL(NEW.usrPasswordCreatedAt) OR OLD.usrPasswordCreatedAt != NEW.usrPasswordCreatedAt THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrPasswordCreatedAt", IF(ISNULL(OLD.usrPasswordCreatedAt), NULL, OLD.usrPasswordCreatedAt))); END IF;
  IF ISNULL(OLD.usrMustChangePassword) != ISNULL(NEW.usrMustChangePassword) OR OLD.usrMustChangePassword != NEW.usrMustChangePassword THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrMustChangePassword", IF(ISNULL(OLD.usrMustChangePassword), NULL, OLD.usrMustChangePassword))); END IF;
  IF ISNULL(OLD.usrBirthDate) != ISNULL(NEW.usrBirthDate) OR OLD.usrBirthDate != NEW.usrBirthDate THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrBirthDate", IF(ISNULL(OLD.usrBirthDate), NULL, OLD.usrBirthDate))); END IF;
  IF ISNULL(OLD.usrBirthCityID) != ISNULL(NEW.usrBirthCityID) OR OLD.usrBirthCityID != NEW.usrBirthCityID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrBirthCityID", IF(ISNULL(OLD.usrBirthCityID), NULL, OLD.usrBirthCityID))); END IF;
  IF ISNULL(OLD.usrCountryID) != ISNULL(NEW.usrCountryID) OR OLD.usrCountryID != NEW.usrCountryID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrCountryID", IF(ISNULL(OLD.usrCountryID), NULL, OLD.usrCountryID))); END IF;
  IF ISNULL(OLD.usrStateID) != ISNULL(NEW.usrStateID) OR OLD.usrStateID != NEW.usrStateID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrStateID", IF(ISNULL(OLD.usrStateID), NULL, OLD.usrStateID))); END IF;
  IF ISNULL(OLD.usrCityOrVillageID) != ISNULL(NEW.usrCityOrVillageID) OR OLD.usrCityOrVillageID != NEW.usrCityOrVillageID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrCityOrVillageID", IF(ISNULL(OLD.usrCityOrVillageID), NULL, OLD.usrCityOrVillageID))); END IF;
  IF ISNULL(OLD.usrTownID) != ISNULL(NEW.usrTownID) OR OLD.usrTownID != NEW.usrTownID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrTownID", IF(ISNULL(OLD.usrTownID), NULL, OLD.usrTownID))); END IF;
  IF ISNULL(OLD.usrHomeAddress) != ISNULL(NEW.usrHomeAddress) OR OLD.usrHomeAddress != NEW.usrHomeAddress THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrHomeAddress", IF(ISNULL(OLD.usrHomeAddress), NULL, OLD.usrHomeAddress))); END IF;
  IF ISNULL(OLD.usrZipCode) != ISNULL(NEW.usrZipCode) OR OLD.usrZipCode != NEW.usrZipCode THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrZipCode", IF(ISNULL(OLD.usrZipCode), NULL, OLD.usrZipCode))); END IF;
  IF ISNULL(OLD.usrPhones) != ISNULL(NEW.usrPhones) OR OLD.usrPhones != NEW.usrPhones THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrPhones", IF(ISNULL(OLD.usrPhones), NULL, OLD.usrPhones))); END IF;
  IF ISNULL(OLD.usrWorkAddress) != ISNULL(NEW.usrWorkAddress) OR OLD.usrWorkAddress != NEW.usrWorkAddress THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrWorkAddress", IF(ISNULL(OLD.usrWorkAddress), NULL, OLD.usrWorkAddress))); END IF;
  IF ISNULL(OLD.usrWorkPhones) != ISNULL(NEW.usrWorkPhones) OR OLD.usrWorkPhones != NEW.usrWorkPhones THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrWorkPhones", IF(ISNULL(OLD.usrWorkPhones), NULL, OLD.usrWorkPhones))); END IF;
  IF ISNULL(OLD.usrWebsite) != ISNULL(NEW.usrWebsite) OR OLD.usrWebsite != NEW.usrWebsite THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrWebsite", IF(ISNULL(OLD.usrWebsite), NULL, OLD.usrWebsite))); END IF;
  IF ISNULL(OLD.usrImageFileID) != ISNULL(NEW.usrImageFileID) OR OLD.usrImageFileID != NEW.usrImageFileID THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrImageFileID", IF(ISNULL(OLD.usrImageFileID), NULL, OLD.usrImageFileID))); END IF;
  IF ISNULL(OLD.usrEducationLevel) != ISNULL(NEW.usrEducationLevel) OR OLD.usrEducationLevel != NEW.usrEducationLevel THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrEducationLevel", IF(ISNULL(OLD.usrEducationLevel), NULL, OLD.usrEducationLevel))); END IF;
  IF ISNULL(OLD.usrFieldOfStudy) != ISNULL(NEW.usrFieldOfStudy) OR OLD.usrFieldOfStudy != NEW.usrFieldOfStudy THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrFieldOfStudy", IF(ISNULL(OLD.usrFieldOfStudy), NULL, OLD.usrFieldOfStudy))); END IF;
  IF ISNULL(OLD.usrYearOfGraduation) != ISNULL(NEW.usrYearOfGraduation) OR OLD.usrYearOfGraduation != NEW.usrYearOfGraduation THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrYearOfGraduation", IF(ISNULL(OLD.usrYearOfGraduation), NULL, OLD.usrYearOfGraduation))); END IF;
  IF ISNULL(OLD.usrEducationPlace) != ISNULL(NEW.usrEducationPlace) OR OLD.usrEducationPlace != NEW.usrEducationPlace THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrEducationPlace", IF(ISNULL(OLD.usrEducationPlace), NULL, OLD.usrEducationPlace))); END IF;
  IF ISNULL(OLD.usrMaritalStatus) != ISNULL(NEW.usrMaritalStatus) OR OLD.usrMaritalStatus != NEW.usrMaritalStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrMaritalStatus", IF(ISNULL(OLD.usrMaritalStatus), NULL, OLD.usrMaritalStatus))); END IF;
  IF ISNULL(OLD.usrMilitaryStatus) != ISNULL(NEW.usrMilitaryStatus) OR OLD.usrMilitaryStatus != NEW.usrMilitaryStatus THEN SET Changes = JSON_MERGE_PRESERVE(Changes, JSON_OBJECT("usrMilitaryStatus", IF(ISNULL(OLD.usrMilitaryStatus), NULL, OLD.usrMilitaryStatus))); END IF;
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
END
SQLSTR
        );

    }

    public function safeDown()
    {
        echo "m230902_120820_aaa_add_somefields_to_user cannot be reverted.\n";
        return false;
    }

}
