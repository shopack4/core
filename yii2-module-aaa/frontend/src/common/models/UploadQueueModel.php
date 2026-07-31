<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\models;

use Yii;
use shopack\base\frontend\common\rest\RestClientActiveRecord;

class UploadQueueModel extends RestClientActiveRecord
{
    use \shopack\aaa\common\models\UploadQueueModelTrait;

    public static $resourceName = 'aaa/upload-queue';

    public function attributeLabels()
    {
        return [
            'uquID'                => Yii::t('app', 'ID'),
            'uquFileID'            => Yii::t('aaa', 'File'),
            'uquGatewayID'         => Yii::t('aaa', 'Gateway'),
            'uquLockedAt'          => Yii::t('aaa', 'Locked At'),
            'uquLockedBy'          => Yii::t('aaa', 'Locked By'),
            'uquLastTryAt'         => Yii::t('aaa', 'Last Try At'),
            'uquStoredAt'          => Yii::t('aaa', 'Stored At'),
            'uquResult'            => Yii::t('aaa', 'Result'),
            'uquStatus'            => Yii::t('app', 'Status'),
            'uquCreatedAt'         => Yii::t('app', 'Created At'),
            'uquCreatedBy'         => Yii::t('app', 'Created By'),
            'uquCreatedBy_User'    => Yii::t('app', 'Created By'),
            'uquUpdatedAt'         => Yii::t('app', 'Updated At'),
            'uquUpdatedBy'         => Yii::t('app', 'Updated By'),
            'uquUpdatedBy_User'    => Yii::t('app', 'Updated By'),
            'uquRemovedAt'         => Yii::t('app', 'Removed At'),
            'uquRemovedBy'         => Yii::t('app', 'Removed By'),
            'uquRemovedBy_User'    => Yii::t('app', 'Removed By'),

            'fullFileUrl'          => Yii::t('app', 'Full File Url'),
        ];
    }

    public function isSoftDeleted()
    {
        return false; //($this->cntrStatus == enuGeoCountryStatus::Removed);
    }

    public static function canCreate()
    {
        return true;
    }

    public function canUpdate()
    {
        return true; //($this->cntrStatus != enuGeoCountryStatus::Removed);
    }

    public function canDelete()
    {
        return true; //($this->cntrStatus != enuGeoCountryStatus::Removed);
    }

    public function canUndelete()
    {
        return false; //($this->cntrStatus == enuGeoCountryStatus::Removed);
    }

    public function isImage()
    {
        return str_starts_with($this->uflMimeType ?? '', 'image');
    }
}
