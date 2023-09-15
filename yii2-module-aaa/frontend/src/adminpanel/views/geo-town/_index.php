<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\common\widgets\grid\GridView;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\common\helpers\StringHelper;
use shopack\aaa\frontend\common\models\GeoTownModel;
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
      'twnID',
      [
        'attribute' => 'twnName',
        'format' => 'raw',
        'value' => function ($model, $key, $index, $widget) {
          return Html::a($model->twnName, ['view', 'id' => $model->twnID]);
        },
      ],
      [
        'attribute' => 'twnCityID',
        'value' => function ($model, $key, $index, $widget) {
          return $model->cityOrVillage->ctvName;
        },
      ],
      // [
      //   'class' => \shopack\base\frontend\common\widgets\grid\EnumDataColumn::class,
      //   'enumClass' => enuGeoTownStatus::class,
      //   'attribute' => 'twnStatus',
      // ],
      [
        'class' => \shopack\base\frontend\common\widgets\ActionColumn::class,
        'header' => GeoTownModel::canCreate() ? Html::createButton(null, [
          'twnCityID' => $twnCityID ?? $_GET['twnCityID'] ?? null
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
            $model->twnCreatedAt,
            $model->createdByUser,
            $model->twnUpdatedAt,
            $model->updatedByUser,
            $model->twnRemovedAt,
            $model->removedByUser,
          );
        },
      ],
    ],
  ]);

?>
