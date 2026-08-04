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
	public $isBackend = false;
	public $isJustForMe = true;

	public function init()
	{
		parent::init();

		//trigger db
		if ($this->has('db'))
			$this->db->open();
	}

	public function handleRequest($request)
    {
		if ($this->isBackend) {
			if ($request->headers->has('accept-language'))
				$this->language = $request->headers->get('accept-language', $this->language);
		}

        return parent::handleRequest($request);
    }

}
