<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use shopack\base\frontend\rest\RestClientActiveRecord;
// use shopack\aaa\common\enums\enuGeoTownStatus;

class GeoTownModel extends RestClientActiveRecord
{
	use \shopack\aaa\common\models\GeoTownModelTrait;

	public static $resourceName = 'aaa/geo-town';
  public static $primaryKey = ['twnID'];

	public function attributeLabels()
	{
		return [
			'twnID'               => Yii::t('app', 'ID'),
			'twnName'             => Yii::t('aaa', 'Town'),
			'twnCityID'			      => Yii::t('aaa', 'City Or Village'),
			'twnCreatedAt'        => Yii::t('app', 'Created At'),
			'twnCreatedBy'        => Yii::t('app', 'Created By'),
			'twnCreatedBy_User'   => Yii::t('app', 'Created By'),
			'twnUpdatedAt'        => Yii::t('app', 'Updated At'),
			'twnUpdatedBy'        => Yii::t('app', 'Updated By'),
			'twnUpdatedBy_User'   => Yii::t('app', 'Updated By'),
			'twnRemovedAt'        => Yii::t('app', 'Removed At'),
			'twnRemovedBy'        => Yii::t('app', 'Removed By'),
			'twnRemovedBy_User'   => Yii::t('app', 'Removed By'),
		];
	}

	public function isSoftDeleted()
  {
    return false; //($this->twnStatus == enuGeoTownStatus::Removed);
  }

	public static function canCreate() {
		return true;
	}

	public function canUpdate() {
		return true; //($this->twnStatus != enuGeoTownStatus::Removed);
	}

	public function canDelete() {
		return true; //($this->twnStatus != enuGeoTownStatus::Removed);
	}

	public function canUndelete() {
		return false; //($this->twnStatus == enuGeoTownStatus::Removed);
	}

}
