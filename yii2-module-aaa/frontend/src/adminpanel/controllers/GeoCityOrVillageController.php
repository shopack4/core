<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\controllers;

use Yii;
use yii\web\Response;
use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\StringHelper;
use shopack\aaa\frontend\common\auth\BaseCrudController;
use shopack\aaa\frontend\common\models\GeoCityOrVillageModel;
use shopack\aaa\frontend\common\models\GeoCityOrVillageSearchModel;

class GeoCityOrVillageController extends BaseCrudController
{
	public $modelClass = GeoCityOrVillageModel::class;
	public $searchModelClass = GeoCityOrVillageSearchModel::class;
	public $modalDoneFragment = 'city-or-village';

	public function init()
	{
		$this->doneLink = function ($model) {
			return Url::to(['/aaa/state/view',
				'id' => $model->ctvStateID,
				'fragment' => $this->modalDoneFragment,
				'anchor' => StringHelper::convertToJsVarName($model->primaryKeyValue()),
			]);
		};

		parent::init();
	}

  public function actionCreate_afterCreateModel(&$model)
  {
		$model->ctvStateID = $_GET['ctvStateID'] ?? null;
		// $model->mbrminsdocStatus = enuInsurerDocStatus::WaitForSurvey;
  }

	public function actionSelect2List(
    $q=null,
    // $id=null,
    $page=0,
    $perPage=20
  ) {
    Yii::$app->response->format = Response::FORMAT_JSON;

    $out['total_count'] = 0;
		$out['items'] = [['id' => '', 'title' => '']];

		if (empty($q))
			return $this->renderJson($out);

    //count
    $query = GeoCityOrVillageModel::find()
      ->addUrlParameter('q', $q);

    $out['total_count'] = $count = $query->count();
    if ($count == 0)
      return $this->renderJson($out);

    //items
    $query->limit($perPage);
    $query->offset($page * $perPage);
    $models = $query->all();

		$list = [];
    if (empty($models) == false) {
			foreach ($models as $model) {
        $list[] = [
          'id' => $model->ctvID,
          'title' => $model->state->sttName . ' - ' . $model->ctvName,
        ];
			}
    }

    $out['items'] = $list;

    return $this->renderJson($out);
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
		$query = GeoCityOrVillageModel::find()
			->noLimit()
			->andWhere(['ctvStateID' => $parentID]);

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
					'id' => $model->ctvID,
					'name' => $model->ctvName,
				];
			}
		}

		$out['output'] = $list;

		return $this->renderJson($out);
  }

}
