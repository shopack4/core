<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\models;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use shopack\base\common\db\DbExpression;
use shopack\aaa\common\enums\enuGender;
use shopack\aaa\common\enums\enuOnlinePaymentStatus;
use shopack\aaa\backend\classes\AAAActiveRecord;

class OnlinePaymentModel extends AAAActiveRecord
{
    use \shopack\aaa\common\models\OnlinePaymentModelTrait;

    use \shopack\base\common\db\SoftDeleteActiveRecordTrait;
    public function initSoftDelete()
    {
        $this->softdelete_RemovedStatus  = enuOnlinePaymentStatus::Removed;
        // $this->softdelete_StatusField    = 'onpStatus';
        $this->softdelete_RemovedAtField = 'onpRemovedAt';
        $this->softdelete_RemovedByField = 'onpRemovedBy';
    }

    public static function tableName()
    {
        return '{{%AAA_OnlinePayment}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => \shopack\base\common\behaviors\RowDatesAttributesBehavior::class,
                'createdAtAttribute' => 'onpCreatedAt',
                'createdByAttribute' => 'onpCreatedBy',
                'updatedAtAttribute' => 'onpUpdatedAt',
                'updatedByAttribute' => 'onpUpdatedBy',
            ],
        ];
    }

    public function insert($runValidation = true, $attributes = null)
    {
        if (empty($this->onpUUID))
            $this->onpUUID = new DbExpression('UUID()'); //Uuid::uuid4()->toString();
        // $this->onpUUID = Yii::$app->security->generateRandomString();

        return parent::insert($runValidation, $attributes);
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        $oldStatus = NULL;
        $dirtyAttributes = $this->getDirtyAttributes(['mbrknnStatus']);
        if (empty($dirtyAttributes) == false)
            $oldStatus = $this->oldAttributes['onpStatus'] ?? null;

        /*************************************************/
        if (parent::save($runValidation, $attributeNames) == false)
            return false;
        /*************************************************/

        //status changed
        if ($oldStatus != $this->onpStatus) {
            $status_to_message_key = [
                enuOnlinePaymentStatus::New     => 'onlinePaymentStatusChangedTo_New',
                // enuOnlinePaymentStatus::Pending => 'onlinePaymentStatusChangedTo_Pending',
                enuOnlinePaymentStatus::Paid    => 'onlinePaymentStatusChangedTo_Paid',
                // enuOnlinePaymentStatus::Error   => 'onlinePaymentStatusChangedTo_Error',
                // enuOnlinePaymentStatus::Removed => 'onlinePaymentStatusChangedTo_Removed',
            ];

            if (isset($status_to_message_key[$this->onpStatus])) {
                $userFullName = [];
                if ((empty($this->voucher->owner->usrGender) == false)
                    && ($this->voucher->owner->usrGender != enuGender::NotSet)
                )
                    $userFullName[] = enuGender::getAbrLabel($this->voucher->owner->usrGender);
                if (empty($this->voucher->owner->usrFirstName) == false)
                    $userFullName[] = $this->voucher->owner->usrFirstName;
                if (empty($this->voucher->owner->usrLastName) == false)
                    $userFullName[] = $this->voucher->owner->usrLastName;
                $userFullName = implode(' ', $userFullName);

                MessageModel::saveNewMessage(
                    $this->voucher->vchOwnerUserID,
                    $status_to_message_key[$this->onpStatus],
                    $this->voucher->owner->usrMobile,
                    [
                        'user'   => $userFullName,
                        'amount' => $this->onpAmount,
                        'status' => enuOnlinePaymentStatus::getLabel($this->onpStatus),
                    ],
                    'onlinepayment:save',
                    false
                );
            }
        }

        return true;
    }
}
