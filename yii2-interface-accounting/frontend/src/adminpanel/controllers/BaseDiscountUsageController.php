<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\interface\accounting\frontend\adminpanel\controllers;

use shopack\aaa\frontend\common\auth\BaseCrudController;

class BaseDiscountUsageController extends BaseCrudController
{
	public function init()
  {
    parent::init();

    $viewPath = dirname(dirname(__FILE__))
      . DIRECTORY_SEPARATOR
      . 'views'
      . DIRECTORY_SEPARATOR
      . $this->id;

    $this->setViewPath($viewPath);
  }

  //todo: disable create,update,delete

}
