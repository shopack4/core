<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\adminpanel\models;

use Yii;
use yii\base\Model;
use yii\web\UnprocessableEntityHttpException;
use shopack\base\common\helpers\HttpHelper;

class SetDeadtimeForm extends Model
{
    public $userID;
    public $deadAt;

    public function rules()
    {
        return [
            ['userID', 'integer'],
            [['deadAt'], 'safe'],
            [['userID', 'deadAt'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'userID' => Yii::t('aaa', 'User'),
            'deadAt' => Yii::t('aaa', 'Dead At'),
        ];
    }

    public function process()
    {
        if ($this->validate() == false)
            throw new UnprocessableEntityHttpException(implode("\n", $this->getFirstErrors()));

        $apiResponse = HttpHelper::callApi(
            'aaa/user/set-deadtime',
            HttpHelper::METHOD_POST,
            [
                'id' => $this->userID,
            ],
            [
                'deadAt' => $this->deadAt,
            ]
        );

        HttpHelper::throwApiResponseIfFailed($apiResponse, 'aaa');

        return true;
    }
}
