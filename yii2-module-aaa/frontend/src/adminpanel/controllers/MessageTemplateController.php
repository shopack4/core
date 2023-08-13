<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\controllers;

use Yii;
use shopack\aaa\frontend\common\auth\BaseCrudController;
use shopack\aaa\frontend\common\models\MessageTemplateModel;
use shopack\aaa\frontend\common\models\MessageTemplateSearchModel;

class MessageTemplateController extends BaseCrudController
{
	public $modelClass = MessageTemplateModel::class;
	public $searchModelClass = MessageTemplateSearchModel::class;

}
