<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\controllers;

use Yii;
use shopack\base\backend\controller\BaseCrudController;
use shopack\aaa\backend\models\UploadQueueModel;

class UploadQueueController extends BaseCrudController
{
    public $modelClass = UploadQueueModel::class;

    public function permissions()
    {
        $checkOwner = function ($model): bool {
            return (($model != null) && ($model['uquUserID'] == Yii::$app->user->id));
        };

        return [
            'index'  => [
                'aaa/upload-queue/crud' => '0100',
                'filter' => function ($query) {
                    Yii::$app->user->assertIsNotGuest();
                    $query->andWhere(['uquUserID' => Yii::$app->user->id]);
                },
            ],
            'view'   => ['aaa/upload-queue/crud' => '0100', 'checker' => $checkOwner],
            // 'create' => ['aaa/upload-queue/crud' => '1000', 'checker' => $checkOwner],
            // 'update' => ['aaa/upload-queue/crud' => '0010', 'checker' => $checkOwner],
            // 'delete' => ['aaa/upload-queue/crud' => '0001', 'checker' => $checkOwner],
            // 'undelete' => ['aaa/upload-queue/undelete'],
        ];
    }

    public function queryAugmentaters()
    {
        return [
            'index' => function ($query) {
                $query
                    ->with('uploadFile')
                    ->with('gateway')
                    ->with('createdByUser')
                    ->with('updatedByUser')
                    ->with('removedByUser')
                ;
            },
            'view' => function ($query) {
                $query
                    ->with('uploadFile')
                    ->with('gateway')
                    ->with('createdByUser')
                    ->with('updatedByUser')
                    ->with('removedByUser')
                ;
            },
        ];
    }
}
