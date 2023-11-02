<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\common\widgets\grid\GridView;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\common\helpers\StringHelper;
// use shopack\aaa\common\enums\enuAccessGroupStatus;
use shopack\aaa\frontend\common\models\AccessGroupModel;

$this->title = Yii::t('aaa', 'Access Groups');
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="access-group-index w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
        <?= AccessGroupModel::canCreate() ? Html::createButton() : '' ?>
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
          'agpID',
          [
            'attribute' => 'agpName',
            'format' => 'raw',
            'value' => function ($model, $key, $index, $widget) {
              return Html::a($model->agpName, ['view', 'id' => $model->agpID]);
            },
          ],
          // [
          //   'class' => \shopack\base\frontend\common\widgets\grid\EnumDataColumn::class,
          //   'enumClass' => enuAccessGroupStatus::class,
          //   'attribute' => 'agpStatus',
          // ],
          [
            'class' => \shopack\base\frontend\common\widgets\ActionColumn::class,
            'header' => AccessGroupModel::canCreate() ? Html::createButton() : Yii::t('app', 'Actions'),
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
                $model->agpCreatedAt,
                $model->createdByUser,
                $model->agpUpdatedAt,
                $model->updatedByUser,
                $model->agpRemovedAt,
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
