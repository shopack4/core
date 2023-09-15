<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\common\widgets\grid\GridView;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\common\helpers\StringHelper;
use shopack\aaa\common\enums\enuUploadFileStatus;
use shopack\aaa\frontend\common\models\UploadFileModel;
?>

<?php
  $uflOwnerUserID = Yii::$app->request->queryParams['uflOwnerUserID'] ?? null;
?>

<?php
	// echo Alert::widget(['key' => 'shoppingcart']);

	if (isset($statusReport))
		echo $statusReport;

    // (is_array($statusReport) ? Html::icon($statusReport[0], ['plugin' => 'glyph']) . ' ' . $statusReport[1] : $statusReport);

  $columns = [
    [
      'class' => 'kartik\grid\SerialColumn',
    ],
    'uflID',
    [
      'attribute' => 'uflImageFileID',
      // 'label' => '',
      'format' => 'raw',
      'value' => function ($model, $key, $index, $widget) {
        if (empty($model->fullFileUrl))
          return Yii::t('aaa', '...');
        elseif ($model->isImage())
          return Html::img($model->fullFileUrl, ['style' => ['width' => '50px']]);
        else
          return Html::a(Yii::t('app', 'Download'), $model->fullFileUrl);
      },
    ],
  ];

  if (empty($uflOwnerUserID)) {
    $columns = array_merge($columns, [
      [
        'class' => \shopack\aaa\frontend\common\widgets\grid\UserDataColumn::class,
        'attribute' => 'uflOwnerUserID',
        // 'label' => 'مالک',
        'format' => 'raw',
        'value' => function($model) {
          return Html::a($model->owner->displayName(), Yii::$app->getModule('aaa')->createUserViewUrl($model->uflOwnerUserID));
        },
      ],
    ]);
  }

  $columns = array_merge($columns, [
    [
      'class' => \shopack\base\frontend\common\widgets\grid\EnumDataColumn::class,
      'enumClass' => enuUploadFileStatus::class,
      'attribute' => 'uflStatus',
    ],
    /*[
      'class' => \shopack\base\frontend\common\widgets\ActionColumn::class,
      'header' => UploadFileModel::canCreate() ? Html::createButton(null, [
        'create',
        'uflOwnerUserID' => $uflOwnerUserID ?? $_GET['uflOwnerUserID'] ?? null,
      ]) : Yii::t('app', 'Actions'),
      // 'template' => '{accept} {reject} {update} {delete}{undelete}',
      'template' => '{accept} {reject} {delete}{undelete}',

      'buttons' => [
        'accept' => function ($url, $model, $key) {
          return Html::confirmButton(Yii::t('aaa', 'Approve'), [
            'approve',
            'id' => $model->uflID,
          ], Yii::t('aaa', 'Are you sure you want to APPROVE this item?'), [
            'class' => 'btn btn-sm btn-success',
            'ajax' => 'post',
          ]);
        },
        'reject' => function ($url, $model, $key) {
          return Html::confirmButton(Yii::t('aaa', 'Reject'), [
            'reject',
            'id' => $model->uflID,
          ], Yii::t('aaa', 'Are you sure you want to REJECT this item?'), [
            'class' => 'btn btn-sm btn-warning',
            'ajax' => 'post',
          ]);
        },
      ],

      'visibleButtons' => [
        'update' => function ($model, $key, $index) {
          return $model->canUpdate();
        },
        'delete' => function ($model, $key, $index) {
          return $model->canDelete();
        },
        'undelete' => function ($model, $key, $index) {
          return $model->canUndelete();
        },
        'accept' => function ($model, $key, $index) {
          return $model->canAccept();
        },
        'reject' => function ($model, $key, $index) {
          return $model->canReject();
        },
      ],
    ],*/
    [
      'attribute' => 'rowDate',
      'noWrap' => true,
      'format' => 'raw',
      'label' => 'ایجاد / ویرایش',
      'value' => function($model) {
        return Html::formatRowDates(
          $model->uflCreatedAt,
          $model->createdByUser,
          $model->uflUpdatedAt,
          $model->updatedByUser,
          $model->uflRemovedAt,
          $model->removedByUser,
        );
      },
    ],
  ]);

  echo GridView::widget([
    'id' => StringHelper::generateRandomId(),
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $columns,
  ]);

?>
