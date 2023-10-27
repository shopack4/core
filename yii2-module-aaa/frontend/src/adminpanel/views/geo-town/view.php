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
use shopack\aaa\frontend\common\models\GeoTownModel;

$this->title = Yii::t('aaa', 'Town') . ': ' . $model->twnID . ' - ' . $model->twnName;
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = ['label' => Yii::t('aaa', 'Countries'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="geo-town-view w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
				<?= GeoTownModel::canCreate() ? Html::createButton() : '' ?>
        <?= $model->canUpdate()   ? Html::updateButton(null,   ['id' => $model->twnID]) : '' ?>
        <?= $model->canDelete()   ? Html::deleteButton(null,   ['id' => $model->twnID]) : '' ?>
        <?= $model->canUndelete() ? Html::undeleteButton(null, ['id' => $model->twnID]) : '' ?>
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
              'twnCreatedAt:jalaliWithTime',
              [
                'attribute' => 'twnCreatedBy_User',
                'format' => 'raw',
                'value' => $model->createdByUser->actorName ?? '-',
              ],
              'twnUpdatedAt:jalaliWithTime',
              [
                'attribute' => 'twnUpdatedBy_User',
                'format' => 'raw',
                'value' => $model->updatedByUser->actorName ?? '-',
              ],
              'twnRemovedAt:jalaliWithTime',
              [
                'attribute' => 'twnRemovedBy_User',
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
          // 'isVertical' => false,
          'attributes' => [
            'twnID',
            // [
            //   'attribute' => 'twnStatus',
            //   'value' => enuGeoTownStatus::getLabel($model->twnStatus),
            // ],
            'twnName',
            [
              'attribute' => 'twnCityID',
              'value' => $model->cityOrVillage->ctvName,
            ],
          ],
        ]);
      ?>
    </div>
  </div>
</div>
