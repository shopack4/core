<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\models;

use Yii;
use shopack\aaa\common\enums\enuOfflinePaymentStatus;
use shopack\aaa\common\enums\enuGender;
use shopack\aaa\backend\classes\AAAActiveRecord;
use shopack\aaa\backend\models\WalletModel;

class OfflinePaymentModel extends AAAActiveRecord
{
    use \shopack\aaa\common\models\OfflinePaymentModelTrait;

    use \shopack\base\common\db\SoftDeleteActiveRecordTrait;
    public function initSoftDelete()
    {
        $this->softdelete_RemovedStatus  = enuOfflinePaymentStatus::Removed;
        // $this->softdelete_StatusField    = 'ofpStatus';
        $this->softdelete_RemovedAtField = 'ofpRemovedAt';
        $this->softdelete_RemovedByField = 'ofpRemovedBy';
    }

    public static function tableName()
    {
        return '{{%AAA_OfflinePayment}}';
    }

    public function extraRules()
    {
        return [
            ['ofpWalletID', 'required'],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => \shopack\base\common\behaviors\RowDatesAttributesBehavior::class,
                'createdAtAttribute' => 'ofpCreatedAt',
                'createdByAttribute' => 'ofpCreatedBy',
                'updatedAtAttribute' => 'ofpUpdatedAt',
                'updatedByAttribute' => 'ofpUpdatedBy',
            ],
        ];
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        if (empty($_FILES) == false) {
            $uploadResult = Yii::$app->fileManager->saveUploadedFiles(
                /* userID             */
                $this->ofpOwnerUserID,
                /* targetPath         */
                'offline-payment',
                /* allowedFileTypes   */
                null,
                /* allowedMimeTypes   */
                ['image/png', 'image/gif', 'image/jpg', 'image/jpeg'],
                /* allowedMinFileSize */
                0,
                /* allowedMaxFileSize */
                2 * 1024 * 1024
            );

            if (empty($uploadResult))
                return false;

            foreach ($uploadResult as $k => $v) {
                $this->$k = $v['fileID'];
            }
        }

        if (empty($this->ofpWalletID)) {
            $walletModel = WalletModel::ensureIHaveDefaultWallet($this->ofpOwnerUserID);
            $this->ofpWalletID = $walletModel->walID;
        }

        // if ($this->validate() == false)
        // throw new UnprocessableEntityHttpException(implode("\n", $this->getFirstErrors()));

        $oldStatus = NULL;
        $dirtyAttributes = $this->getDirtyAttributes(['mbrknnStatus']);
        if (empty($dirtyAttributes) == false)
            $oldStatus = $this->oldAttributes['ofpStatus'] ?? null;

        /*************************************************/
        if (parent::save($runValidation, $attributeNames) == false)
            return false;
        /*************************************************/

        //status changed
        if ($oldStatus != $this->ofpStatus) {
            $status_to_message_key = [
                enuOfflinePaymentStatus::WaitForApprove => 'offlinePaymentStatusChangedTo_WaitForApprove',
                enuOfflinePaymentStatus::Approved       => 'offlinePaymentStatusChangedTo_Approved',
                enuOfflinePaymentStatus::Rejected       => 'offlinePaymentStatusChangedTo_Rejected',
                // enuOfflinePaymentStatus::Removed        => 'offlinePaymentStatusChangedTo_Removed',
            ];

            if (isset($status_to_message_key[$this->ofpStatus])) {
                $userFullName = [];
                if ((empty($this->owner->usrGender) == false)
                    && ($this->owner->usrGender != enuGender::NotSet)
                )
                    $userFullName[] = enuGender::getAbrLabel($this->owner->usrGender);
                if (empty($this->owner->usrFirstName) == false)
                    $userFullName[] = $this->owner->usrFirstName;
                if (empty($this->owner->usrLastName) == false)
                    $userFullName[] = $this->owner->usrLastName;
                $userFullName = implode(' ', $userFullName);

                MessageModel::saveNewMessage(
                    $this->ofpOwnerUserID,
                    $status_to_message_key[$this->ofpStatus],
                    $this->owner->usrMobile,
                    [
                        'user'   => $userFullName,
                        'amount' => $this->ofpAmount,
                        'status' => enuOfflinePaymentStatus::getLabel($this->ofpStatus),
                    ],
                    'offlinepayment:save',
                    false
                );
            }
        }

        return true;
    }

    public function doAccept()
    {
        Yii::$app->paymentManager->approveOfflinePayment($this);
    }

    public function doReject($reasons = null, $comment = null)
    {
        Yii::$app->paymentManager->rejectOfflinePayment($this, $reasons, $comment);
    }
}
