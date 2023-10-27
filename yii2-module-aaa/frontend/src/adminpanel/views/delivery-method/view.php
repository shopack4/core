<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\common\widgets\PopoverX;
use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\common\helpers\ArrayHelper;
use shopack\base\frontend\common\widgets\DetailView;
use shopack\base\frontend\common\helpers\Html;
use shopack\aaa\frontend\common\models\DeliveryMethodModel;
use shopack\aaa\common\enums\enuDeliveryMethodStatus;
use shopack\aaa\common\enums\enuDeliveryMethodType;

$this->title = Yii::t('aaa', 'Delivery Method') . ': ' . $model->dlvID . ' - ' . $model->dlvName;
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = ['label' => Yii::t('aaa', 'Delivery Methods'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="delivery-method-view w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
				<?= DeliveryMethodModel::canCreate() ? Html::createButton() : '' ?>
        <?= $model->canUpdate()   ? Html::updateButton(null,   ['id' => $model->dlvID]) : '' ?>
        <?= $model->canDelete()   ? Html::deleteButton(null,   ['id' => $model->dlvID]) : '' ?>
        <?= $model->canUndelete() ? Html::undeleteButton(null, ['id' => $model->dlvID]) : '' ?>
        <?php
          PopoverX::begin([
            // 'header' => 'Hello world',
            'closeButton' => false,
            'toggleButton' => [
              'label' => Yii::t('app', 'Logs'),
              'class' => 'btn btn-sm btn-outline-secondary',
            ],
            'placement' => PopoverX::ALIGN_AUTO_BOTTOM,
          ]);

          echo DetailView::widget([
            'model' => $model,
            'enableEditMode' => false,
            // 'isVertical' => false,
            'attributes' => [
              'dlvCreatedAt:jalaliWithTime',
              [
                'attribute' => 'dlvCreatedBy_User',
                'format' => 'raw',
                'value' => $model->createdByUser->actorName ?? '-',
              ],
              'dlvUpdatedAt:jalaliWithTime',
              [
                'attribute' => 'dlvUpdatedBy_User',
                'format' => 'raw',
                'value' => $model->updatedByUser->actorName ?? '-',
              ],
              'dlvRemovedAt:jalaliWithTime',
              [
                'attribute' => 'dlvRemovedBy_User',
                'format' => 'raw',
                'value' => $model->removedByUser->actorName ?? '-',
              ],
            ],
          ]);

          PopoverX::end();
        ?>
			</div>
      <div class='card-title'><?= Html::encode($this->title) ?></div>
			<div class="clearfix"></div>
		</div>
    <div class='card-body'>
      <?php
        echo DetailView::widget([
          'model' => $model,
          'enableEditMode' => false,
          // 'cols' => 2,
          // 'isVertical' => false,
          'attributes' => [
            'dlvID',
            [
              'attribute' => 'dlvStatus',
              'value' => enuDeliveryMethodStatus::getLabel($model->dlvStatus),
            ],
            'dlvName',
            [
              'attribute' => 'dlvType',
              'value' => enuDeliveryMethodType::getLabel($model->dlvType),
            ],
            'dlvAmount:toman',
            'dlvTotalUsedCount:toman',
            'dlvTotalUsedAmount:toman',
          ],
        ]);
      ?>
    </div>
  </div>
</div>
