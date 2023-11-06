<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\common\widgets\grid\GridView;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\common\helpers\StringHelper;
use shopack\aaa\common\enums\enuOfflinePaymentStatus;
use shopack\aaa\frontend\common\models\OfflinePaymentModel;
?>

<?php
  $ofpOwnerUserID = Yii::$app->request->queryParams['ofpOwnerUserID'] ?? null;
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
    [
      'class' => 'kartik\grid\ExpandRowColumn',
      'value' => function ($model, $key, $index, $column) {
        return GridView::ROW_COLLAPSED;
        // this bahaviour moved to gridview::run for covering initialize error
        // return ($selected_adngrpID == $model->adngrpID ? GridView::ROW_EXPANDED : GridView::ROW_COLLAPSED);
      },
      'expandOneOnly' => true,
      'detailAnimationDuration' => 150,
      'detail' => function ($model) {
        if (empty($model->ofpComment))
          return '';

        return $model->ofpComment;
      },
    ],
    'ofpID',
    [
      'attribute' => 'ofpImageFileID',
      // 'label' => '',
      'format' => 'raw',
      'value' => function ($model, $key, $index, $widget) {
        if ($model->ofpImageFileID == null)
          return '';
        elseif (empty($model->imageFile->fullFileUrl))
          return Yii::t('aaa', '...');
        elseif ($model->imageFile->isImage())
          return Html::img($model->imageFile->fullFileUrl, ['style' => ['width' => '50px']]);
        else
          return Html::a(Yii::t('app', 'Download'), $model->imageFile->fullFileUrl);
      },
    ],
  ];

  if (empty($ofpOwnerUserID)) {
    $columns = array_merge($columns, [
      [
        'class' => \shopack\aaa\frontend\common\widgets\grid\UserDataColumn::class,
        'attribute' => 'ofpOwnerUserID',
        // 'label' => 'مالک',
        'format' => 'raw',
        'value' => function($model) {
          return Html::a($model->owner->displayName(), Yii::$app->getModule('aaa')->createUserViewUrl($model->ofpOwnerUserID));
        },
      ],
    ]);
  }

  $columns = array_merge($columns, [
    'ofpBankOrCart',
    'ofpTrackNumber',
    'ofpReferenceNumber',
    'ofpPayDate:jalaliWithTime',
    'ofpAmount:toman',
    'ofpPayer',
    'ofpSourceCartNumber',
    [
      'attribute' => 'ofpWalletID',
      'format' => 'raw',
      'value' => function ($model, $key, $index, $widget) {
        return Html::a($model->wallet->walName, ['/aaa/wallet/view', 'id' => $model->ofpWalletID]);
      },
    ],
    [
      'class' => \shopack\base\frontend\common\widgets\grid\EnumDataColumn::class,
      'enumClass' => enuOfflinePaymentStatus::class,
      'attribute' => 'ofpStatus',
    ],
    [
      'attribute' => 'ofpComment',
      'value' => function ($model, $key, $index, $widget) {
        if (empty($model->ofpComment))
          return '';

        return 'دارد';
      },
    ],
    [
      'class' => \shopack\base\frontend\common\widgets\ActionColumn::class,
      'header' => OfflinePaymentModel::canCreate() ? Html::createButton(null, [
        'create',
        'ofpOwnerUserID' => $ofpOwnerUserID ?? $_GET['ofpOwnerUserID'] ?? null,
      ]) : Yii::t('app', 'Actions'),
      // 'template' => '{accept} {reject} {update} {delete}{undelete}',
      'template' => '{accept} {reject} {delete}{undelete}',

      'buttons' => [
        'accept' => function ($url, $model, $key) {
          return Html::confirmButton(Yii::t('aaa', 'Approve'), [
            'approve',
            'id' => $model->ofpID,
          ], Yii::t('aaa', 'Are you sure you want to APPROVE this item?'), [
            'class' => 'btn btn-sm btn-success',
            'ajax' => 'post',
          ]);
        },
        'reject' => function ($url, $model, $key) {
          return Html::confirmButton(Yii::t('aaa', 'Reject'), [
            'reject',
            'id' => $model->ofpID,
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
    ],
    [
      'attribute' => 'rowDate',
      'noWrap' => true,
      'format' => 'raw',
      'label' => 'ایجاد / ویرایش',
      'value' => function($model) {
        return Html::formatRowDates(
          $model->ofpCreatedAt,
          $model->createdByUser,
          $model->ofpUpdatedAt,
          $model->updatedByUser,
          $model->ofpRemovedAt,
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
