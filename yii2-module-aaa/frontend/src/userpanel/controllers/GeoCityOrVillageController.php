<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\userpanel\controllers;

use Yii;
use yii\web\Response;
use shopack\aaa\frontend\common\auth\BaseController;
use shopack\aaa\frontend\common\models\GeoCityOrVillageModel;

class GeoCityOrVillageController extends BaseController
{
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
    $bodyParams = Yii::$app->request->getBodyParams();

    $parentID = (isset($bodyParams['depdrop_parents']) ? end($bodyParams['depdrop_parents']) : $p);

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
