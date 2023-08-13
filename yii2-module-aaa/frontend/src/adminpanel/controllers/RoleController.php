<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\controllers;

use Yii;
use yii\web\Response;
use shopack\aaa\frontend\common\auth\BaseCrudController;
use shopack\base\common\helpers\HttpHelper;
use shopack\aaa\frontend\common\models\RoleModel;
use shopack\aaa\frontend\common\models\RoleSearchModel;

class RoleController extends BaseCrudController
{
	public $modelClass = RoleModel::class;
	public $searchModelClass = RoleSearchModel::class;

}
