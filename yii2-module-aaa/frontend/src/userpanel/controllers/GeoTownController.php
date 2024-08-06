<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\userpanel\controllers;

use shopack\aaa\frontend\common\auth\BaseController;
use shopack\aaa\frontend\common\models\GeoTownModel;
use Yii;

class GeoTownController extends BaseController
{
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
		$query = GeoTownModel::find()
			->noLimit()
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
