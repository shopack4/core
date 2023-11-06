<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\controllers;

use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\StringHelper;
use shopack\aaa\frontend\common\auth\BaseCrudController;
use shopack\aaa\frontend\common\models\UserAccessGroupModel;
use shopack\aaa\frontend\common\models\UserAccessGroupSearchModel;

class UserAccessGroupController extends BaseCrudController
{
	public $modelClass = UserAccessGroupModel::class;
	public $searchModelClass = UserAccessGroupSearchModel::class;
	public $modalDoneFragment = 'user-access-groups';

	public function init()
	{
		$this->doneLink = function ($model) {
			return Url::to(['/aaa/user/view',
				'id' => $model->usragpUserID,
				'fragment' => $this->modalDoneFragment,
				'anchor' => StringHelper::convertToJsVarName($model->primaryKeyValue()),
			]);
		};

		parent::init();
	}

	public function actionCreate_afterCreateModel(&$model)
  {
		$model->usragpUserID = $_GET['usragpUserID'] ?? null;
  }

}
