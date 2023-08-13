<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\web\UnprocessableEntityHttpException;
use shopack\base\frontend\helpers\Html;
use shopack\aaa\common\enums\enuOnlinePaymentStatus;
use shopack\aaa\common\enums\enuVoucherType;
use shopack\aaa\frontend\common\auth\BaseCrudController;
use shopack\aaa\frontend\common\models\WalletModel;
use shopack\aaa\frontend\common\models\WalletSearchModel;
use shopack\aaa\frontend\common\models\WalletIncreaseForm;
use shopack\aaa\frontend\common\models\OnlinePaymentModel;

class WalletController extends BaseCrudController
{
	public $modelClass = WalletModel::class;
	public $searchModelClass = WalletSearchModel::class;

}
