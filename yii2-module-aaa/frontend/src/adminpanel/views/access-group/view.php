<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\common\helpers\Json;
use shopack\base\frontend\common\widgets\PopoverX;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\DetailView;
use shopack\aaa\frontend\common\models\AccessGroupModel;

$this->title = Yii::t('aaa', 'Access Group') . ': ' . $model->agpID . ' - ' . $model->agpName;
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = ['label' => Yii::t('aaa', 'Access Groups'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="access-group-view w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
				<?= AccessGroupModel::canCreate() ? Html::createButton() : '' ?>
        <?= $model->canUpdate()   ? Html::updateButton(null,   ['id' => $model->agpID]) : '' ?>
        <?= $model->canDelete()   ? Html::deleteButton(null,   ['id' => $model->agpID]) : '' ?>
        <?= $model->canUndelete() ? Html::undeleteButton(null, ['id' => $model->agpID]) : '' ?>
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
              'agpCreatedAt:jalaliWithTime',
              [
                'attribute' => 'agpCreatedBy_User',
                'format' => 'raw',
                'value' => $model->createdByUser->actorName ?? '-',
              ],
              'agpUpdatedAt:jalaliWithTime',
              [
                'attribute' => 'agpUpdatedBy_User',
                'format' => 'raw',
                'value' => $model->updatedByUser->actorName ?? '-',
              ],
              'agpRemovedAt:jalaliWithTime',
              [
                'attribute' => 'agpRemovedBy_User',
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
          'agpID',
          'agpName',
          [
            'attribute' => 'agpPrivs',
            'value' => Json::encode($model->agpPrivs),
          ],
          // [
          //   'attribute' => 'agpStatus',
          //   'value' => enuAccessGroupStatus::getLabel($model->agpStatus),
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
