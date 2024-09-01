<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\common\helpers\ArrayHelper;
use shopack\base\common\helpers\StringHelper;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\grid\GridView;
use shopack\aaa\common\enums\enuBasicDefinitionType;
use shopack\aaa\common\enums\enuOfflinePaymentStatus;
use shopack\aaa\common\enums\enuOfflinePaymentType;
use shopack\aaa\frontend\common\models\BasicDefinitionModel;
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

  $rejectReasons = ArrayHelper::map(BasicDefinitionModel::find()
    ->where(['bdfType' => enuBasicDefinitionType::OfflinePaymentRejectReason])
    ->noLimit()
    ->asArray()
    ->all(),
    'bdfID',
    'bdfName'
  );

  $columns = [
    [
      'class' => 'kartik\grid\SerialColumn',
    ],
    [
      'class' => 'shopack\base\frontend\common\widgets\grid\ExpandRowColumn',
      'value' => function ($model, $key, $index, $column) {
        return GridView::ROW_COLLAPSED;
        // this bahaviour moved to gridview::run for covering initialize error
        // return ($selected_adngrpID == $model->adngrpID ? GridView::ROW_EXPANDED : GridView::ROW_COLLAPSED);
      },
      'detail' => function ($model) use($rejectReasons) {
        $rows = [];

        // $rows[] = [$model->getAttributeLabel('ofpPayer'), $model->ofpPayer];
        $rows[] = [$model->getAttributeLabel('ofpSourceCartNumber'), $model->ofpSourceCartNumber];

        if (empty($model->ofpRejectReasonIDs) == false) {
          $reasons = [];

          foreach ($model->ofpRejectReasonIDs as $r) {
            if (isset($rejectReasons[$r]))
              $reasons[] = $rejectReasons[$r];
          }

          if (empty($reasons) == false) {
            $rows[] = [$model->getAttributeLabel('ofpRejectReasonIDs'), implode(' - ', $reasons)];
          }
        }

        // if (empty($model->ofpComment) == false)
        $rows[] = [$model->getAttributeLabel('ofpComment'), $model->ofpComment];

        return Html::asTable($rows);
      },
    ],
    [
      'attribute' => 'ofpID',
      'format' => 'raw',
      'value' => function ($model, $key, $index, $widget) {
        return Html::a($model->ofpID, ['view', 'id' => $model->ofpID]);
      },
    ],
    [
      'attribute' => 'ofpImageFileID',
      // 'label' => '',
      'format' => 'raw',
      'value' => function ($model, $key, $index, $widget) {
        return Html::asUploadedImage($model->imageFile);
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
    'ofpAmount:toman',
    'ofpPayDate:jalaliWithTime',
    'ofpPayer',
    [
      'class' => \shopack\base\frontend\common\widgets\grid\EnumDataColumn::class,
      'enumClass' => enuOfflinePaymentType::class,
      'attribute' => 'ofpType',
    ],
    // 'ofpDestCartID',
    // 'ofpDestAccountNumber',
    // 'ofpDestISBN',
    // 'ofpDestBankID',
    // 'ofpDestName',
    // 'ofpDueDate:jalali',
    'ofpTrackNumber',
    'ofpReferenceNumber',
    [
      'attribute' => 'ofpWalletID',
      'format' => 'raw',
      'value' => function ($model, $key, $index, $widget) {
        return Html::a($model->wallet->walID . ' - ' . $model->wallet->walName, ['/aaa/wallet/view', 'id' => $model->ofpWalletID]);
      },
    ],
    // 'ofpSourceCartNumber',
    [
      'class' => \shopack\base\frontend\common\widgets\grid\EnumDataColumn::class,
      'enumClass' => enuOfflinePaymentStatus::class,
      'attribute' => 'ofpStatus',
    ],
    // [
    //   'attribute' => 'ofpComment',
    //   'value' => function ($model, $key, $index, $widget) {
    //     if (empty($model->ofpComment))
    //       return '';

    //     return 'دارد';
    //   },
    // ],
    [
      'class' => \shopack\base\frontend\common\widgets\ActionColumn::class,
      'header' => OfflinePaymentModel::canCreate() ? Html::createButton(null, [
        'create',
        'ofpOwnerUserID' => $ofpOwnerUserID ?? $_GET['ofpOwnerUserID'] ?? null,
      ], [
        'data-popup-size' => 'lg',
        'title' => Yii::t('aaa', 'Create Offline Payment'),
      ]) : Yii::t('app', 'Actions'),
      'updateOptions' => [
        'modal' => true,
        'data-popup-size' => 'lg',
      ],
      'template' => '{accept} {reject}<br>{update} {delete}{undelete}',
      'buttons' => [
        'accept' => function ($url, $model, $key) {
          return Html::a(Yii::t('aaa', 'Approve'), [
            'accept',
            'id' => $model->ofpID,
          ], [
            'class' => 'btn btn-sm btn-success',
            'modal' => true,
            'title' => Yii::t('aaa', 'Approve'),
          ]);
        },
        'reject' => function ($url, $model, $key) {
          return Html::a(Yii::t('aaa', 'Reject'), [
            'reject',
            'id' => $model->ofpID,
          ], [
            'class' => 'btn btn-sm btn-warning',
            'modal' => true,
            'title' => Yii::t('aaa', 'Reject'),
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
