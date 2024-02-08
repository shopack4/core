<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\web;

use shopack\base\common\base\ApplicationInstanceIDTrait;
use shopack\base\common\base\ApplicationTopModuleTrait;

class Application extends \yii\web\Application
{
	use ApplicationInstanceIDTrait;
	use ApplicationTopModuleTrait;

	public $isConsole = false;
	public $isJustForMe = true;

}
