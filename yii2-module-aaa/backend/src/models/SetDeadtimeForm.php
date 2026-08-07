<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\models;

use yii\base\Model;
use yii\web\NotFoundHttpException;

class SetDeadtimeForm extends Model
{
    public $userID;
    public $deadAt;

    public function rules()
    {
        return [
            ['userID', 'required'],
            ['deadAt', 'required'],
        ];
    }

    public function save()
    {
        if ($this->validate() == false)
            return false;

        $userModel = UserModel::findOne([
            'usrID' => $this->userID
        ]);

        if (!$userModel)
            throw new NotFoundHttpException("user not found");

        $userModel->usrDeadAt = $this->deadAt;

        return $userModel->save();
    }
}
