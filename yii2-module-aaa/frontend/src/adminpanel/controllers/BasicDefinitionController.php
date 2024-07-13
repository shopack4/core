<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\controllers;

use shopack\aaa\frontend\common\auth\BaseCrudController;
use shopack\aaa\common\enums\enuBasicDefinitionStatus;
use shopack\aaa\frontend\common\models\BasicDefinitionModel;
use shopack\aaa\frontend\common\models\BasicDefinitionSearchModel;

class BasicDefinitionController extends BaseCrudController
{
	public $modelClass = BasicDefinitionModel::class;
	public $searchModelClass = BasicDefinitionSearchModel::class;

	public function actionCreate_afterCreateModel(&$model)
  {
		$model->bdfStatus = enuBasicDefinitionStatus::Active;
  }

}
