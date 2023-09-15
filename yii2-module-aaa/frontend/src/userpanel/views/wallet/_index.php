<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\common\widgets\grid\GridView;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\common\helpers\StringHelper;
use shopack\aaa\frontend\common\models\WalletModel;
use shopack\aaa\common\enums\enuWalletStatus;
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
      'walID',
      [
        'attribute' => 'walName',
        'format' => 'raw',
        'value' => function ($model, $key, $index, $widget) {
          return Html::a($model->walName, ['view', 'id' => $model->walID]);
        },
      ],
      [
        'attribute' => 'walRemainedAmount',
        'format' => 'toman',
        'contentOptions' => [
          'class' => ['text-nowrap', 'tabular-nums'],
        ],
      ],
      'walIsDefault:boolean',
      [
        'class' => \shopack\base\frontend\common\widgets\grid\EnumDataColumn::class,
        'enumClass' => enuWalletStatus::class,
        'attribute' => 'walStatus',
      ],
      [
        'class' => \shopack\base\frontend\common\widgets\ActionColumn::class,
        'header' => /*WalletModel::canCreate() ? Html::createButton(null, [
          'justForMe' => $justForMe ?? $_GET['justForMe'] ?? null
        ]) :*/ Yii::t('app', 'Actions'),
        'template' => '{set-as-default} {increase}',
        'visibleButtons' => [
          'set-as-default' => function ($model, $key, $index) {
            return ($model->walIsDefault != true);
          },
          'increase' => function ($model, $key, $index) {
            return true;
          },
        ],
        'buttons' => [
          'increase' => function ($url, $model, $key) {
            return Html::a(Yii::t('aaa', 'Increase Balance'), [
              '/aaa/wallet/increase',
              'id' => $model->walID,
              // 'ref' => Url::toRoute(['view', 'id' => $model->mbrUserID], true),
            ], [
              'class' => 'btn btn-sm btn-primary',
              'modal' => true,
            ]);
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
            $model->walCreatedAt,
            null, //$model->createdByUser,
            $model->walUpdatedAt,
            null, //$model->updatedByUser,
            $model->walRemovedAt,
            null, //$model->removedByUser,
          );
        },
      ],
    ],
  ]);

?>
