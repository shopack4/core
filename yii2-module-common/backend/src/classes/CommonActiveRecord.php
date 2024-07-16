<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\cmn\backend\classes;

use shopack\base\backend\rest\RestServerActiveRecord;

abstract class CommonActiveRecord extends RestServerActiveRecord
{
	public static function getDb()
	{
		return \shopack\cmn\backend\Module::getInstance()->db;
		// return Yii::$app->controller->module->db;
	}

}
