<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\controllers;

use Yii;
use yii\web\Response;
use shopack\base\common\helpers\HttpHelper;
use shopack\aaa\frontend\common\auth\BaseCrudController;
use shopack\aaa\frontend\common\models\AccessGroupModel;
use shopack\aaa\frontend\common\models\AccessGroupSearchModel;

class AccessGroupController extends BaseCrudController
{
	public $modelClass = AccessGroupModel::class;
	public $searchModelClass = AccessGroupSearchModel::class;

}
