<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\common\helpers\StringHelper;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\grid\GridView;
use shopack\aaa\common\enums\enuWalletTransactionStatus;
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
      'wtrID',
      [
        'attribute' => 'wtrWalletID',
        'format' => 'raw',
        'value' => function($model) {
          return Html::a(Yii::t('app', $model->wallet->walID . ' - ' . $model->wallet->walName), [
            '/aaa/wallet/view',
            'id' => $model->wallet->walID,
          ]);
        },
      ],
      [
        'attribute' => 'wtrVoucherID',
        'format' => 'raw',
        'value' => function($model) {
          if (empty($model->wtrVoucherID))
            return null;

          if ($model->voucher->vchType == enuVoucherType::Invoice)
            return Html::a($model->wtrVoucherID . ' - ' . enuVoucherType::getLabel($model->voucher->vchType), ['/aaa/order/view', 'id' => $model->wtrVoucherID]);

          return $model->wtrVoucherID . ' - ' . enuVoucherType::getLabel($model->voucher->vchType);
        },
      ],
      [
        'attribute' => 'vchOriginVoucherID',
        'format' => 'raw',
        'value' => function($model) {
          if (empty($model->voucher->vchOriginVoucherID))
            return null;

          $link = ($model->voucher->originVoucher->vchType == enuVoucherType::Invoice ? '/aaa/order/view' : '/aaa/voucher/view');

          return Html::a($model->voucher->originVoucher->vchID . ' - ' . enuVoucherType::getLabel($model->voucher->originVoucher->vchType), [$link, 'id' => $model->voucher->originVoucher->vchID]);
        },
      ],
      [
        'attribute' => 'wtrOnlinePaymentID',
        'format' => 'raw',
        'value' => function($model) {
          if (empty($model->wtrOnlinePaymentID))
            return null;

          return Html::a($model->wtrOnlinePaymentID, ['/aaa/online-payment/view', 'id' => $model->wtrOnlinePaymentID]);
        },
      ],
      [
        'attribute' => 'wtrOfflinePaymentID',
        'format' => 'raw',
        'value' => function($model) {
          if (empty($model->wtrOfflinePaymentID))
            return null;

          return Html::a($model->wtrOfflinePaymentID, ['/aaa/offline-payment/view', 'id' => $model->wtrOfflinePaymentID]);
        },
      ],
      [
        'attribute' => 'credit',
        'label' => 'واریز',
        'contentOptions' => [
          'class' => ['text-nowrap', 'tabular-nums'],
        ],
        'value' => function($model) {
          if ($model->wtrAmount > 0)
            return Yii::$app->formatter->asToman($model->wtrAmount);
          return '';
        },
      ],
      [
        'attribute' => 'debit',
        'label' => 'برداشت',
        'contentOptions' => [
          'class' => ['text-nowrap', 'tabular-nums'],
        ],
        'value' => function($model) {
          if ($model->wtrAmount < 0)
            return Yii::$app->formatter->asToman(abs($model->wtrAmount));
          return '';
        },
      ],

      // [
      //   'class' => \shopack\base\frontend\common\widgets\grid\EnumDataColumn::class,
      //   'enumClass' => enuWalletTransactionStatus::class,
      //   'attribute' => 'wtrStatus',
      // ],
      [
        'attribute' => 'wtrCreatedAt',
        'format' => 'jalaliWithTime',
        'contentOptions' => [
          'class' => ['text-nowrap', 'small'],
        ],
      ],
      [
        'class' => \shopack\base\frontend\common\widgets\ActionColumn::class,
        'header' => Yii::t('app', 'Actions'),
        'template' => false,
      ],
    ],
  ]);

?>
