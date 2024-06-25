<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\common\helpers\StringHelper;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\grid\GridView;
use shopack\aaa\common\enums\enuVoucherStatus;
?>

<?php
	// echo Alert::widget(['key' => 'shoppingcart']);

	if (isset($statusReport))
		echo $statusReport;

    // (is_array($statusReport) ? Html::icon($statusReport[0], ['plugin' => 'glyph']) . ' ' . $statusReport[1] : $statusReport);

  echo GridView::widget([
    'id' => StringHelper::generateRandomId(),
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,

    'columns' => [
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
        /*[
          {
            "key": "e219ad40-61c0-4e10-b264-9dd31d5ffe8a",
            "qty": 2,
            "desc": "عضویت خانه موسیقی از 1400/10/28 تا1402/10/27 به مدت 2 سال",
            "error": "Page not found.",
            "slbid": 1,
            "maxqty": 2,
            "slbkey": "mbrshp",
            "status": "E",
            "userid": 52,
            "qtystep": 0,
            "service": "mha",
            "slbinfo": {
              "endDate": "2024-01-17",
              "startDate": "2022-01-18"
            },
            "unitprice": 150000
          }
        ]*/
        'detail' => function ($model) {
          $result = [];
          $result[] = '<tr><td>' . implode('</td><td>', [
            '#',
            Yii::t('app', 'Description'),
            Yii::t('aaa', 'Qty'),
            Yii::t('aaa', 'Unit'),
            Yii::t('aaa', 'Unit Price'),
            Yii::t('aaa', 'Discount Amount'),
            Yii::t('aaa', 'VAT Amount'),
            Yii::t('aaa', 'Total Amount'),
          ]) . '</td></tr>';

          if (empty($model->vchItems) == false) {
            $vchItems = $model->vchItems;
            foreach ($vchItems as $k => $vchItem) {
              $result[] = '<tr><td>' . implode('</td><td>', [
                $k + 1,
                $vchItem['desc'],
                Yii::$app->formatter->asDecimal($vchItem['qty']),
                $vchItem['unit'],
                Yii::$app->formatter->asToman($vchItem['unitPrice']),
                Yii::$app->formatter->asToman($vchItem['discount'] ?? 0),
                Yii::$app->formatter->asToman($vchItem['vat'] ?? 0),
                Yii::$app->formatter->asToman($vchItem['totalPrice']),
              ]) . '</td></tr>';
            }
          }

          return '<table class="table table-bordered table-striped">' . implode('', $result) . '</table>';
        },
      ],
      [
        'attribute' => 'vchID',
        'format' => 'raw',
        'value' => function ($model, $key, $index, $widget) {
          return Html::a($model->vchID, ['view', 'id' => $model->vchID]);
        },
      ],
      [
        'attribute' => 'vchAmount',
        'format' => 'toman',
        'contentOptions' => [
          'class' => ['text-nowrap', 'tabular-nums'],
        ],
      ],
      [
        'attribute' => 'vchItemsDiscounts',
        'format' => 'toman',
        'contentOptions' => [
          'class' => ['text-nowrap', 'tabular-nums'],
        ],
      ],
      [
        'attribute' => 'vchItemsVATs',
        'format' => 'toman',
        'contentOptions' => [
          'class' => ['text-nowrap', 'tabular-nums'],
        ],
      ],
      [
        'attribute' => 'vchDeliveryAmount',
        'format' => 'toman',
        'contentOptions' => [
          'class' => ['text-nowrap', 'tabular-nums'],
        ],
      ],
      [
        'attribute' => 'vchTotalAmount',
        'format' => 'toman',
        'contentOptions' => [
          'class' => ['text-nowrap', 'tabular-nums'],
        ],
      ],
      [
        'class' => \shopack\base\frontend\common\widgets\grid\EnumDataColumn::class,
        'enumClass' => enuVoucherStatus::class,
        'attribute' => 'vchStatus',
      ],
      [
        'class' => \shopack\base\frontend\common\widgets\ActionColumn::class,
        'header' => Yii::t('app', 'Actions'),
        'template' => '{pay} {cancel}', // {reprocess}',
        'buttons' => [
          'pay' => function ($url, $model, $key) {
            return Html::a(Yii::t('aaa', 'Payment'), [
              'pay',
              'id' => $model->vchID,
            ], [
              'class' => 'btn btn-sm btn-success',
              'modal' => true,
            ]);
          },
          'cancel' => function ($url, $model, $key) {
            return Html::confirmButton(Yii::t('aaa', 'Cancel Order'), [
              'cancel',
              'id' => $model->vchID,
            ], Yii::t('aaa', 'Are you sure you want to cancel this order?'), [
              'class' => 'btn btn-sm btn-danger',
            ]);
          },
          // 'reprocess' => function ($url, $model, $key) {
          //   return Html::a(Yii::t('aaa', 'Reprocess'), [
          //     'reprocess',
          //     'id' => $model->vchID,
          //   ], [
          //     'class' => 'btn btn-sm btn-primary',
          //     'modal' => true,
          //   ]);
          // },
        ],
        'visibleButtons' => [
          'pay' => function ($model, $key, $index) {
            return $model->canPay();
          },
          'cancel' => function ($model, $key, $index) {
            return $model->canCancel();
          },
          // 'reprocess' => function ($model, $key, $index) {
            // return $model->canReprocess();
          // },
        ],
      ],
      [
        'attribute' => 'rowDate',
        'noWrap' => true,
        'format' => 'raw',
        'label' => 'ایجاد / ویرایش',
        'value' => function($model) {
          return Html::formatRowDates(
            $model->vchCreatedAt,
            null, //$model->createdByUser,
            $model->vchUpdatedAt,
            null, //$model->updatedByUser,
            $model->vchRemovedAt,
            null, //$model->removedByUser,
          );
        },
      ],
      // [
      //   'attribute' => 'vchCreatedAt',
      //   'format' => 'jalaliWithTime',
      //   'contentOptions' => [
      //     'class' => ['text-nowrap', 'small'],
      //   ],
      // ],
      // [
      //   'attribute' => 'vchUpdatedAt',
      //   'format' => 'jalaliWithTime',
      //   'contentOptions' => [
      //     'class' => ['text-nowrap', 'small'],
      //   ],
      // ],
    ],
  ]);

?>
