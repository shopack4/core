<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\common\widgets\grid\GridView;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\common\helpers\StringHelper;
use shopack\aaa\frontend\common\models\GeoCityOrVillageModel;
?>

<?php
	// echo Alert::widget(['key' => 'shoppingcart']);

	// if (isset($statusReport))
	// 	echo (is_array($statusReport) ? Html::icon($statusReport[0], ['plugin' => 'glyph']) . ' ' . $statusReport[1] : $statusReport);

  echo GridView::widget([
    'id' => StringHelper::generateRandomId(),
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,

    'columns' => [
      [
        'class' => 'kartik\grid\SerialColumn',
      ],
      'ctvID',
      [
        'attribute' => 'ctvName',
        'format' => 'raw',
        'value' => function ($model, $key, $index, $widget) {
          return Html::a($model->ctvName, ['view', 'id' => $model->ctvID]);
        },
      ],
      [
        'attribute' => 'ctvStateID',
        'value' => function ($model, $key, $index, $widget) {
          return $model->state->sttName;
        },
      ],
      // [
      //   'class' => \shopack\base\frontend\common\widgets\grid\EnumDataColumn::class,
      //   'enumClass' => enuGeoCityOrVillageStatus::class,
      //   'attribute' => 'ctvStatus',
      // ],
      [
        'class' => \shopack\base\frontend\common\widgets\ActionColumn::class,
        'header' => GeoCityOrVillageModel::canCreate() ? Html::createButton(null, [
          'ctvStateID' => $ctvStateID ?? $_GET['ctvStateID'] ?? null
        ]) : Yii::t('app', 'Actions'),
        'template' => '{update} {delete}{undelete}',
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
      ],
      [
        'attribute' => 'rowDate',
        'noWrap' => true,
        'format' => 'raw',
        'label' => 'ایجاد / ویرایش',
        'value' => function($model) {
          return Html::formatRowDates(
            $model->ctvCreatedAt,
            $model->createdByUser,
            $model->ctvUpdatedAt,
            $model->updatedByUser,
            $model->ctvRemovedAt,
            $model->removedByUser,
          );
        },
      ],
    ],
  ]);

?>
