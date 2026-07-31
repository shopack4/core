<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\common\widgets\grid\GridView;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\common\helpers\StringHelper;
use shopack\aaa\common\enums\enuUploadQueueStatus;
use shopack\aaa\frontend\common\models\UploadQueueModel;
?>

<?php
if (isset($statusReport))
    echo $statusReport;

$columns = [
    [
        'class' => 'kartik\grid\SerialColumn',
    ],
    'uquID',
    [
        'attribute' => 'uquFileID',
        // 'label' => '',
        'format' => 'raw',
        'value' => function ($model, $key, $index, $widget) {
            return Html::asUploadedImage($model->uploadFile, '50px', false);
        },
    ],
    // 'uquFileID',
    [
        'attribute' => 'uquGatewayID',
        'value' => function ($model) {
            return $model->gateway->gtwName;
        },
    ],
    'uquLockedAt:jalaliWithTime',
    'uquLockedBy',
    'uquLastTryAt:jalaliWithTime',
    'uquStoredAt:jalaliWithTime',
    'uquResult',
    [
        'class' => \shopack\base\frontend\common\widgets\grid\EnumDataColumn::class,
        'enumClass' => enuUploadQueueStatus::class,
        'attribute' => 'uquStatus',
    ],
    [
        'attribute' => 'rowDate',
        'noWrap' => true,
        'format' => 'raw',
        'label' => 'ایجاد / ویرایش',
        'value' => function ($model) {
            return Html::formatRowDates(
                $model->uquCreatedAt,
                $model->createdByUser,
                $model->uquUpdatedAt,
                $model->updatedByUser,
                $model->uquRemovedAt,
                $model->removedByUser,
            );
        },
    ],
];

echo GridView::widget([
    'id' => StringHelper::generateRandomId(),
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $columns,
]);

?>
