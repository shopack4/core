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
use shopack\base\backend\controller\BaseRestController;
use shopack\aaa\backend\models\BasketForm;
use shopack\aaa\backend\models\BasketItemForm;
use shopack\aaa\backend\models\BasketCheckoutForm;
use shopack\aaa\backend\models\VoucherModel;
use shopack\aaa\backend\models\WalletModel;
use shopack\aaa\backend\models\WalletTransactionModel;
use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\aaa\common\enums\enuVoucherType;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\common\helpers\Json;
use shopack\base\common\security\RsaPrivate;
use shopack\base\common\security\RsaPublic;

// use shopack\base\backend\models\BasketModel;

class BasketController extends BaseRestController
{
	public function getSecureData()
	{
		// $allData = array_merge($_GET, $_POST);
		$allData = $_POST;

		$service = $allData['service'];
		if (empty($service))
			throw new UnprocessableEntityHttpException('NOT_PROVIDED:Service');

		$data = $allData['data'];
		if (empty($data))
			throw new UnprocessableEntityHttpException('NOT_PROVIDED:Data');

		$module = Yii::$app->controller->module;

		$key = $module->servicesPublicKeys[$service];
		$rsaModel = RsaPublic::model($key);
		$data = $rsaModel->decrypt($data);

		$data = Json::decode($data);

		if ($service != $data['service']) //todo: change to sanity check
			throw new ForbiddenHttpException('INVALID:Service');

		return $data;
	}

	public function actionGetCurrent($recheckItems = false)
	{
		if (Yii::$app->user->isGuest)
			throw new ForbiddenHttpException('guest not allowed');

		// $recheckItems = $_POST['recheckItems'] ?? false;

		// $data = $this->getSecureData();

		// $userid = $data['userid'];
		// if ($userid != Yii::$app->user->id)
		// 	throw new ForbiddenHttpException('Access denied');

		$model = VoucherModel::find()
			->select(VoucherModel::selectableColumns())
			->andWhere(['vchOwnerUserID' => Yii::$app->user->id])
			->andWhere(['vchType' => enuVoucherType::Basket])
			->andWhere(['vchStatus' => enuVoucherStatus::New])
			->andWhere(['vchRemovedAt' => 0])
			->asArray()
			->one();

		if ($model == null) {
			$model = new VoucherModel();
			$model->vchOwnerUserID = Yii::$app->user->id;
			$model->vchType        = enuVoucherType::Basket;
			$model->vchAmount      = 0;
			$model->vchTotalAmount = 0;
			if ($model->save() == false) {
				throw new UnprocessableEntityHttpException('could not create new basket');
			}
			return $model;
		}

		if ($recheckItems) {
			$parentModule = Yii::$app->topModule;

			if (empty($model['vchItems']) == false) {
				if (is_string($model['vchItems'])) {
					$model['vchItems'] = Json::decode($model['vchItems']);
				}

				$services = [];

				//1- get services
				foreach ($model['vchItems'] as $item) {
					if (empty($services[$item['service']])) {
						$services[$item['service']] = [];
					}

					$services[$item['service']][] = $item;
				}

				//@TEMP:
				$_old_vchItems = $model['vchItems'];

				$model['vchItems'] = null;
				$newItems = [];

				//2: call recheck for every service
				foreach ($services as $service => $items) {
					$data = Json::encode([
						'service' => $service,
						'prevoucher' => $model,
						'items' => $items,
					]);

					$data = RsaPublic::model($parentModule->servicesPublicKeys[$service])->encrypt($data);

					list ($resultStatus, $resultData) = HttpHelper::callApi(
						"{$service}/accounting/recheck-basket-items",
						HttpHelper::METHOD_POST,
						[],
						[
							'service'	=> $service,
							'data' => $data,
						]
					);

					if ($resultStatus < 200 || $resultStatus >= 300) {
						// throw new \yii\web\HttpException($resultStatus, Yii::t('mha', $resultData['message'], $resultData));
					} else {

						//add to $newItems

					}
				}

				//3: add new items
				$model['vchItems'] = $newItems;


				//@TEMP:
				$model['vchItems'] = $_old_vchItems;

			}
		}

		return $model;
	}

