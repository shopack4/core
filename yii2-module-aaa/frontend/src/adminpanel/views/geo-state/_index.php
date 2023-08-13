<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\widgets\grid\GridView;
use shopack\base\frontend\helpers\Html;
use shopack\base\common\helpers\StringHelper;
use shopack\aaa\frontend\common\models\GeoStateModel;
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
      'sttID',
      [
        'attribute' => 'sttName',
        'format' => 'raw',
        'value' => function ($model, $key, $index, $widget) {
          return Html::a($model->sttName, ['view', 'id' => $model->sttID]);
        },
      ],
      [
        'attribute' => 'sttCountryID',
        'value' => function ($model, $key, $index, $widget) {
          return $model->country->cntrName;
        },
      ],
      // [
      //   'class' => \shopack\base\frontend\widgets\grid\EnumDataColumn::class,
      //   'enumClass' => enuGeoStateStatus::class,
      //   'attribute' => 'sttStatus',
      // ],
      [
        'class' => \shopack\base\frontend\widgets\ActionColumn::class,
        'header' => GeoStateModel::canCreate() ? Html::createButton(null, [
          'sttCountryID' => $sttCountryID ?? $_GET['sttCountryID'] ?? null
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
            $model->sttCreatedAt,
            $model->createdByUser,
            $model->sttUpdatedAt,
            $model->updatedByUser,
            $model->sttRemovedAt,
            $model->removedByUser,
          );
        },
      ],
    ],
  ]);

?>
