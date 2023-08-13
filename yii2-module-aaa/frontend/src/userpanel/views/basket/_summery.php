<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\widgets\DetailView;
use shopack\base\frontend\helpers\Html;
?>

<?php
  // $model = [
  //   'totalPrices'     => $totalPrices,
  //   'totalDiscounts'  => $totalDiscounts,
  //   'totalTaxes'      => $totalTaxes,
  //   'total'           => $total,
  // ];

  $attributes = [
    [
      'attribute' => 'totalPrices',
      'label' => 'جمع کل',
      'format' => 'toman',
      'value' => $model['totalPrices'],
    ],
    [
      'attribute' => 'totalDiscounts',
      'label' => 'تخفیف',
      'format' => 'toman',
      'value' => $model['totalDiscounts'],
    ],
    [
      'attribute' => 'totalTaxes',
      'label' => 'مالیات',
      'format' => 'toman',
      'value' => $model['totalTaxes'],
    ],
  ];

  if ($model->paid > 0) {
    $attributes = array_merge($attributes, [
      // [
      //   'attribute' => 'vchtotal',
      //   'label' => 'جمع کل',
      //   'format' => 'toman',
      //   'value' => $model['vchtotal'],
      // ],
      [
        'attribute' => 'paid',
        'label' => 'پرداخت شده',
        'format' => 'toman',
        'value' => $model['paid'],
      ],
    ]);
  }

  $attributes = array_merge($attributes, [
    [
      'attribute' => 'walletamount',
      'label' => 'برداشت از کیف پول',
      'format' => 'raw',
      'value' => Html::span('', ['id' => 'spn-walletamount']),
      'rowOptions' => [
        'id' => 'row-walletamount',
        'class' => 'table-active',
        'style' => 'display:none',
      ],
    ],
    [
      'attribute' => 'total',
      'label' => 'قابل پرداخت',
      'format' => 'raw',
      'value' => Html::span(Yii::$app->formatter->asToman($model['total']), ['id' => 'spn-total']),
      'rowOptions' => [
        'class' => 'table-active',
      ],
    ],
  ]);

  echo DetailView::widget([
    'model' => $model,
    'enableEditMode' => false,
    // 'cols' => 2,
    // 'isVertical' => false,
    'striped' => false,
    'labelColOptions' => ['class' => ['w-50', 'text-nowrap']],
    'valueColOptions' => ['class' => ['w-50', 'text-nowrap']],
    'attributes' => $attributes,
  ]);
  // echo $model->voucher->vchAmount;
?>
