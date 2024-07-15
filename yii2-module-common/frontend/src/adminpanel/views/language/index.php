<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\common\widgets\grid\GridView;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\common\helpers\StringHelper;
// use shopack\cmn\common\enums\enuLanguageStatus;
use shopack\cmn\frontend\common\models\LanguageModel;

$this->title = Yii::t('cmn', 'Languages');
$this->params['breadcrumbs'][] = Yii::t('cmn', 'Common');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="language-index w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
        <?= LanguageModel::canCreate() ? Html::createButton() : '' ?>
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
          'lngID',
          [
            'attribute' => 'lngName',
            'format' => 'raw',
            'value' => function ($model, $key, $index, $widget) {
              return Html::a($model->lngName, ['view', 'id' => $model->lngID]);
            },
          ],
          // [
          //   'class' => \shopack\base\frontend\common\widgets\grid\EnumDataColumn::class,
          //   'enumClass' => enuLanguageStatus::class,
          //   'attribute' => 'lngStatus',
          // ],
          [
            'class' => \shopack\base\frontend\common\widgets\ActionColumn::class,
            'header' => LanguageModel::canCreate() ? Html::createButton() : Yii::t('app', 'Actions'),
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
                $model->lngCreatedAt,
                $model->createdByUser,
                $model->lngUpdatedAt,
                $model->updatedByUser,
                $model->lngRemovedAt,
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