	public function actionSetCurrent()
	{
		$data = $this->getSecureData();

		$voucher = $data['voucher'];

		$userid = $voucher['vchOwnerUserID'];
		if ($userid != Yii::$app->user->id)
			throw new ForbiddenHttpException('Access denied');

		if (empty($voucher['vchID']))
			throw new UnprocessableEntityHttpException('Invalid voucher id');

		$voucherModel = VoucherModel::findOne(['vchID' => $voucher['vchID']]);

		if ($voucherModel == null)
			throw new NotFoundHttpException('Voucher not found');

		if ($voucherModel->vchType != enuVoucherType::Basket)
			throw new UnprocessableEntityHttpException('Voucher is not basket');

		if ($voucherModel->vchStatus != enuVoucherStatus::New)
			throw new UnprocessableEntityHttpException('Basket is not open');

		//---------------------------------
		$orgVchAmount         = $voucherModel->vchAmount ?? 0;
		$orgVchItemsDiscounts = $voucherModel->vchItemsDiscounts ?? 0;
		$orgVchItemsVATs			= $voucherModel->vchItemsVATs ?? 0;
		$orgVchTotalAmount    = $voucherModel->vchTotalAmount ?? 0;

		//---------------------------------
		$allData = $_POST;
		$service = $allData['service'];

		$newServiceItems = [];
		foreach ($voucher['vchItems'] as $newItem) {
			if ($newItem['service'] != $service)
				continue;

			$newServiceItems[] = $newItem;
		}

		$vchItems = $voucherModel->vchItems ?? [];
		foreach ($vchItems as $oldKey => $oldItem) {
			if ($oldItem['service'] != $service)
				continue;

			$found = false;
			foreach ($newServiceItems as $newIndex => $newItem) {
				//found in both: update
				if ($oldItem['key'] == $newItem['key']) {
					// if (Json::encode($oldItem) != Json::encode($newItem)) {
						$orgVchAmount         -= $oldItem['subTotal'] ?? 0;
						$orgVchItemsDiscounts -= $oldItem['discount'] ?? 0;
						$orgVchItemsVATs			-= $oldItem['vat'] ?? 0;
						$orgVchTotalAmount    -= $oldItem['totalPrice'] ?? 0;

						$orgVchAmount         += $newItem['subTotal'] ?? 0;
						$orgVchItemsDiscounts += $newItem['discount'] ?? 0;
						$orgVchItemsVATs			+= $newItem['vat'] ?? 0;
						$orgVchTotalAmount    += $newItem['totalPrice'] ?? 0;

						$vchItems[$oldKey] = $newItem;
					// }

					$found = true;
					unset($newServiceItems[$newIndex]);
					break;
				}
			}

			//not found in new data: remove
			if ($found == false) {
				$orgVchAmount         -= $oldItem['subTotal'] ?? 0;
				$orgVchItemsDiscounts -= $oldItem['discount'] ?? 0;
				$orgVchItemsVATs			-= $oldItem['vat'] ?? 0;
				$orgVchTotalAmount    -= $oldItem['totalPrice'] ?? 0;

				unset($vchItems[$oldKey]);
			}
		}

		//not exists in old data: add
		foreach ($newServiceItems as $newItem) {
			$orgVchAmount         += $newItem['subTotal'] ?? 0;
			$orgVchItemsDiscounts += $newItem['discount'] ?? 0;
			$orgVchItemsVATs			+= $newItem['vat'] ?? 0;
			$orgVchTotalAmount    += $newItem['totalPrice'] ?? 0;

			$vchItems[] = $newItem;
		}

		//---------------------------------
		$voucherModel->vchItems = $vchItems ?? null;

		$voucherModel->vchAmount					= $orgVchAmount;
		$voucherModel->vchItemsDiscounts	= $orgVchItemsDiscounts;
		$voucherModel->vchItemsVATs				= $orgVchItemsVATs;
		$voucherModel->vchTotalAmount			= $orgVchTotalAmount;
		// $voucherModel->vchDeliveryMethodID = $voucher['vchDeliveryMethodID'] ?? null;
		// $voucherModel->vchDeliveryAmount   = $voucher['vchDeliveryAmount'] ?? null;

		try {
			//2: check paid by wallet return amount
			if (($voucherModel->vchPaidByWallet ?? 0) > $voucherModel->vchTotalAmount) {
				//start transaction
				$transaction = Yii::$app->db->beginTransaction();

				$walletReturnAmount = $voucherModel->vchPaidByWallet - $voucherModel->vchTotalAmount;

				$walletModel = WalletModel::ensureIHaveDefaultWallet();

				//2.1: create wallet transaction
				$walletTransactionModel = new WalletTransactionModel();
				$walletTransactionModel->wtrWalletID	= $walletModel->walID;
				$walletTransactionModel->wtrVoucherID	= $voucherModel->vchID;
				$walletTransactionModel->wtrAmount		= $walletReturnAmount;
				$walletTransactionModel->save();

				//2.2: increase wallet amount
				$walletTableName = WalletModel::tableName();
				$qry =<<<SQL
  UPDATE {$walletTableName}
     SET walRemainedAmount = walRemainedAmount + {$walletReturnAmount}
   WHERE walID = {$walletModel->walID}
SQL;
				$rowsCount = Yii::$app->db->createCommand($qry)->execute();

				//3: save to the voucher
				$voucherModel->vchPaidByWallet = $voucherModel->vchTotalAmount;
				$voucherModel->vchTotalPaid = $voucherModel->vchTotalPaid - $walletReturnAmount;
			}

			//---------------------------------
			if ($voucherModel->save() == false)
				throw new UnprocessableEntityHttpException(implode("\n", $voucherModel->getFirstErrors()));

			//commit
			if (isset($transaction))
				$transaction->commit();

		} catch (\Exception $e) {
			if (isset($transaction))
				$transaction->rollBack();
			throw $e;
		} catch (\Throwable $e) {
			if (isset($transaction))
				$transaction->rollBack();
			throw $e;
		}

		return [
			'ok'
		];
	}

	//just called from other services with encryption
	// public function actionAddItem()
	// {
	// 	return BasketItemForm::addItem();
	// }

	// public function actionRemoveItem($key)
	// {
	// 	return BasketItemForm::removeItem($key);
	// }

	public function actionCheckout()
	{
		$model = new BasketCheckoutForm();

		if ($model->load(Yii::$app->request->getBodyParams(), '') == false)
			throw new NotFoundHttpException("parameters not provided");

		try {
			$result = $model->checkout();
			if ($result == false)
				throw new UnprocessableEntityHttpException(implode("\n", $model->getFirstErrors()));

			return $result;

		} catch(\Exception $exp) {
			$msg = ExceptionHelper::CheckDuplicate($exp, $model);
			throw new UnprocessableEntityHttpException($msg);
		}
	}

}
