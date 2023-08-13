<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\userpanel\controllers;

use shopack\aaa\frontend\common\auth\BaseController;
use shopack\aaa\frontend\common\models\GeoCityOrVillageModel;

class GeoCityOrVillageController extends BaseController
{
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
