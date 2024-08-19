<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\console;

use shopack\base\common\base\ApplicationInstanceIDTrait;
use shopack\base\common\base\ApplicationTopModuleTrait;

class Application extends \yii\console\Application
{
	use ApplicationInstanceIDTrait;
	use ApplicationTopModuleTrait;

	public $isConsole = true;
	public $isBackend = false;
	public $isJustForMe = true;

	public function init()
	{
		parent::init();

		//trigger db
		if ($this->has('db')) {
			$this->db->open();
			echo "DB Datetime: " . $this->db->utcNow->format('Y-m-d H:i:s P');
		}
	}

	public function coreCommands()
	{
		$commands = parent::coreCommands();

		$commands = array_replace_recursive($commands, [
			'migrate' => \shopack\base\common\console\controllers\MigrateController::class,
		]);

		return $commands;
	}

}
