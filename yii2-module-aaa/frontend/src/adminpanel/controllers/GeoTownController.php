<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\controllers;

use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\StringHelper;
use shopack\aaa\frontend\common\auth\BaseCrudController;
use shopack\aaa\frontend\common\models\GeoTownModel;
use shopack\aaa\frontend\common\models\GeoTownSearchModel;

class GeoTownController extends BaseCrudController
{
	public $modelClass = GeoTownModel::class;
	public $searchModelClass = GeoTownSearchModel::class;
	public $modalDoneFragment = 'town';

	public function init()
	{
		$this->doneLink = function ($model) {
			return Url::to(['/aaa/geo-city-or-village/view',
				'id' => $model->twnCityID,
				'fragment' => $this->modalDoneFragment,
				'anchor' => StringHelper::convertToJsVarName($model->primaryKeyValue()),
			]);
		};

		parent::init();
	}

  public function actionCreate_afterCreateModel(&$model)
  {
		$model->twnCityID = $_GET['twnCityID'] ?? null;
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
		$query = GeoTownModel::find()
			->andWhere(['twnCityID' => $parentID]);

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
					'id' => $model->twnID,
					'name' => $model->twnName,
				];
			}
		}

		$out['output'] = $list;

		return $this->renderJson($out);
  }

}
