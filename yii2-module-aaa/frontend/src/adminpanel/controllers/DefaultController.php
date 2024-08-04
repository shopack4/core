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

	//http://adminpanel.crm.iranhmusic.dom:81/aaa/default/test
	public function actionTest()
	{
		return $this->render('test');
	}

	//http://adminpanel.crm.iranhmusic.dom:81/aaa/default/test-multi-request-per-one-session
	public function actionTestMultiRequestPerOneSession($id)
	{
		return $id;
	}

}
