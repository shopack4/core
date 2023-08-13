<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\controllers;

use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\StringHelper;
use shopack\aaa\frontend\common\auth\BaseCrudController;
use shopack\aaa\frontend\common\models\GeoStateModel;
use shopack\aaa\frontend\common\models\GeoStateSearchModel;

class GeoStateController extends BaseCrudController
{
	public $modelClass = GeoStateModel::class;
	public $searchModelClass = GeoStateSearchModel::class;
	public $modalDoneFragment = 'state';

	public function init()
	{
		$this->doneLink = function ($model) {
			return Url::to(['/aaa/country/view',
				'id' => $model->sttCountryID,
				'fragment' => $this->modalDoneFragment,
				'anchor' => StringHelper::convertToJsVarName($model->primaryKeyValue()),
			]);
		};

		parent::init();
	}

  public function actionCreate_afterCreateModel(&$model)
  {
		$model->sttCountryID = $_GET['sttCountryID'] ?? null;
		// $model->mbrminsdocStatus = enuInsurerDocStatus::WaitForSurvey;
  }

	public function actionDepdropList($p=null, $sel=null)
  {
    $parentID = (isset($_POST['depdrop_parents']) ? end($_POST['depdrop_parents']) : $p);

		$out = [
			'output' => [],
			'selected' => $sel,
		];

		if (empty($parentID))
			return $this->renderJson($out);

		//count
		$query = GeoStateModel::find()
			->andWhere(['sttCountryID' => $parentID]);

		$out['total_count'] = $count = $query->count();
		if ($count == 0)
			return $this->renderJson($out);

		//items
		// $query->limit($perPage);
		// $query->offset($page * $perPage);
		$models = $query->all();

		$list = [];
		if (empty($models) == false) {
			foreach ($models as $model) {
				$list[] = [
					'id' => $model->sttID,
					'name' => $model->sttName,
				];
			}
		}

		$out['output'] = $list;

		return $this->renderJson($out);
  }

}
