<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\common\helpers\Json;
use shopack\base\frontend\common\widgets\PopoverX;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\DetailView;
use shopack\cmn\frontend\common\models\LanguageModel;

$this->title = Yii::t('cmn', 'Language') . ': ' . $model->lngID . ' - ' . $model->lngName;
$this->params['breadcrumbs'][] = Yii::t('cmn', 'Common');
$this->params['breadcrumbs'][] = ['label' => Yii::t('cmn', 'Languages'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="language-view w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
				<?= LanguageModel::canCreate() ? Html::createButton() : '' ?>
        <?= $model->canUpdate()   ? Html::updateButton(null,   ['id' => $model->lngID]) : '' ?>
        <?= $model->canDelete()   ? Html::deleteButton(null,   ['id' => $model->lngID]) : '' ?>
        <?= $model->canUndelete() ? Html::undeleteButton(null, ['id' => $model->lngID]) : '' ?>
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
            'attributes' => [
              'lngCreatedAt:jalaliWithTime',
              [
                'attribute' => 'lngCreatedBy_User',
                'format' => 'raw',
                'value' => $model->createdByUser->actorName ?? '-',
              ],
              'lngUpdatedAt:jalaliWithTime',
              [
                'attribute' => 'lngUpdatedBy_User',
                'format' => 'raw',
                'value' => $model->updatedByUser->actorName ?? '-',
              ],
              'lngRemovedAt:jalaliWithTime',
              [
                'attribute' => 'lngRemovedBy_User',
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
        $attributes = [
          'lngID',
          'lngName',
          'lngLanguageCode',
          'lngCountryCode',
          // 'lngIsPreferred:boolean',
          // [
          //   'attribute' => 'lngStatus',
          //   'value' => enuLanguageStatus::getLabel($model->lngStatus),
          // ],
        ];

        echo DetailView::widget([
          'model' => $model,
          'enableEditMode' => false,
          'attributes' => $attributes,
        ]);
      ?>
    </div>
  </div>
</div>
