<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use shopack\aaa\backend\models\OrderChangeDeliveryMethodForm;
use shopack\aaa\backend\models\OrderPaymentForm;
use shopack\base\common\helpers\ExceptionHelper;
use shopack\base\backend\controller\BaseCrudController;
use shopack\base\backend\helpers\PrivHelper;
use shopack\aaa\backend\models\VoucherModel;
use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\aaa\common\enums\enuVoucherType;

class VoucherController extends BaseCrudController
{
	public function behaviors()
	{
		$behaviors = parent::behaviors();

		$behaviors[static::BEHAVIOR_AUTHENTICATOR]['except'] = [
			'process-voucher',
		];

		return $behaviors;
	}

	public $modelClass = VoucherModel::class;

	public function permissions()
	{
		$checkOwner = function($model) : bool {
			return (($model != null) && ($model['vchOwnerUserID'] == Yii::$app->user->id));
		};

		return [
			'index'  => [
										'aaa/voucher/crud' => '0100',
										'filter' => function($query) {
											Yii::$app->user->assertIsNotGuest();
											$query->andWhere(['vchOwnerUserID' => Yii::$app->user->id]);
										},
									],
			'view'   => ['aaa/voucher/crud' => '0100', 'checker' => $checkOwner],
			// 'create' => ['aaa/voucher/crud' => '1000', 'checker' => $checkOwner],
			// 'update' => ['aaa/voucher/crud' => '0010', 'checker' => $checkOwner],
			// 'delete' => ['aaa/voucher/crud' => '0001', 'checker' => $checkOwner],
			// 'undelete' => ['aaa/voucher/undelete'],
		];
	}

	public function queryAugmentaters()
	{
		return [
			'index' => function($query) {
				$query
					->joinWith('owner')
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
			'view' => function($query) {
				$query
					->joinWith('owner')
					->with('createdByUser')
					->with('updatedByUser')
					->with('removedByUser')
				;
			},
		];
	}

	public function actionProcessVoucher($id)
	{
		$res = $this->findModel($id)->processVoucher();

		return [
			'result' => $res ? 'ok' : 'error',
		];
	}

	public function actionGetOrCreateOpenInvoice()
	{
		$bodyParams = Yii::$app->request->getBodyParams();

		$memberID		= $bodyParams['memberID'] ?? Yii::$app->user->id;
		$invoiceID	= $bodyParams['invoiceID'] ?? null;

		if (($memberID != Yii::$app->user->id)
				&& (PrivHelper::hasPriv('aaa/voucher/crud', '1000') == false))
			throw new ForbiddenHttpException('access denied');

		if (empty($invoiceID)) {
			$model = new VoucherModel();

			$model->vchOwnerUserID = $memberID;
			$model->vchType        = enuVoucherType::Invoice;
			$model->vchAmount      = 0;
			$model->vchTotalAmount = 0;

			if ($model->save() == false)
				throw new UnprocessableEntityHttpException('Could not create new invoice');

			return $model;
		}

		$model = VoucherModel::find()
			// ->select(VoucherModel::selectableColumns())
			->andWhere(['vchID' => $invoiceID])
			// ->andWhere(['vchOwnerUserID' => $memberID])
			->andWhere(['vchType' => enuVoucherType::Invoice])
			->andWhere(['IN', 'vchStatus', [
				enuVoucherStatus::New,
				enuVoucherStatus::WaitForPayment,
			]])
			->andWhere(['vchRemovedAt' => 0])
			->asArray()
			->one();

		if ($model == null)
			throw new NotFoundHttpException('Invoice not found');

		if ($memberID != $model['vchOwnerUserID'])
			throw new ForbiddenHttpException('invoice is not yours');

		return $model;
	}

	public function actionUpdateOpenInvoice()
	{
		$data = $this->getSecureData();

		VoucherModel::updateBasketOrOpenInvoice($data['service'], $data['voucher']);

		return [
			'ok'
		];
	}

	public function actionSetInvoiceAsWaitForPayment()
	{
		$data = $this->getSecureData();

		VoucherModel::setInvoiceAsWaitForPayment($data['service'], $data['voucherID']);

		return [
			'ok'
		];
	}

	public function actionOrderChangeDeliveryMethod($id)
	{
		$model = new OrderChangeDeliveryMethodForm();
		$model->vchID = $id;

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		try {
			$result = $model->process();

			//convert errors to 422
			if ($result !== true)
				throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

			return $result;

		} catch(\Exception $exp) {
			$msg = ExceptionHelper::CheckDuplicate($exp, $model);
			throw new UnprocessableEntityHttpException($msg);
		}
	}

	public function actionOrderPayment($id)
	{
		$model = new OrderPaymentForm();
		$model->vchID = $id;

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		try {
			$result = $model->process();

			//convert errors to 422
			if ($result == false)
				throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

			return $result;

		} catch(\Exception $exp) {
			$msg = ExceptionHelper::CheckDuplicate($exp, $model);
			throw new UnprocessableEntityHttpException($msg);
		}
	}

	public function actionCancel($id)
	{
		$model = $this->findModel($id);

		if (($model->vchOwnerUserID != Yii::$app->user->id)
				&& (PrivHelper::hasPriv('aaa/voucher/cancel') == false))
			throw new ForbiddenHttpException('access denied');

		$model->doCancel();

		return [
			'result' => true,
		];
	}

	public function actionReprocess($id)
	{
		$model = $this->findModel($id);

		if (($model->vchOwnerUserID != Yii::$app->user->id)
				&& (PrivHelper::hasPriv('aaa/voucher/reprocess') == false))
			throw new ForbiddenHttpException('access denied');

		$model->doReprocess();

		return [
			'result' => true,
		];
	}

}
