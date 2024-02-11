<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\common\widgets\DetailView;
use shopack\base\frontend\common\helpers\Html;
?>

<?php
  $hasDiscount = (empty($model->voucher['vchItemsDiscounts']) == false);
  $hasVAT = (empty($model->voucher['vchItemsVATs']) == false);
  $hasPhysical = ($model->physicalCount > 0);

  $attributes = [];

  if ($hasDiscount || $hasVAT) {
    $attributes = array_merge($attributes, [
      [
        'attribute' => 'vchAmount',
        'format' => 'toman',
        'value' => $model->voucher['vchAmount'],
      ]
    ]);
  }

  if ($hasDiscount) {
    $attributes = array_merge($attributes, [
      [
        'attribute' => 'vchItemsDiscounts',
        'format' => 'toman',
        'value' => $model->voucher['vchItemsDiscounts'],
      ]
    ]);
  }

  if ($hasVAT) {
    $attributes = array_merge($attributes, [
      [
        'attribute' => 'vchItemsVATs',
        'format' => 'toman',
        'value' => $model->voucher['vchItemsVATs'],
      ]
    ]);
  }

  if ($hasDiscount || $hasVAT || $hasPhysical) {
    $attributes = array_merge($attributes, [
      [
        'attribute' => 'vchTotalAmount',
        'format' => 'toman',
        'value' => $model->voucher['vchTotalAmount'],
      ]
    ]);
  }

  if ($hasPhysical) {
    $attributes = array_merge($attributes, [
      [
        'attribute' => 'vchDeliveryAmount',
        'format' => 'toman',
        'value' => $model['deliveryAmount'],
      ],
    ]);
  }

  if (empty($model->voucher['vchTotalPaid']) == false) {
    $attributes = array_merge($attributes, [
      [
        'attribute' => 'vchTotalPaid',
        'format' => 'toman',
        'value' => $model->voucher['vchTotalPaid'],
      ],
    ]);
  }

  $attributes = array_merge($attributes, [
    [
      'attribute' => 'walletamount',
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
?>
