<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\console\controllers;

use Yii;

class MigrateController extends \yii\console\controllers\MigrateController
{
	public $templateFile = '@shopack/base/common/console/views/migration.php';

	// public function beforeAction($action)
	// {
	// 	return parent::beforeAction($action);
	// }

}
