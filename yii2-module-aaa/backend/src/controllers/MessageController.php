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
use shopack\aaa\backend\models\MessageModel;

class MessageController extends BaseCrudController
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

	public $modelClass = \shopack\aaa\backend\models\MessageModel::class;

	public function permissions()
	{
		$checkOwner = function($model) : bool {
			return (($model != null) && ($model['msgUsrID'] == Yii::$app->user->id));
		};

		return [
			'index'  => [
										'aaa/message/crud' => '0100',
										'filter' => function($query) {
											if (Yii::$app->user->isGuest)
												throw new \yii\web\ForbiddenHttpException("not allowed for guest");
											$query->andWhere(['msgUsrID' => Yii::$app->user->id]);
										},
									],
			'view'   => ['aaa/message/crud' => '0100', 'checker' => $checkOwner],
			'create' => ['aaa/message/crud' => '1000'],
			'update' => ['aaa/message/crud' => '0010', 'checker' => $checkOwner],
			'delete' => ['aaa/message/crud' => '0001', 'checker' => $checkOwner],
		];
	}

	public function queryAugmentaters()
	{
		return [
			'index' => function($query) {
				$query
					->joinWith('user')
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
			'view' => function($query) {
				$query
					->joinWith('user')
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
		];
	}

}
