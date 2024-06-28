<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use shopack\aaa\backend\models\OrderChangeDeliveryMethodForm;
use shopack\aaa\backend\models\OrderPaymentForm;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\data\ActiveDataProvider;
use shopack\base\common\helpers\ExceptionHelper;
use shopack\base\backend\controller\BaseRestController;
use shopack\base\backend\helpers\PrivHelper;
use shopack\aaa\backend\models\VoucherModel;
use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\aaa\common\enums\enuVoucherType;

class VoucherController extends BaseRestController
{
	public function behaviors()
	{
		$behaviors = parent::behaviors();

		$behaviors[BaseRestController::BEHAVIOR_AUTHENTICATOR]['except'] = [
			'process-voucher',
		];

		return $behaviors;
	}

	protected function findModel($id)
	{
		if (($model = VoucherModel::findOne($id)) !== null)
			return $model;

		throw new NotFoundHttpException('The requested item does not exist.');
	}

	public function actionOptions()
	{
		return 'options';
	}

	public function actionIndex()
	{
		$filter = $this->checkPrivAndGetFilter('aaa/voucher/crud', '0100', 'vchOwnerUserID');

		$searchModel = new VoucherModel;
		$query = $searchModel::find()
			->select(VoucherModel::selectableColumns())
			->joinWith('owner')
			->with('createdByUser')
			->with('updatedByUser')
			->with('removedByUser')
			->asArray()
		;

		$searchModel->fillQueryFromRequest($query);

		if (empty($filter) == false)
			$query->andWhere($filter);

		return $this->queryAllToResponse($query);
	}

	public function actionView($id)
	{
		$model = VoucherModel::find()
			->select(VoucherModel::selectableColumns())
			->joinWith('owner')
			->with('createdByUser')
			->with('updatedByUser')
			->with('removedByUser')
			->where(['vchID' => $id])
			->asArray()
			->one()
		;

		if ((PrivHelper::hasPriv('aaa/voucher/crud', '0100') == false)
			&& ($model != null)
			&& ($model['vchOwnerUserID'] != Yii::$app->user->id)
		) {
			throw new ForbiddenHttpException('access denied');
		}

		return $this->modelToResponse($model);
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
		$memberID		= $_POST['memberID'] ?? Yii::$app->user->id;
		$invoiceID	= $_POST['invoiceID'] ?? null;

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
			->select(VoucherModel::selectableColumns())
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
			if ($result !== true)
				throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

			return $result;

		} catch(\Exception $exp) {
			$msg = ExceptionHelper::CheckDuplicate($exp, $model);
			throw new UnprocessableEntityHttpException($msg);
		}
	}

	public function actionCancel($id)
	{
		PrivHelper::checkPriv('aaa/voucher/cancel');

		$model = $this->findModel($id);
		$model->doCancel();

		return [
			'result' => true,
		];
	}

}
