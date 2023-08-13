<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use shopack\base\frontend\rest\RestClientActiveRecord;

class UploadFileModel extends RestClientActiveRecord
{
  use \shopack\aaa\common\models\UploadFileModelTrait;

  public static $resourceName = 'aaa/upload-file';
  public static $primaryKey = ['uflID'];

  public function attributeLabels()
	{
		return [
      'uflID'                => Yii::t('app', 'ID'),
      'uflOwnerUserID'       => Yii::t('app', 'Owner'),
      'uflPath'              => Yii::t('app', 'Path'),
      'uflOriginalFileName'  => Yii::t('app', 'Original File Name'),
      'uflCounter'           => Yii::t('app', 'Counter'),
      'uflStoredFileName'    => Yii::t('app', 'Stored File Name'),
      'uflSize'              => Yii::t('app', 'Size'),
      'uflFileType'          => Yii::t('app', 'File Type'),
      'uflMimeType'          => Yii::t('app', 'Mime Type'),
      'uflLocalFullFileName' => Yii::t('app', 'Local Full File Name'),
      'uflStatus'            => Yii::t('app', 'Status'),
      'uflCreatedAt'         => Yii::t('app', 'Created At'),
      'uflCreatedBy'         => Yii::t('app', 'Created By'),
      'uflCreatedBy_User'    => Yii::t('app', 'Created By'),
      'uflUpdatedAt'         => Yii::t('app', 'Updated At'),
      'uflUpdatedBy'         => Yii::t('app', 'Updated By'),
      'uflUpdatedBy_User'    => Yii::t('app', 'Updated By'),
      'uflRemovedAt'         => Yii::t('app', 'Removed At'),
      'uflRemovedBy'         => Yii::t('app', 'Removed By'),
      'uflRemovedBy_User'    => Yii::t('app', 'Removed By'),

      'fullFileUrl'          => Yii::t('app', 'Full File Url'),
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

  public function isImage()
  {
    return str_starts_with($this->uflMimeType ?? '', 'image');
  }

}
