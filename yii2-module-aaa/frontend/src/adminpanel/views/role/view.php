<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\common\helpers\Json;
use shopack\base\frontend\widgets\PopoverX;
use shopack\base\frontend\helpers\Html;
use shopack\base\frontend\widgets\DetailView;
use shopack\aaa\frontend\common\models\RoleModel;

$this->title = Yii::t('aaa', 'Role') . ': ' . $model->rolID . ' - ' . $model->rolName;
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = ['label' => Yii::t('aaa', 'Roles'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="role-view w-100">
  <div class='card border-default'>
		<div class='card-header bg-default'>
			<div class="float-end">
				<?= RoleModel::canCreate() ? Html::createButton() : '' ?>
        <?= $model->canUpdate()   ? Html::updateButton(null,   ['id' => $model->rolID]) : '' ?>
        <?= $model->canDelete()   ? Html::deleteButton(null,   ['id' => $model->rolID]) : '' ?>
        <?= $model->canUndelete() ? Html::undeleteButton(null, ['id' => $model->rolID]) : '' ?>
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
              'rolCreatedAt:jalaliWithTime',
              [
                'attribute' => 'rolCreatedBy_User',
                'format' => 'raw',
                'value' => $model->createdByUser->actorName ?? '-',
              ],
              'rolUpdatedAt:jalaliWithTime',
              [
                'attribute' => 'rolUpdatedBy_User',
                'format' => 'raw',
                'value' => $model->updatedByUser->actorName ?? '-',
              ],
              'rolRemovedAt:jalaliWithTime',
              [
                'attribute' => 'rolRemovedBy_User',
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
          'rolID',
          'rolName',
          [
            'attribute' => 'rolPrivs',
            'value' => Json::encode($model->rolPrivs),
          ],
          // [
          //   'attribute' => 'rolStatus',
          //   'value' => enuRoleStatus::getLabel($model->rolStatus),
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
