<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use shopack\base\common\helpers\ExceptionHelper;
use shopack\base\backend\controller\BaseCrudController;
use shopack\base\backend\helpers\PrivHelper;
use shopack\aaa\backend\models\Active2FAForm;
use shopack\aaa\backend\models\EmailChangeForm;
use shopack\aaa\backend\models\MobileChangeForm;
use shopack\aaa\backend\models\PasswordResetForm;
use shopack\aaa\backend\models\UserSendMessageForm;
use shopack\aaa\backend\models\UpdateImageForm;
use shopack\aaa\backend\models\UserModel;

class UserController extends BaseCrudController
{
	public $modelClass = UserModel::class;

	public function permissions()
	{
		$checkOwner = function($model) : bool {
			return (($model != null) && ($model['usrID'] == Yii::$app->user->id));
		};

		return [
			'index'  => [
				'aaa/user/crud' => '0100',
				'filter' => function($query) {
					Yii::$app->user->assertIsNotGuest();
					$query->andWhere(['usrID' => Yii::$app->user->id]);
				},
			],
			'view'   => ['aaa/user/crud' => '0100', 'checker' => $checkOwner],
			'create' => ['aaa/user/crud' => '1000'],
			'update' => ['aaa/user/crud' => '0010', 'checker' => $checkOwner],
			'delete' => ['aaa/user/crud' => '0001', 'checker' => $checkOwner],
			'undelete' => ['aaa/user/undelete'],
		];
	}

	public function queryAugmentaters()
	{
		return [
			'index' => function($query) {
				$query
					->joinWith('role')
					->joinWith('country')
					->joinWith('state')
					->joinWith('cityOrVillage')
					->joinWith('town')
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
			'view' => function($query) {
				$query
					->joinWith('role')
					->joinWith('country')
					->joinWith('state')
					->joinWith('cityOrVillage')
					->joinWith('town')
					->joinWith('birthCityOrVillage')
					->joinWith('imageFile')
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
		];
	}

	public function fillGlobalSearchFromRequest(\yii\db\ActiveQuery $query, $q)
	{
		if (empty($q) || ($q == '***'))
			return;

		$query->andWhere([
			'OR',
			['LIKE', 'usrFirstName', $q],
			['LIKE', 'usrFirstName_en', $q],
			['LIKE', 'usrLastName', $q],
			['LIKE', 'usrLastName_en', $q],
			['LIKE', 'usrEmail', $q],
			['LIKE', 'usrMobile', $q],
			['LIKE', 'usrSSID', $q],
		]);
	}

	public function actionWhoAmI()
	{
		return [
			Yii::$app->user->identity,
			Yii::$app->user->accessToken->claims()->all(),
			Yii::$app->user->accessToken->toString(),
		];
	}

	public function actionEmailChange()
	{
		$model = new EmailChangeForm();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		try {
			$result = $model->process();

			if ($result == false)
				throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

			return $result;

		} catch(\Exception $exp) {
			$msg = ExceptionHelper::CheckDuplicate($exp, $model);
			throw new UnprocessableEntityHttpException($msg);
		}
	}

	public function actionMobileChange()
	{
		$model = new MobileChangeForm();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		try {
			$result = $model->process();

			if ($result == false)
				throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

			return $result;

		} catch(\Exception $exp) {
			$msg = ExceptionHelper::CheckDuplicate($exp, $model);
			throw new UnprocessableEntityHttpException($msg);
		}
	}

	public function actionUpdateImage($id)
	{
		if ((Yii::$app->user->id != $id)
			&& (PrivHelper::hasPriv('aaa/user/crud', '0010') == false)
		) {
			throw new ForbiddenHttpException('access denied');
		}

		$model = new UpdateImageForm();
		$model->userID = $id;

		// if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
		// 	throw new NotFoundHttpException("parameters not provided");

		try {
			$result = $model->process();

			if ($result == false)
				throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

			return $result;

		} catch(\Exception $exp) {
			$msg = ExceptionHelper::CheckDuplicate($exp, $model);
			throw new UnprocessableEntityHttpException($msg);
		}
	}

	public function actionPasswordReset($id)
	{
		PrivHelper::checkPriv('aaa/user/password-reset');

		$model = new PasswordResetForm();
		$model->userID = $id;

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		if ($model->save() == false)
			throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

		return [
			'result' => true,
		];
	}

	public function actionSendMessage()
	{
		PrivHelper::checkPriv(['aaa/user/send-message']);

		$model = new UserSendMessageForm();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		try {
			$result = $model->process();

			if ($result === false)
				throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

			return $result;

		} catch(\Exception $exp) {
			$msg = ExceptionHelper::CheckDuplicate($exp, $model);
			throw new UnprocessableEntityHttpException($msg);
		}
	}

	public function actionGenerate2faActivationCode()
	{
		$model = new Active2FAForm();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		try {
			$result = $model->generate();

			if ($result == false)
				throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

			return $result;

		} catch(\Exception $exp) {
			$msg = ExceptionHelper::CheckDuplicate($exp, $model);
			throw new UnprocessableEntityHttpException($msg);
		}
	}

	public function actionActive2fa()
	{
		$model = new Active2FAForm();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		try {
			$result = $model->process();

			if ($result == false)
				throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

			return $result;

		} catch(\Exception $exp) {
			$msg = ExceptionHelper::CheckDuplicate($exp, $model);
			throw new UnprocessableEntityHttpException($msg);
		}
	}

	public function actionInactive2fa()
	{
		$bodyParams = Yii::$app->request->getBodyParams();

		if (empty($bodyParams['type']))
			throw new NotFoundHttpException("parameters not provided");

		return [
			'result' => Active2FAForm::inactive2FA($bodyParams['type']),
		];
	}

}
