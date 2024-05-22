<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\controller;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use shopack\base\backend\controller\BaseController;
use shopack\base\backend\auth\JwtHttpBearerAuth;

class BaseRestController extends BaseController
{
	const BEHAVIOR_AUTHENTICATOR = 'authenticator';

	public function behaviors()
	{
		$behaviors = parent::behaviors();

		$behaviors[static::BEHAVIOR_AUTHENTICATOR] = [
			'class' => JwtHttpBearerAuth::class,
		];

		return $behaviors;
	}

	public function queryAllToResponse($query)
	{
		$query->asArray();

		$noLimit = false;
		if (array_key_exists('per-page', $_GET)) {
			if ($_GET['per-page'] == 0)
				$noLimit = true;
		}

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
		]);

		if ($noLimit)
			$dataProvider->setPagination(false);

		$totalCount = $dataProvider->getTotalCount();
		Yii::$app->response->headers->add('X-Pagination-Total-Count', $totalCount);

		if (Yii::$app->request->getMethod() == 'HEAD') {
			// $totalCount = $query->count();
			return null;
		}

		$allModels = $dataProvider->getModels();

		return [
			'data' => $allModels,
			'pagination' => [
				'totalCount' => $totalCount,
			],
		];
  }

  public function modelToResponse($model)
  {
		if ($model == null)
			throw new NotFoundHttpException('The requested item does not exist.');

		return $model;
  }

}
