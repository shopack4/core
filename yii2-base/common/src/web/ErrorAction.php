<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\web;

use shopack\base\common\base\ApplicationInstanceIDTrait;
use shopack\base\common\base\ApplicationTopModuleTrait;

class ErrorAction extends \yii\web\ErrorAction
{
	protected function renderAjaxResponse()
	{
		if (YII_DEBUG)
			return $this->getExceptionName() . ': ' . $this->getExceptionMessage();

		return $this->getExceptionMessage();
	}

}
