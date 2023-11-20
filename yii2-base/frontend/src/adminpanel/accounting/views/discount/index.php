<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\common\helpers\StringHelper;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\grid\GridView;

$modelClass = Yii::$app->controller->modelClass;

$this->title = Yii::t('aaa', 'Discounts');
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="discount-index w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
        <?= $modelClass::canCreate() ? Html::createButton() : '' ?>
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
          'dscID',
          [
            'attribute' => 'dscName',
            'format' => 'raw',
            'value' => function ($model, $key, $index, $widget) {
              return Html::a($model->dscName, ['view', 'id' => $model->dscID]);
            },
          ],
          // [
          //   'class' => \shopack\base\frontend\common\widgets\grid\EnumDataColumn::class,
          //   'enumClass' => enuGeoCountryStatus::class,
          //   'attribute' => 'dscStatus',
          // ],
          [
            'class' => \shopack\base\frontend\common\widgets\ActionColumn::class,
            'header' => $modelClass::canCreate() ? Html::createButton() : Yii::t('app', 'Actions'),
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
                $model->dscCreatedAt,
                $model->createdByUser,
                $model->dscUpdatedAt,
                $model->updatedByUser,
                $model->dscRemovedAt,
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
