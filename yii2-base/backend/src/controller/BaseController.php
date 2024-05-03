<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\backend\controller;

use Yii;
use yii\rest\Controller;
use shopack\base\backend\helpers\PrivHelper;

class BaseController extends Controller
{
	public function actions()
	{
		return [
			'error' => [
				'class' => '\shopack\base\common\web\ErrorAction',
			],
			'captcha' => [
				'class' => 'yii\captcha\CaptchaAction',
				'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
			],
		];
	}

	public function checkPrivAndGetFilter(
		$neededPrivKey,
		$neededPrivValue,
		$filterUserIdKey
	) {
		$justForMe = $_GET['justForMe'] ?? false;

		$filter = [];
		if ($justForMe || (PrivHelper::hasPriv($neededPrivKey, $neededPrivValue) == false)) {
			$filter = [$filterUserIdKey => Yii::$app->user->id];
		}

		return $filter;
	}

}
