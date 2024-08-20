<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\controllers;

use Yii;
use shopack\aaa\frontend\common\auth\BaseController;

class DefaultController extends BaseController
{
	public function actionIndex()
	{
		return '';
	}

	public function actionTest()
	{
		return $this->render('test');
	}

	public function actionTestMultiRequestPerOneSession($id)
	{
		return $id;
	}

}
