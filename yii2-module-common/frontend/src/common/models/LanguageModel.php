<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\cmn\frontend\common\models;

use Yii;
use shopack\base\frontend\common\rest\RestClientActiveRecord;
// use shopack\cmn\common\enums\enuLanguageStatus;

class LanguageModel extends RestClientActiveRecord
{
	use \shopack\cmn\common\models\LanguageModelTrait;

	public static $resourceName = 'cmn/language';

	public function attributeLabels()
	{
		return [
			'lngID'               => Yii::t('app', 'ID'),
			'lngLanguageCode'     => Yii::t('cmn', 'Language Code'),
			'lngCountryCode'      => Yii::t('cmn', 'Country Code'),
			'lngName'             => Yii::t('app', 'Title'),
			// 'lngIsPreferred'			=> Yii::t('app', 'Is Preferred'),
			'lngCreatedAt'        => Yii::t('app', 'Created At'),
			'lngCreatedBy'        => Yii::t('app', 'Created By'),
			'lngCreatedBy_User'   => Yii::t('app', 'Created By'),
			'lngUpdatedAt'        => Yii::t('app', 'Updated At'),
			'lngUpdatedBy'        => Yii::t('app', 'Updated By'),
			'lngUpdatedBy_User'   => Yii::t('app', 'Updated By'),
			'lngRemovedAt'        => Yii::t('app', 'Removed At'),
			'lngRemovedBy'        => Yii::t('app', 'Removed By'),
			'lngRemovedBy_User'   => Yii::t('app', 'Removed By'),
		];
	}

	public function isSoftDeleted()
  {
    return false; //($this->lngStatus == enuLanguageStatus::Removed);
  }

	public static function canCreate() {
		return true;
	}

	public function canUpdate() {
		return true; //($this->lngStatus != enuLanguageStatus::Removed);
	}

	public function canDelete() {
		return true; //($this->lngStatus != enuLanguageStatus::Removed);
	}

	public function canUndelete() {
		return false; //($this->lngStatus == enuLanguageStatus::Removed);
	}

}
