<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\common\helpers\StringHelper;
use shopack\base\frontend\helpers\Html;
use shopack\base\frontend\widgets\grid\GridView;
use shopack\aaa\common\enums\enuOnlinePaymentStatus;
use shopack\aaa\common\enums\enuVoucherType;

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
      'onpID',
      [
        'attribute' => 'onpAmount',
        'format' => 'toman',
        'contentOptions' => [
          'class' => ['text-nowrap', 'tabular-nums'],
        ],
      ],
      // 'onpGatewayID',
      [
        'attribute' => 'onpVoucherID',
        'value' => function($model) {
          if (empty($model->onpVoucherID))
            return null;
          return $model->onpVoucherID . ' - ' . enuVoucherType::getLabel($model->voucher->vchType);
        },
      ],
      [
        'attribute' => 'onpWalletID',
        'value' => function($model) {
          if (empty($model->onpWalletID))
            return null;
          return Yii::t('app', $model->wallet->walName);
        },
      ],
      [
        'attribute' => 'onpTrackNumber',
        'contentOptions' => [
          'class' => ['small'],
        ],
      ],
      [
        'attribute' => 'onpRRN',
        'contentOptions' => [
          'class' => ['small'],
        ],
      ],
      [
        'class' => \shopack\base\frontend\widgets\grid\EnumDataColumn::class,
        'enumClass' => enuOnlinePaymentStatus::class,
        'attribute' => 'onpStatus',
      ],
      [
        'attribute' => 'onpCreatedAt',
        'format' => 'jalaliWithTime',
        'contentOptions' => [
          'class' => ['text-nowrap', 'small'],
        ],
      ],
      [
        'attribute' => 'onpUpdatedAt',
        'format' => 'jalaliWithTime',
        'contentOptions' => [
          'class' => ['text-nowrap', 'small'],
        ],
      ],
      [
        'class' => \shopack\base\frontend\widgets\ActionColumn::class,
        'header' => Yii::t('app', 'Actions'),
        'template' => false,
      ],
    ],
  ]);

?>
