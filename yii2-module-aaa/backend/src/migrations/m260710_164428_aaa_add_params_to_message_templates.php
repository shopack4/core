<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m260710_164428_aaa_add_params_to_message_templates extends Migration
{
    public function safeUp()
    {
        $this->execute(
            <<<SQL
ALTER TABLE `tbl_AAA_MessageTemplate`
	ADD COLUMN `mstName` VARCHAR(256) NULL AFTER `mstUUID`,
    ADD COLUMN `mstParams` VARCHAR(1024) NULL AFTER `mstParamsSuffix`;
SQL
        );

        $this->execute("UPDATE tbl_AAA_MessageTemplate SET mstName='تایید ایمیل',              mstParams = 'panel-address,email,code' WHERE mstKey = 'emailApproval';");
        $this->execute("UPDATE tbl_AAA_MessageTemplate SET mstName='تایید ایمیل برای ورود',    mstParams = 'code,link'                WHERE mstKey = 'emailApprovalForLogin';");
        $this->execute("UPDATE tbl_AAA_MessageTemplate SET mstName='ایمیل تایید شد'                                                   WHERE mstKey = 'emailApproved';");
        $this->execute("UPDATE tbl_AAA_MessageTemplate SET mstName='تایید موبایل',             mstParams = 'code'                     WHERE mstKey = 'mobileApproval';");
        $this->execute("UPDATE tbl_AAA_MessageTemplate SET mstName='تایید موبایل برای ورود',   mstParams = 'code'                     WHERE mstKey = 'mobileApprovalForLogin';");
        $this->execute("UPDATE tbl_AAA_MessageTemplate SET mstName='موبایل تایید شد'                                                  WHERE mstKey = 'mobileApproved';");
        $this->execute("UPDATE tbl_AAA_MessageTemplate SET mstName='فراموشی رمز با ایمیل',     mstParams = 'panel-address,email,code' WHERE mstKey = 'forgotPassByEmail';");
        $this->execute("UPDATE tbl_AAA_MessageTemplate SET mstName='رمز توسط ایمیل تغییر کرد'                                         WHERE mstKey = 'passChangedByEmail';");
        $this->execute("UPDATE tbl_AAA_MessageTemplate SET mstName='فراموشی رمز با موبایل',    mstParams = 'code,mobile'              WHERE mstKey = 'forgotPassByMobile';");
        $this->execute("UPDATE tbl_AAA_MessageTemplate SET mstName='رمز توسط موبایل تغییر کرد'                                        WHERE mstKey = 'passChangedByMobile';");
        $this->execute("UPDATE tbl_AAA_MessageTemplate SET mstName='تبریک تولد',               mstParams = 'member'                   WHERE mstKey = 'happyBirthday';");
        $this->execute("UPDATE tbl_AAA_MessageTemplate SET mstName='ارسال پیغام',              mstParams = 'message'                  WHERE mstKey = 'rawMessage';");
    }

    public function safeDown()
    {
        echo "m260710_164428_aaa_add_params_to_message_templates cannot be reverted.\n";
        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
    }

    public function down()
    {
        echo "m260710_164428_aaa_add_params_to_message_templates cannot be reverted.\n";
        return false;
    }
    */
}
