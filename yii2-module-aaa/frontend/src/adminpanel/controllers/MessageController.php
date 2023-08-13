<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\controllers;

use Yii;
use shopack\aaa\frontend\common\auth\BaseCrudController;
use shopack\aaa\frontend\common\models\MessageModel;
use shopack\aaa\frontend\common\models\MessageSearchModel;

class MessageController extends BaseCrudController
{
	public $modelClass = MessageModel::class;
	public $searchModelClass = MessageSearchModel::class;

}
