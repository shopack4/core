<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\cmn\frontend\adminpanel\controllers;

use Yii;
use yii\web\Response;
use shopack\base\common\helpers\HttpHelper;
use shopack\aaa\frontend\common\auth\BaseCrudController;
use shopack\cmn\frontend\common\models\LanguageModel;
use shopack\cmn\frontend\common\models\LanguageSearchModel;

class LanguageController extends BaseCrudController
{
	public $modelClass = LanguageModel::class;
	public $searchModelClass = LanguageSearchModel::class;

}
