<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\data\ActiveDataProvider;
use shopack\base\common\helpers\ExceptionHelper;
use shopack\base\backend\controller\BaseCrudController;
use shopack\base\backend\helpers\PrivHelper;
use shopack\aaa\backend\models\GeoStateModel;

class GeoStateController extends BaseCrudController
{
	public function behaviors()
	{
		$behaviors = parent::behaviors();

		$behaviors[static::BEHAVIOR_AUTHENTICATOR]['except'] = [
			'index',
			'view',
		];

		return $behaviors;
	}

	public $modelClass = \shopack\aaa\backend\models\GeoStateModel::class;

	public function permissions()
	{
		return [
			// 'index'  => ['aaa/geo-state/crud' => '0100'],
			// 'view'   => ['aaa/geo-state/crud' => '0100'],
			'create' => ['aaa/geo-state/crud' => '1000'],
			'update' => ['aaa/geo-state/crud' => '0010'],
			'delete' => ['aaa/geo-state/crud' => '0001'],
		];
	}

	public function queryAugmentaters()
	{
		return [
			'index' => function($query) {
				$query
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
			'view' => function($query) {
				$query
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
		];
	}

}
