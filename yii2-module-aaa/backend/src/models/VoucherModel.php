<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\models;

use Yii;
use yii\db\Expression;
use yii\web\UnprocessableEntityHttpException;
use shopack\base\common\helpers\Json;
use shopack\aaa\backend\classes\AAAActiveRecord;
use shopack\aaa\common\enums\enuVoucherType;
use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\aaa\common\enums\enuVoucherItemStatus;
use shopack\base\common\accounting\enums\enuUserAssetStatus;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\common\security\RsaPublic;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

class VoucherModel extends AAAActiveRecord
{
	use \shopack\aaa\common\models\VoucherModelTrait;

  use \shopack\base\common\db\SoftDeleteActiveRecordTrait;
  public function initSoftDelete()
  {
    $this->softdelete_RemovedStatus  = enuVoucherStatus::Removed;
    // $this->softdelete_StatusField    = 'vchStatus';
    $this->softdelete_RemovedAtField = 'vchRemovedAt';
    $this->softdelete_RemovedByField = 'vchRemovedBy';
	}

	public static function tableName()
	{
		return '{{%AAA_Voucher}}';
	}

	public function behaviors()
	{
		return [
			[
				'class' => \shopack\base\common\behaviors\RowDatesAttributesBehavior::class,
				'createdAtAttribute' => 'vchCreatedAt',
				'createdByAttribute' => 'vchCreatedBy',
				'updatedAtAttribute' => 'vchUpdatedAt',
				'updatedByAttribute' => 'vchUpdatedBy',
			],
		];
	}

	public function processVoucher()
	{
		switch ($this->vchType)
		{
			case enuVoucherType::Basket				: return $this->processVoucher_Basket();
			case enuVoucherType::Invoice			: return $this->processVoucher_Invoice();
			case enuVoucherType::Withdrawal		: return $this->processVoucher_Withdrawal();
			case enuVoucherType::Income				: return $this->processVoucher_Income();
			case enuVoucherType::Credit				: return $this->processVoucher_Credit();
			case enuVoucherType::TransferTo		: return $this->processVoucher_TransferTo();
			case enuVoucherType::TransferFrom	: return $this->processVoucher_TransferFrom();
			case enuVoucherType::Prize				: return $this->processVoucher_Prize();
		}
	}

	protected function processVoucher_Basket()
	{
		throw new UnprocessableEntityHttpException('The Basket cannot be processed');
	}

	protected function processVoucher_Invoice()
	{
		if ($this->vchStatus == enuVoucherStatus::Finished)
			return true;

		if (in_array($this->vchStatus, [enuVoucherStatus::Settled, enuVoucherStatus::Error]) == false)
      throw new UnprocessableEntityHttpException('The voucher status is not settled or error');

		//double check: is settled?
		if ($this->vchTotalAmount != ($this->vchTotalPaid ?? 0))
      throw new UnprocessableEntityHttpException('This voucher not paid totaly');

		$services = [];

		//1- get services
		foreach ($this->vchItems as $k => $voucherItem) {
			if (empty($services[$voucherItem['service']])) {
				$services[$voucherItem['service']] = [];
			}

			$services[$voucherItem['service']][] = $voucherItem;
		}

		$org_vchItems = $this->vchItems;
		$this->vchItems = null;
		$errorCount = 0;

		//2: call process-voucher-items for every service
		$parentModule = Yii::$app->topModule;

		foreach ($services as $service => $items) {
			$data = Json::encode([
				'service' => $service,
				'voucher' => $this,
				'items' => $items,
			]);

			$data = RsaPublic::model($parentModule->servicesPublicKeys[$service])->encrypt($data);

			list ($resultStatus, $resultData) = HttpHelper::callApi(
				"{$service}/accounting/process-voucher-items",
				HttpHelper::METHOD_POST,
				[],
				[
					'service'	=> $service,
					'data' => $data,
				]
			);

			if ($resultStatus < 200 || $resultStatus >= 300) {
				throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));
				++$errorCount;
			} else {
				foreach ($resultData as $resKey => $resVal) {
					foreach ($org_vchItems as $orgKey => $orgVal) {
						if ($orgVal['key'] == $resKey) {
							if (isset($resVal['ok'])) {
								$org_vchItems[$orgKey]['status'] = enuVoucherItemStatus::Processed;

								if (isset($org_vchItems[$orgKey]['error'])) {
									unset($org_vchItems[$orgKey]['error']);
								}
							} else if (isset($resVal['error'])) {
								++$errorCount;

								$org_vchItems[$orgKey]['status'] = enuVoucherItemStatus::Error;
								$org_vchItems[$orgKey]['error'] = $resVal['error'];
							}

							break;
						}
					}
				}
			}
		}

		//3: update items
		$this->vchItems = $org_vchItems;

		$this->vchStatus = ($errorCount > 0 ? enuVoucherStatus::Error : enuVoucherStatus::Finished);
		$ret = $this->save();

		return $ret;






