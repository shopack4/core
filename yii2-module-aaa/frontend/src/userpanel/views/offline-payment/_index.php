<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\common\helpers\StringHelper;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\grid\GridView;
use shopack\aaa\common\enums\enuOfflinePaymentStatus;
use shopack\aaa\common\enums\enuOfflinePaymentType;
use shopack\aaa\frontend\common\models\OfflinePaymentModel;
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
          return Html::asUploadedImage($model->imageFile, '50px', false);
        },
      ],
      [
        'attribute' => 'ofpAmount',
        'format' => 'toman',
        'contentOptions' => [
          'class' => ['text-nowrap', 'tabular-nums'],
        ],
      ],
      'ofpPayDate:jalaliWithTime',
      'ofpPayer',
      [
        'class' => \shopack\base\frontend\common\widgets\grid\EnumDataColumn::class,
        'enumClass' => enuOfflinePaymentType::class,
        'attribute' => 'ofpType',
      ],
      // 'ofpDestCartNumber',
      // 'ofpDestAccountNumber',
      // 'ofpDestISBN',
      // 'ofpDestBankID',
      // 'ofpDestName',
      // 'ofpDueDate:jalali',
      // [
      //   'attribute' => 'ofpTrackNumber',
      //   'contentOptions' => [
      //     'class' => ['small'],
      //   ],
      // ],
      // [
      //   'attribute' => 'ofpReferenceNumber',
      //   'contentOptions' => [
      //     'class' => ['small'],
      //   ],
      // ],
			// 'ofpSourceCartNumber',
      [
        'attribute' => 'ofpWalletID',
        'format' => 'raw',
        'value' => function($model) {
          return Html::a($model->wallet->walID . ' - ' . $model->wallet->walName, ['/aaa/wallet/view', 'id' => $model->ofpWalletID]);
        },
      ],
      [
        'class' => \shopack\base\frontend\common\widgets\grid\EnumDataColumn::class,
        'enumClass' => enuOfflinePaymentStatus::class,
        'attribute' => 'ofpStatus',
      ],
      [
        'attribute' => 'ofpCreatedAt',
        'format' => 'jalaliWithTime',
        'contentOptions' => [
          'class' => ['text-nowrap', 'small'],
        ],
      ],
      [
        'attribute' => 'ofpUpdatedAt',
        'format' => 'jalaliWithTime',
        'contentOptions' => [
          'class' => ['text-nowrap', 'small'],
        ],
      ],
      [
        'class' => \shopack\base\frontend\common\widgets\ActionColumn::class,
        'header' => OfflinePaymentModel::canCreate() ? Html::createButton(null, null, [
          'data-popup-size' => 'lg',
        ]) : Yii::t('app', 'Actions'),
        'template' => '{update} {delete}{undelete}',
        'updateOptions' => [
          'modal' => true,
          'data-popup-size' => 'lg',
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
        ],
      ]
    ],
  ]);

?>
