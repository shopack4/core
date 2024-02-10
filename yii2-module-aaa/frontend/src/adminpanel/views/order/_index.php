<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\common\helpers\StringHelper;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\grid\GridView;
use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\aaa\frontend\common\models\VoucherModel;
?>

<?php
  $vchOwnerUserID = Yii::$app->request->queryParams['vchOwnerUserID'] ?? null;
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
          'شرح',
          'تعداد',
          'مبلغ واحد',
          'تخفیف',
          'مبلغ کل',
        ]) . '</td></tr>';
        $vchItems = $model->vchItems;
        foreach ($vchItems as $k => $vchItem)
        {
          $result[] = '<tr><td>' . implode('</td><td>', [
            $k + 1,
            $vchItem['desc'],
            Yii::$app->formatter->asDecimal($vchItem['qty']),
            Yii::$app->formatter->asToman($vchItem['unitPrice']),
            Yii::$app->formatter->asToman($vchItem['discount'] ?? 0),
            Yii::$app->formatter->asToman(($vchItem['qty'] * $vchItem['unitPrice']) - ($vchItem['discount'] ?? 0)),
          ]) . '</td></tr>';
        }
        return '<table class="table table-bordered table-striped">' . implode('', $result) . '</table>';
      },
    ],
    'vchID',
  ];

  if (empty($vchOwnerUserID)) {
    $columns = array_merge($columns, [
      [
        'class' => \shopack\aaa\frontend\common\widgets\grid\UserDataColumn::class,
        'attribute' => 'vchOwnerUserID',
        'format' => 'raw',
        'value' => function ($model, $key, $index, $widget) {
          return Html::a($model->owner->displayName(), Yii::$app->getModule('aaa')->createUserViewUrl($model->vchOwnerUserID));
        },
      ],
    ]);
  }

  $columns = array_merge($columns, [
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
      'class' => \shopack\base\frontend\common\widgets\grid\LookupDataColumn::class,
      'lookupData' => enuVoucherStatus::getForBasketList(),
      'attribute' => 'vchStatus',
    ],
    // [
    //   'class' => \shopack\base\frontend\common\widgets\ActionColumn::class,
    //   'header' => Yii::t('app', 'Actions'),
    //   'template' => false,
    // ],
    [
      'attribute' => 'rowDate',
      'noWrap' => true,
      'format' => 'raw',
      'label' => 'ایجاد / ویرایش',
      'value' => function($model) {
        return Html::formatRowDates(
          $model->vchCreatedAt,
          $model->createdByUser,
          $model->vchUpdatedAt,
          $model->updatedByUser,
          $model->vchRemovedAt,
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