/*
		$errorCount = 0;
		$vchItems = $this->vchItems;
		foreach ($vchItems as $k => $voucherItem) {
			if (empty($vchItems[$k]['status'])
				|| ($vchItems[$k]['status'] != enuVoucherItemStatus::Processed)
			) {
				try {
					$this->processVoucherItem($voucherItem);

					$vchItems[$k]['status'] = enuVoucherItemStatus::Processed;

					unset($vchItems[$k]['error']);

				} catch (\Throwable $th) {
					++$errorCount;
					$vchItems[$k]['status'] = enuVoucherItemStatus::Error;
					$vchItems[$k]['error'] = $th->getMessage();
					//throw $th;
				}
			}
		}

		$this->vchItems = $vchItems;
		$this->vchStatus = ($errorCount > 0 ? enuVoucherStatus::Error : enuVoucherStatus::Finished);
		return $this->save();
		*/
	}

	protected function processVoucher_Withdrawal() { return true; }
	protected function processVoucher_Income() { return true; }
	protected function processVoucher_Credit() { return true; }
	protected function processVoucher_TransferTo() { return true; }
	protected function processVoucher_TransferFrom() { return true; }
	protected function processVoucher_Prize() { return true; }

/*
	public function processVoucherItem($voucherItem)
	{
		$service = $voucherItem['service'];

		if ($service == 'aaa') {
			//todo: process aaa voucher item
			return true;
		}

		//other services:

		// $key       = $voucherItem['key'];
		// $slbkey    = $voucherItem['slbkey'];
		// $slbid     = $voucherItem['slbid'];
		// $desc      = $voucherItem['desc'];
		// $qty       = $voucherItem['qty'];
		// $unitprice = $voucherItem['unitprice'];

		$data = Json::encode($voucherItem);

		if (empty(Yii::$app->controller->module->servicesPublicKeys[$service]))
			$data = base64_encode($data);
		else
			$data = RsaPublic::model(Yii::$app->controller->module->servicesPublicKeys[$service])->encrypt($data);

		list ($resultStatus, $resultData) = HttpHelper::callApi($service . "/service/process-voucher-item",
			HttpHelper::METHOD_POST,
			[],
			[
				'vchid' => $this->vchID,
				'userid' => $this->vchOwnerUserID,
				'data' => $data,
			]
		);

		if ($resultStatus < 200 || $resultStatus >= 300)
			throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', $resultData['message'], $resultData));
	}
*/

	public static function updateBasketOrOpenInvoice($service, $voucher)
	{
		$vchType = $voucher['vchType'];
		if (($vchType != enuVoucherType::Basket) && ($vchType != enuVoucherType::Invoice))
			throw new UnprocessableEntityHttpException('Invalid voucher type');

		$vchStatus = $voucher['vchStatus'];
		if (($vchType == enuVoucherType::Invoice)
				&& ($vchStatus == enuVoucherStatus::New) && ($vchStatus == enuVoucherStatus::WaitForPayment))
			throw new UnprocessableEntityHttpException('Invalid voucher status');

		$userid = $voucher['vchOwnerUserID'];
		if (($vchType == enuVoucherType::Basket)
				&& ($userid != Yii::$app->user->id))
			throw new ForbiddenHttpException('Access denied');

		if (empty($voucher['vchID']))
			throw new UnprocessableEntityHttpException('Invalid voucher id');

		$voucherModel = VoucherModel::findOne(['vchID' => $voucher['vchID']]);

		if ($voucherModel == null)
			throw new NotFoundHttpException('Voucher not found');

		// if ($voucherModel->vchType != enuVoucherType::Basket)
		// 	throw new UnprocessableEntityHttpException('Voucher is not basket');

		// if ($voucherModel->vchStatus != enuVoucherStatus::New)
		// 	throw new UnprocessableEntityHttpException('Basket is not open');

		//---------------------------------
		$orgVchAmount         = $voucherModel->vchAmount ?? 0;
		$orgVchItemsDiscounts = $voucherModel->vchItemsDiscounts ?? 0;
		$orgVchItemsVATs			= $voucherModel->vchItemsVATs ?? 0;
		$orgVchTotalAmount    = $voucherModel->vchTotalAmount ?? 0;

		//---------------------------------
		// $allData = $_POST;
		// $service = $allData['service'];

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
			if (empty($voucherModel->vchPaidByWallet) == false) {
				$walletReturnAmount = $voucherModel->vchPaidByWallet - ($voucherModel->vchReturnToWallet ?? 0) - $voucherModel->vchTotalAmount;

				if ($walletReturnAmount > 0) {
					//start transaction
					$transaction = Yii::$app->db->beginTransaction();

					WalletModel::returnToTheWallet(
						$walletReturnAmount,
						$voucherModel
						// $walletModel->walID
					);

					//3: save to the voucher
					$voucherModel->vchReturnToWallet = ($voucherModel->vchReturnToWallet ?? 0) + $walletReturnAmount;
					$voucherModel->vchTotalPaid = $voucherModel->vchTotalPaid - $walletReturnAmount;
				}
			}

			//---------------------------------
			if ($voucherModel->save() == false)
				throw new UnprocessableEntityHttpException(implode("\n", $voucherModel->getFirstErrors()));

			//commit
			if (isset($transaction))
				$transaction->commit();

		} catch (\Throwable $exp) {
			if (isset($transaction))
				$transaction->rollBack();

			throw $exp;
		}
	}

	public static function setInvoiceAsWaitForPayment($service, $voucherID)
	{
		$fnGetConstQouted = function($value) { return "'{$value}'"; };

		$voucherTableName = VoucherModel::tableName();

		$qry =<<<SQL
UPDATE {$voucherTableName}
   SET vchStatus = {$fnGetConstQouted(enuVoucherStatus::WaitForPayment)}
 WHERE vchID = {$voucherID}
   AND vchType = {$fnGetConstQouted(enuVoucherType::Invoice)}
   AND vchStatus = {$fnGetConstQouted(enuVoucherStatus::New)}
SQL;

		$rowsCount = Yii::$app->db->createCommand($qry)->execute();
	}

	public function doCancel()
	{
		if ($this->vchType != enuVoucherType::Invoice)
			throw new UnauthorizedHttpException('نوع سند باید صورتحساب باشد.');

		if (($this->vchStatus != enuVoucherStatus::New)
				&& ($this->vchStatus != enuVoucherStatus::WaitForPayment))
			throw new UnauthorizedHttpException('وضعیت سفارش / صورتحساب باید جدید یا منتظر پرداخت باشد.');

		try {
			if (empty($this->vchTotalPaid) == false) {
				//start transaction
				$transaction = Yii::$app->db->beginTransaction();

				WalletModel::returnToTheWallet(
					$this->vchTotalPaid,
					$this
					// $walletModel->walID
				);

				$this->vchReturnToWallet = ($this->vchReturnToWallet ?? 0) + $this->vchTotalPaid;
				$this->vchTotalPaid = 0;
			}

			$this->vchStatus = enuVoucherStatus::Canceled;

			if ($this->save() == false)
				throw new UnprocessableEntityHttpException(implode("\n", $this->getFirstErrors()));

			//commit
			if (isset($transaction))
				$transaction->commit();

		} catch (\Throwable $exp) {
			if (isset($transaction))
				$transaction->rollBack();

			throw $exp;
		}

		return true;
	}

}
