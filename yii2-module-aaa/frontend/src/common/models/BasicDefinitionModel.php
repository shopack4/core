<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\frontend\common\rest\RestClientActiveRecord;
use shopack\aaa\common\enums\enuBasicDefinitionStatus;

class BasicDefinitionModel extends RestClientActiveRecord
{
	use \shopack\aaa\common\models\BasicDefinitionModelTrait;

	public static $resourceName = 'aaa/basic-definition';

	public function attributeLabels()
	{
		return [
			'bdfID'                    => Yii::t('app', 'ID'),
			'bdfType'                  => Yii::t('app', 'Type'),
			'bdfName'                  => Yii::t('app', 'Title'),
			'bdfStatus'                => Yii::t('app', 'Status'),
			'bdfCreatedAt'             => Yii::t('app', 'Created At'),
			'bdfCreatedBy'             => Yii::t('app', 'Created By'),
			'bdfCreatedBy_User'        => Yii::t('app', 'Created By'),
			'bdfUpdatedAt'             => Yii::t('app', 'Updated At'),
			'bdfUpdatedBy'             => Yii::t('app', 'Updated By'),
			'bdfUpdatedBy_User'        => Yii::t('app', 'Updated By'),
			'bdfRemovedAt'             => Yii::t('app', 'Removed At'),
			'bdfRemovedBy'             => Yii::t('app', 'Removed By'),
			'bdfRemovedBy_User'        => Yii::t('app', 'Removed By'),
		];
	}

	public function isSoftDeleted()
  {
    return ($this->bdfStatus == enuBasicDefinitionStatus::Removed);
  }

	public static function canCreate() {
		return true;
	}

	public function canUpdate() {
		return ($this->bdfStatus != enuBasicDefinitionStatus::Removed);
	}

	public function canDelete() {
		return ($this->bdfStatus != enuBasicDefinitionStatus::Removed);
	}

	public function canUndelete() {
		return ($this->bdfStatus == enuBasicDefinitionStatus::Removed);
	}

}
