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
		// $qry = "SELECT UTC_TIMESTAMP(6) as _now;";
		// $result = Yii::$app->db->createCommand($qry)->queryOne();
		// $now1 = new \DateTimeImmutable($result['_now']);
		// $now2 = new \DateTimeImmutable($result['_now'], new \DateTimeZone('UTC'));
		// $now3 = new \DateTimeImmutable('now');
		// $now4 = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

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
