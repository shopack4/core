<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use yii\db\Expression;
use shopack\base\common\db\Migration;

class m231025_115607_aaa_insert_rawMessage_to_templates extends Migration
{
	public function safeUp()
	{
		$this->batchInsertIgnore('tbl_AAA_MessageTemplate', [
			'mstKey',
			'mstUUID',
			'mstMedia',
			'mstLanguage',
			'mstTitle',
			'mstBody',
			'mstParamsPrefix',
			'mstParamsSuffix',
			'mstIsSystem'
		], [
			['rawMessage', new Expression('UUID()'), 'S', 'fa', NULL, "{{message}}", '{{', '}}', 1],
		]);
	}

	public function safeDown()
	{
		echo "m231025_115607_aaa_insert_rawMessage_to_templates cannot be reverted.\n";
		return false;
	}

}
