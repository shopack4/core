<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\models;

use Yii;
use shopack\base\common\db\DbExpression;
use shopack\aaa\backend\classes\AAAActiveRecord;
use shopack\aaa\common\enums\enuMessageTemplateStatus;
use shopack\aaa\backend\models\MessageTemplateModel;

class MessageModel extends AAAActiveRecord
{
    use \shopack\aaa\common\models\MessageModelTrait;

    public $sendNow = true;

    public static function tableName()
    {
        return '{{%AAA_Message}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => \shopack\base\common\behaviors\RowDatesAttributesBehavior::class,
                'createdAtAttribute' => 'msgCreatedAt',
                'createdByAttribute' => 'msgCreatedBy',
                // 'updatedAtAttribute' => 'msgUpdatedAt',
                // 'updatedByAttribute' => 'msgUpdatedBy',
            ],
        ];
    }

    public function insert($runValidation = true, $attributes = null)
    {
        $instanceID = Yii::$app->getInstanceID();

        if ($this->sendNow) {
            $this->msgLockedAt = new DbExpression("NOW()");
            $this->msgLockedBy = $instanceID;
        }

        $ret = parent::insert($runValidation, $attributes);

        if ($this->sendNow) {
            if ($ret) {
                try {
                    Yii::$app->messageManager->processQueue(1, $this->msgID);
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
        }

        return $ret;
    }

    public static function saveNewMessage(
        $msgUserID,
        $messageTemplateKey,
        $msgTarget,
        $msgInfo,
        $msgIssuer,
        $sendNow
    ) {
        //1: check template existance and status == 'A'
        $messageTemplateModel = MessageTemplateModel::findOne([
            'mstKey' => $messageTemplateKey,
            'mstStatus' => enuMessageTemplateStatus::Active
        ]);

        if (empty($messageTemplateModel))
            return NULL;

        //2: enqueue message
        $messageModel = new MessageModel;
        $messageModel->msgUserID  = $msgUserID;
        $messageModel->msgTypeKey = $messageTemplateKey;
        $messageModel->msgTarget  = $msgTarget;
        $messageModel->msgInfo    = $msgInfo;
        $messageModel->msgIssuer  = $msgIssuer;
        $messageModel->sendNow    = $sendNow;

        if ($messageModel->save())
            return $messageModel;

        return NULL;
    }
}
