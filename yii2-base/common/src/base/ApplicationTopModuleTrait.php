<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\base;

use Yii;
use shopack\base\common\helpers\Json;

trait ApplicationTopModuleTrait
{
	private $topModule = null;
	public function getTopModule()
	{
		if ($this->topModule == null) {
			$this->topModule = $this->controller->module;

			while ($this->topModule->module != Yii::$app) {
				$this->topModule = $this->topModule->module;
			}
		}

		return $this->topModule;
	}

}
