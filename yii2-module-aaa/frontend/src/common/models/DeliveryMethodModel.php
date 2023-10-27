<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\rest\RestClientActiveRecord;
use shopack\aaa\common\enums\enuDeliveryMethodType;
use shopack\aaa\common\enums\enuDeliveryMethodStatus;

class DeliveryMethodModel extends RestClientActiveRecord
{
	use \shopack\aaa\common\models\DeliveryMethodModelTrait;

	public static $resourceName = 'aaa/delivery-method';

	public function attributeLabels()
	{
		return [
			'dlvID'               => Yii::t('app', 'ID'),
			'dlvName'             => Yii::t('aaa', 'Name'),
			'dlvType'             => Yii::t('app', 'Type'),
			'dlvAmount'           => Yii::t('aaa', 'Amount'),
			'dlvTotalUsedCount'   => Yii::t('aaa', 'Total Used Count'),
			'dlvTotalUsedAmount'  => Yii::t('aaa', 'Total Used Amount'),
			'dlvStatus'           => Yii::t('app', 'Status'),
			'dlvCreatedAt'        => Yii::t('app', 'Created At'),
			'dlvCreatedBy'        => Yii::t('app', 'Created By'),
			'dlvCreatedBy_User'   => Yii::t('app', 'Created By'),
			'dlvUpdatedAt'        => Yii::t('app', 'Updated At'),
			'dlvUpdatedBy'        => Yii::t('app', 'Updated By'),
			'dlvUpdatedBy_User'   => Yii::t('app', 'Updated By'),
			'dlvRemovedAt'        => Yii::t('app', 'Removed At'),
			'dlvRemovedBy'        => Yii::t('app', 'Removed By'),
			'dlvRemovedBy_User'   => Yii::t('app', 'Removed By'),
		];
	}

	public function extraRules()
	{
    $fnGetConst = function($value) { return $value; };
		$fnGetFieldId = function($field) { return Html::getInputId($this, $field); };

		return [
			['dlvAmount',
				'required',
				'when' => function ($model) {
					return ($model->dlvType == enuDeliveryMethodType::SendToCustomer);
				},
				'whenClient' => "function (attribute, value) {
					return ($('#{$fnGetFieldId('dlvType')}').val() == '{$fnGetConst(enuDeliveryMethodType::SendToCustomer)}');
				}"
			],
		];
	}

	public function isSoftDeleted()
  {
    return ($this->dlvStatus == enuDeliveryMethodStatus::Removed);
  }

	public static function canCreate() {
		return true;
	}

	public function canUpdate() {
		return ($this->dlvStatus != enuDeliveryMethodStatus::Removed);
	}

	public function canDelete() {
		return ($this->dlvStatus != enuDeliveryMethodStatus::Removed);
	}

	public function canUndelete() {
		return ($this->dlvStatus == enuDeliveryMethodStatus::Removed);
	}

}
