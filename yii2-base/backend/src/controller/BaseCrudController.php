<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\controller;

use Yii;
use yii\data\ActiveDataProvider;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;
use shopack\base\backend\controller\BaseController;
use shopack\base\backend\auth\JwtHttpBearerAuth;

class BaseCrudController extends BaseRestController
{
	public $modelClass;

	public function init()
	{
		parent::init();

		if ($this->modelClass === null)
			throw new InvalidConfigException('The "modelClass" property must be set.');
	}

	protected function findModel($id)
	{
		$modelClass = $this->modelClass;
		if (($model = $modelClass::findOne($id)) === null)
			throw new NotFoundHttpException('The requested item not exist.');

		return $model;
	}

}
