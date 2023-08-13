<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use shopack\base\frontend\rest\RestClientActiveRecord;
// use shopack\aaa\common\enums\enuGeoCountryStatus;

class GeoCountryModel extends RestClientActiveRecord
{
	use \shopack\aaa\common\models\GeoCountryModelTrait;

	public static $resourceName = 'aaa/geo-country';
  public static $primaryKey = ['cntrID'];

	public function attributeLabels()
	{
		return [
			'cntrID'               => Yii::t('app', 'ID'),
			'cntrName'             => Yii::t('aaa', 'Country'),
			'cntrCreatedAt'        => Yii::t('app', 'Created At'),
			'cntrCreatedBy'        => Yii::t('app', 'Created By'),
			'cntrCreatedBy_User'   => Yii::t('app', 'Created By'),
			'cntrUpdatedAt'        => Yii::t('app', 'Updated At'),
			'cntrUpdatedBy'        => Yii::t('app', 'Updated By'),
			'cntrUpdatedBy_User'   => Yii::t('app', 'Updated By'),
			'cntrRemovedAt'        => Yii::t('app', 'Removed At'),
			'cntrRemovedBy'        => Yii::t('app', 'Removed By'),
			'cntrRemovedBy_User'   => Yii::t('app', 'Removed By'),
		];
	}

	public function isSoftDeleted()
  {
    return false; //($this->cntrStatus == enuGeoCountryStatus::Removed);
  }

	public static function canCreate() {
		return true;
	}

	public function canUpdate() {
		return true; //($this->cntrStatus != enuGeoCountryStatus::Removed);
	}

	public function canDelete() {
		return true; //($this->cntrStatus != enuGeoCountryStatus::Removed);
	}

	public function canUndelete() {
		return false; //($this->cntrStatus == enuGeoCountryStatus::Removed);
	}

}
