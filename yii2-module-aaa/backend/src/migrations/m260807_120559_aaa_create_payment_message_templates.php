<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use yii\db\Expression;
use shopack\base\common\db\Migration;

class m260807_120559_aaa_create_payment_message_templates extends Migration
{
    public function safeUp()
    {
        $this->batchInsertIgnore('tbl_AAA_MessageTemplate', [
            'mstUUID',
            'mstKey',
            'mstMedia',
            'mstLanguage',
            'mstStatus',
            'mstParamsPrefix',
            'mstParamsSuffix',
            'mstParams',
            'mstIsSystem',
            'mstName',
            'mstBody',
        ], [

            [new Expression('UUID()'), 'offlinePaymentStatusChangedTo_WaitForApprove', 'S', 'fa', 'D', '{{', '}}', 'user,amount,status', 1, 'پرداخت آفلاین: جدید',       "کاربر محترم {{user}}\n" . "پرداخت آفلاین شما به مبلغ {{amount}} " . "جهت بررسی ثبت شد"],
            [new Expression('UUID()'), 'offlinePaymentStatusChangedTo_Approved',       'S', 'fa', 'D', '{{', '}}', 'user,amount,status', 1, 'پرداخت آفلاین: تایید شده',  "کاربر محترم {{user}}\n" . "پرداخت آفلاین شما به مبلغ {{amount}} " . "تایید شد"],
            [new Expression('UUID()'), 'offlinePaymentStatusChangedTo_Rejected',       'S', 'fa', 'D', '{{', '}}', 'user,amount,status', 1, 'پرداخت آفلاین: رد شده',     "کاربر محترم {{user}}\n" . "پرداخت آفلاین شما به مبلغ {{amount}} " . "رد شد"],
            // [new Expression('UUID()'), 'offlinePaymentStatusChangedTo_Removed',        'S', 'fa', 'D', '{{', '}}', 'user,amount,status', 1, 'پرداخت آفلاین: حذف',        "کاربر محترم {{user}}\n" . "پرداخت آفلاین شما به مبلغ {{amount}} " . "حذف شد"],
            [new Expression('UUID()'), 'onlinePaymentStatusChangedTo_New',             'S', 'fa', 'D', '{{', '}}', 'user,amount,status', 1, 'پرداخت آنلاین: جدید',       "کاربر محترم {{user}}\n" . "پرداخت آنلاین شما به مبلغ {{amount}} " . "ایجاد شد"],
            // [new Expression('UUID()'), 'onlinePaymentStatusChangedTo_Pending',         'S', 'fa', 'D', '{{', '}}', 'user,amount,status', 1, 'پرداخت آنلاین: منتظر',      "کاربر محترم {{user}}\n" . "پرداخت آنلاین شما به مبلغ {{amount}} " . "منتظر پرداخت است"],
            [new Expression('UUID()'), 'onlinePaymentStatusChangedTo_Paid',            'S', 'fa', 'D', '{{', '}}', 'user,amount,status', 1, 'پرداخت آنلاین: پرداخت شده', "کاربر محترم {{user}}\n" . "پرداخت آنلاین شما به مبلغ {{amount}} " . "تایید شد"],
            // [new Expression('UUID()'), 'onlinePaymentStatusChangedTo_Error',           'S', 'fa', 'D', '{{', '}}', 'user,amount,status', 1, 'پرداخت آنلاین: خطا',        "کاربر محترم {{user}}\n" . "پرداخت آنلاین شما به مبلغ {{amount}} " . "به دلیل بروز خطا متوقف شد"],
            // [new Expression('UUID()'), 'onlinePaymentStatusChangedTo_Removed',         'S', 'fa', 'D', '{{', '}}', 'user,amount,status', 1, 'پرداخت آنلاین: حذف',        "کاربر محترم {{user}}\n" . "پرداخت آنلاین شما به مبلغ {{amount}} " . "حذف شد"],
        ]);
    }

    public function safeDown()
    {
        echo "m260807_120559_aaa_create_payment_message_templates cannot be reverted.\n";
        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
    }

    public function down()
    {
        echo "m260807_120559_aaa_create_payment_message_templates cannot be reverted.\n";
        return false;
    }
    */
}
