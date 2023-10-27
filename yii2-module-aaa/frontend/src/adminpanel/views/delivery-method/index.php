<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\common\widgets\grid\GridView;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\common\helpers\StringHelper;
use shopack\aaa\common\enums\enuDeliveryMethodStatus;
use shopack\aaa\common\enums\enuDeliveryMethodType;
use shopack\aaa\frontend\common\models\DeliveryMethodModel;

$this->title = Yii::t('aaa', 'Delivery Methods');
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="delivery-method-index w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
        <?= DeliveryMethodModel::canCreate() ? Html::createButton() : '' ?>
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
          'dlvID',
          [
            'attribute' => 'dlvName',
            'format' => 'raw',
            'value' => function ($model, $key, $index, $widget) {
              return Html::a($model->dlvName, ['view', 'id' => $model->dlvID]);
            },
          ],
          [
            'class' => \shopack\base\frontend\common\widgets\grid\EnumDataColumn::class,
            'enumClass' => enuDeliveryMethodType::class,
            'attribute' => 'dlvType',
          ],
          'dlvAmount:toman',
          [
            'class' => \shopack\base\frontend\common\widgets\grid\EnumDataColumn::class,
            'enumClass' => enuDeliveryMethodStatus::class,
            'attribute' => 'dlvStatus',
          ],
          [
            'class' => \shopack\base\frontend\common\widgets\ActionColumn::class,
            'header' => DeliveryMethodModel::canCreate() ? Html::createButton() : Yii::t('app', 'Actions'),
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
                $model->dlvCreatedAt,
                $model->createdByUser,
                $model->dlvUpdatedAt,
                $model->updatedByUser,
                $model->dlvRemovedAt,
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
