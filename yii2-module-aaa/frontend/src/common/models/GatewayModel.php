<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use shopack\base\frontend\common\rest\RestClientActiveRecord;
use shopack\aaa\common\enums\enuGatewayStatus;

class GatewayModel extends RestClientActiveRecord
{
	use \shopack\aaa\common\models\GatewayModelTrait;

	public static $resourceName = 'aaa/gateway';

	public function attributeLabels()
	{
		return [
			'gtwID'               => Yii::t('app', 'ID'),
			'gtwName'             => Yii::t('app', 'Name'),
			'gtwUUID'              => Yii::t('app', 'Key'),
			'gtwPluginType'       => Yii::t('aaa', 'Plugin Type'),
			'gtwPluginName'       => Yii::t('aaa', 'Plugin'),
			'gtwPluginParameters' => Yii::t('aaa', 'Plugin Parameters'),
			'gtwRestrictions'     => Yii::t('aaa', 'Plugin Restrictions'),
			'gtwUsages'           => Yii::t('aaa', 'Plugin Usages'),
			'gtwStatus'           => Yii::t('app', 'Status'),
			'gtwCreatedAt'        => Yii::t('app', 'Created At'),
			'gtwCreatedBy'        => Yii::t('app', 'Created By'),
			'gtwCreatedBy_User'   => Yii::t('app', 'Created By'),
			'gtwUpdatedAt'        => Yii::t('app', 'Updated At'),
			'gtwUpdatedBy'        => Yii::t('app', 'Updated By'),
			'gtwUpdatedBy_User'   => Yii::t('app', 'Updated By'),
			'gtwRemovedAt'        => Yii::t('app', 'Removed At'),
			'gtwRemovedBy'        => Yii::t('app', 'Removed By'),
			'gtwRemovedBy_User'   => Yii::t('app', 'Removed By'),
		];
	}

	public function isSoftDeleted()
  {
    return ($this->gtwStatus == enuGatewayStatus::Removed);
  }

	public static function canCreate() {
		return true;
	}

	public function canUpdate() {
		return ($this->gtwStatus != enuGatewayStatus::Removed);
	}

	public function canDelete() {
		return ($this->gtwStatus != enuGatewayStatus::Removed);
	}

	public function canUndelete() {
		return ($this->gtwStatus == enuGatewayStatus::Removed);
	}

}
