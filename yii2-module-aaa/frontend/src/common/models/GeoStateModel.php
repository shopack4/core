<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use shopack\base\frontend\rest\RestClientActiveRecord;
// use shopack\aaa\common\enums\enuGeoStateStatus;

class GeoStateModel extends RestClientActiveRecord
{
	use \shopack\aaa\common\models\GeoStateModelTrait;

	public static $resourceName = 'aaa/geo-state';
  public static $primaryKey = ['sttID'];

	public function attributeLabels()
	{
		return [
			'sttID'               => Yii::t('app', 'ID'),
			'sttName'             => Yii::t('aaa', 'State'),
			'sttCountryID'			  => Yii::t('aaa', 'Country'),
			'sttCreatedAt'        => Yii::t('app', 'Created At'),
			'sttCreatedBy'        => Yii::t('app', 'Created By'),
			'sttCreatedBy_User'   => Yii::t('app', 'Created By'),
			'sttUpdatedAt'        => Yii::t('app', 'Updated At'),
			'sttUpdatedBy'        => Yii::t('app', 'Updated By'),
			'sttUpdatedBy_User'   => Yii::t('app', 'Updated By'),
			'sttRemovedAt'        => Yii::t('app', 'Removed At'),
			'sttRemovedBy'        => Yii::t('app', 'Removed By'),
			'sttRemovedBy_User'   => Yii::t('app', 'Removed By'),
		];
	}

	public function isSoftDeleted()
  {
    return false; //($this->sttStatus == enuGeoStateStatus::Removed);
  }

	public static function canCreate() {
		return true;
	}

	public function canUpdate() {
		return true; //($this->sttStatus != enuGeoStateStatus::Removed);
	}

	public function canDelete() {
		return true; //($this->sttStatus != enuGeoStateStatus::Removed);
	}

	public function canUndelete() {
		return false; //($this->sttStatus == enuGeoStateStatus::Removed);
	}

}
