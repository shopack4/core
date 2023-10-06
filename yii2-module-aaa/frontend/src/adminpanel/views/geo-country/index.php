<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\common\widgets\grid\GridView;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\common\helpers\StringHelper;
// use shopack\aaa\common\enums\enuGeoCountryStatus;
use shopack\aaa\frontend\common\models\GeoCountryModel;

$this->title = Yii::t('aaa', 'Countries');
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="geo-country-index w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
        <?= GeoCountryModel::canCreate() ? Html::createButton() : '' ?>
			</div>
      <div class='card-title'><?= Html::encode($this->title) ?></div>
			<div class="clearfix"></div>
		</div>

    <div class='card-body'>
      <?php
      echo GridView::widget([
        'id' => StringHelper::generateRandomId(),
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

        'columns' => [
          [
            'class' => 'kartik\grid\SerialColumn',
          ],
          'cntrID',
          [
            'attribute' => 'cntrName',
            'format' => 'raw',
            'value' => function ($model, $key, $index, $widget) {
              return Html::a($model->cntrName, ['view', 'id' => $model->cntrID]);
            },
          ],
          // [
          //   'class' => \shopack\base\frontend\common\widgets\grid\EnumDataColumn::class,
          //   'enumClass' => enuGeoCountryStatus::class,
          //   'attribute' => 'cntrStatus',
          // ],
          [
            'class' => \shopack\base\frontend\common\widgets\ActionColumn::class,
            'header' => GeoCountryModel::canCreate() ? Html::createButton() : Yii::t('app', 'Actions'),
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
                $model->cntrCreatedAt,
                $model->createdByUser,
                $model->cntrUpdatedAt,
                $model->updatedByUser,
                $model->cntrRemovedAt,
                $model->removedByUser,
              );
            },
          ],
        ],
      ]);
      ?>
    </div>
  </div>
</div>
