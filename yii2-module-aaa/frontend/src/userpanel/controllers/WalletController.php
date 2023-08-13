<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\userpanel\controllers;

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

  // public function init()
  // {
  //   parent::init();

  //   $viewPath = dirname(dirname(__FILE__))
  //     . DIRECTORY_SEPARATOR
  //     . 'views'
  //     . DIRECTORY_SEPARATOR
  //     . $this->id;

  //   $this->setViewPath($viewPath);
  // }

  public function actionIncrease($id)
  {
    $model = new WalletIncreaseForm;
    $model->walletID = $id;

		$formPosted = $model->load(Yii::$app->request->post());
		$done = false;
		if ($formPosted)
			$done = $model->process();

    if (Yii::$app->request->isAjax) {
      if ($done != false) {
        return $this->renderJson([
          'message' => Yii::t('app', 'Success'),
          // 'id' => $id,
          'redirect' => $done['paymentUrl'],
          // 'modalDoneFragment' => $this->modalDoneFragment,
        ]);
      }

      if ($formPosted) {
        return $this->renderJson([
          'status' => 'Error',
          'message' => Yii::t('app', 'Error'),
          // 'id' => $id,
          'error' => Html::errorSummary($model),
        ]);
      }

      return $this->renderAjaxModal('_increase', [
        'model' => $model,
      ]);
    }

    if ($done != false)
      return $this->redirect($done['paymentUrl']);

    return $this->render('increase', [
      'model' => $model,
    ]);
  }

  public function actionIncreaseDone($paymentkey, $errors = null)
  {
    $onlinePaymentModels = OnlinePaymentModel::find()
      ->andWhere(['onpUUID' => $paymentkey])
      ->all();

    if (empty($onlinePaymentModels))
      throw new NotFoundHttpException('Payment not found');

    $onlinePaymentModel = $onlinePaymentModels[0];

    if ($onlinePaymentModel->voucher->vchType != enuVoucherType::Credit)
      throw new UnprocessableEntityHttpException('Incorrect Voucher type (not credit)');

    $vchItems = json_decode($onlinePaymentModel->voucher->vchItems, true);
    if (empty($vchItems['inc-wallet-id']))
      throw new UnprocessableEntityHttpException('Incorrect Voucher (not for wallet)');

    // if ($onlinePaymentModel->onpStatus != enuOnlinePaymentStatus::Paid)
    //   throw new UnprocessableEntityHttpException('Incorrect Payment status (not paid)');

    return $this->render('increase-done', [
      'onlinePaymentModel' => $onlinePaymentModel,
			'errors' => $errors ? urldecode($errors) : null,
    ]);
  }

}
