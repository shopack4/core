<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\controller;

use Yii;
use yii\db\QueryInterface;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\web\ForbiddenHttpException;
use shopack\base\common\helpers\Json;
use shopack\base\common\security\RsaPublic;
use shopack\base\backend\controller\BaseController;
use shopack\base\backend\auth\JwtHttpBearerAuth;
use shopack\base\common\security\RsaPrivate;

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

	public function queryAllToResponse($query, $checkExposeFilter = true)
	{
		//todo: this is temporary. complete expose model
		if ($checkExposeFilter)
			$query->asArray(false);
		else
			$query->asArray(true);

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

		$models = $dataProvider->getModels();

		$allModels = [];

		if ($checkExposeFilter) {
			if (empty($models) == false) {
				foreach ($models as $k => $model) {
					$allModels[$k] = $this->exposeModel($model, $checkExposeFilter);
				}
			}
		} else {
			$allModels = $models;
		}

		return [
			'data' => $allModels,
			'pagination' => [
				'totalCount' => $totalCount,
			],
		];
  }

	public function queryOneToResponse(QueryInterface $query, $fnCheckPermission = null)
	{
		$model = $query->asArray(false)->one();

		if ($model == null)
			throw new NotFoundHttpException('The requested item does not exist.');

		if ($fnCheckPermission != null)
			$fnCheckPermission($model);

		return $this->exposeModel($model);
  }

  public function exposeModel($model, $checkExposeFilter = true)
  {
		if ($model == null)
			return [];

		return $model->exposeAttributes(false, $checkExposeFilter);
  }

	public function modelToResponse($model)
	{
		if ($model == null)
			throw new NotFoundHttpException('The requested item does not exist.');

		return $model;
	}

	public function getSecureData()
	{
		$allData = Yii::$app->request->getBodyParams();

		$service = $allData['service'];
		if (empty($service))
			throw new UnprocessableEntityHttpException('NOT_PROVIDED:Service');

		$data = $allData['data'];
		if (empty($data))
			throw new UnprocessableEntityHttpException('NOT_PROVIDED:Data');

		$module = Yii::$app->controller->module;

		//public or private?
		if (isset($module->servicesPublicKeys)) {
			$key = $module->servicesPublicKeys[$service];
			$rsaModel = RsaPublic::model($key);
		} else {
			$parentModule = Yii::$app->topModule;
			$key = $parentModule->servicePrivateKey;
			$rsaModel = RsaPrivate::model($key);
		}

		$data = $rsaModel->decrypt($data);
		$data = Json::decode($data);

		if ($service != $data['service']) //todo: change to sanity check
			throw new ForbiddenHttpException('INVALID:Service');

		return $data;
	}

}
