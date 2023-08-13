<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use shopack\base\frontend\rest\RestClientActiveRecord;
// use shopack\aaa\common\enums\enuGeoCityOrVillageStatus;

class GeoCityOrVillageModel extends RestClientActiveRecord
{
	use \shopack\aaa\common\models\GeoCityOrVillageModelTrait;

	public static $resourceName = 'aaa/geo-city-or-village';
  public static $primaryKey = ['ctvID'];

	public function attributeLabels()
	{
		return [
			'ctvID'               => Yii::t('app', 'ID'),
			'ctvName'             => Yii::t('aaa', 'City Or Village'),
			'ctvStateID'			    => Yii::t('aaa', 'State'),
			'ctvCreatedAt'        => Yii::t('app', 'Created At'),
			'ctvCreatedBy'        => Yii::t('app', 'Created By'),
			'ctvCreatedBy_User'   => Yii::t('app', 'Created By'),
			'ctvUpdatedAt'        => Yii::t('app', 'Updated At'),
			'ctvUpdatedBy'        => Yii::t('app', 'Updated By'),
			'ctvUpdatedBy_User'   => Yii::t('app', 'Updated By'),
			'ctvRemovedAt'        => Yii::t('app', 'Removed At'),
			'ctvRemovedBy'        => Yii::t('app', 'Removed By'),
			'ctvRemovedBy_User'   => Yii::t('app', 'Removed By'),
		];
	}

	public function isSoftDeleted()
  {
    return false; //($this->ctvStatus == enuGeoCityOrVillageStatus::Removed);
  }

	public static function canCreate() {
		return true;
	}

	public function canUpdate() {
		return true; //($this->ctvStatus != enuGeoCityOrVillageStatus::Removed);
	}

	public function canDelete() {
		return true; //($this->ctvStatus != enuGeoCityOrVillageStatus::Removed);
	}

	public function canUndelete() {
		return false; //($this->ctvStatus == enuGeoCityOrVillageStatus::Removed);
	}

}
