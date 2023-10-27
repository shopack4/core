<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\common\helpers\StringHelper;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\grid\GridView;
use shopack\aaa\common\enums\enuOfflinePaymentStatus;
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
      'ofpID',
      'ofpBankOrCart',
      'ofpPayDate:jalaliWithTime',
      [
        'attribute' => 'ofpTrackNumber',
        'contentOptions' => [
          'class' => ['small'],
        ],
      ],
      [
        'attribute' => 'ofpReferenceNumber',
        'contentOptions' => [
          'class' => ['small'],
        ],
      ],
      [
        'attribute' => 'ofpAmount',
        'format' => 'toman',
        'contentOptions' => [
          'class' => ['text-nowrap', 'tabular-nums'],
        ],
      ],
      'ofpPayer',
			'ofpSourceCartNumber',
      [
        'attribute' => 'ofpWalletID',
        'value' => function($model) {
          if (empty($model->ofpWalletID))
            return null;
          return Yii::t('app', $model->wallet->walName);
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
