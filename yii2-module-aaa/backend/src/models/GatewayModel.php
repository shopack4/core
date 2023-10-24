<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\models;

use Yii;
use Ramsey\Uuid\Uuid;
use shopack\aaa\backend\classes\AAAActiveRecord;
use shopack\aaa\common\enums\enuGatewayStatus;
use shopack\base\common\helpers\ArrayHelper;

class GatewayModel extends AAAActiveRecord
{
	use \shopack\aaa\common\models\GatewayModelTrait;

  use \shopack\base\common\db\SoftDeleteActiveRecordTrait;
  public function initSoftDelete()
  {
    $this->softdelete_RemovedStatus  = enuGatewayStatus::Removed;
    // $this->softdelete_StatusField    = 'gtwStatus';
    $this->softdelete_RemovedAtField = 'gtwRemovedAt';
    $this->softdelete_RemovedByField = 'gtwRemovedBy';
	}

	public static function tableName()
	{
		return '{{%AAA_Gateway}}';
	}

	public function behaviors()
	{
		return [
			[
				'class' => \shopack\base\common\behaviors\RowDatesAttributesBehavior::class,
				'createdAtAttribute' => 'gtwCreatedAt',
				'createdByAttribute' => 'gtwCreatedBy',
				'updatedAtAttribute' => 'gtwUpdatedAt',
				'updatedByAttribute' => 'gtwUpdatedBy',
			],
		];
	}

	public function save($runValidation = true, $attributeNames = null)
  {
		if (empty($this->gtwPluginParameters) == false) {
			$paramsSchema = yii::$app->controller->module->GatewayPluginParamsSchema($this->gtwPluginName);

			if (empty($paramsSchema) == false) {
				$gtwPluginParameters = $this->gtwPluginParameters;

				$gtwPluginParameters = ArrayHelper::filterNullOrEmpty($gtwPluginParameters);

				// foreach ($paramsSchema as $v) {
				// 	if (empty($gtwPluginParameters[$v['id']]) == false) {
				// 		if ($v['type'] == 'kvp-multi') {
				// 			$paramValue = $gtwPluginParameters[$v['id']];

				// 			foreach ($paramValue as $kp => $vp) {
				// 				if (empty($vp['value'])) {
				// 					unset($paramValue[$kp]);
				// 				}
				// 			}

				// 			if (empty($paramValue))
				// 				unset($gtwPluginParameters[$v['id']]);
				// 			else
				// 				//array_values used for reindexing keys from zero
				// 				$gtwPluginParameters[$v['id']] = array_values($paramValue);
				// 		}
				// 	}
				// }

				$this->gtwPluginParameters = $gtwPluginParameters;
			}
		}

    return parent::save($runValidation, $attributeNames);
  }

	public function insert($runValidation = true, $attributes = null)
	{
		if (empty($this->gtwUUID))
			$this->gtwUUID = Uuid::uuid4()->toString();
			// $this->gtwUUID = Yii::$app->security->generateRandomString();

		return parent::insert($runValidation, $attributes);
	}

	private $_gatewayClass = null;
	public function getGatewayClass()
	{
		if ($this->_gatewayClass == null) {
			$aaaModule = Yii::$app->getModule('aaa');
			$this->_gatewayClass = clone $aaaModule->GatewayClass($this->gtwPluginName);
			$this->_gatewayClass->extensionModel = $this;
		}
		return $this->_gatewayClass;
	}

}
