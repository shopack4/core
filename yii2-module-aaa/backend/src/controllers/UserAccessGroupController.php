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
use shopack\aaa\backend\models\UserAccessGroupModel;

class UserAccessGroupController extends BaseCrudController
{
	public function behaviors()
	{
		$behaviors = parent::behaviors();

		// $behaviors[static::BEHAVIOR_AUTHENTICATOR]['except'] = [
		// 	'index',
		// 	'view',
		// ];

		return $behaviors;
	}

	public $modelClass = \shopack\aaa\backend\models\UserAccessGroupModel::class;

	public function permissions()
	{
		return [
			'index'  => ['aaa/user-access-group/crud' => '0100'],
			'view'   => ['aaa/user-access-group/crud' => '0100'],
			'create' => ['aaa/user-access-group/crud' => '1000'],
			'update' => ['aaa/user-access-group/crud' => '0010'],
			'delete' => ['aaa/user-access-group/crud' => '0001'],
		];
	}

	public function queryAugmentaters()
	{
		return [
			'index' => function($query) {
				$query
					->joinWith('user')
					->joinWith('user.accessGroup')
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
			'view' => function($query) {
				$query
					->joinWith('user')
					->joinWith('user.accessGroup')
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
		];
	}

}
