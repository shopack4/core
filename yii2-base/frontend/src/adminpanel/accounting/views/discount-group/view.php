<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\common\helpers\ArrayHelper;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\PopoverX;
use shopack\base\frontend\common\widgets\DetailView;

$modelClass = Yii::$app->controller->modelClass;

$this->title = Yii::t('aaa', 'Discount Group') . ': ' . $model->dscgrpgrpID . ' - ' . $model->dscgrpgrpName;
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = ['label' => Yii::t('aaa', 'Discount Groups'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="discount-group-view w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
				<?= $modelClass::canCreate() ? Html::createButton() : '' ?>
        <?= $model->canUpdate()   ? Html::updateButton(null,   ['id' => $model->dscgrpID]) : '' ?>
        <?= $model->canDelete()   ? Html::deleteButton(null,   ['id' => $model->dscgrpID]) : '' ?>
        <?= $model->canUndelete() ? Html::undeleteButton(null, ['id' => $model->dscgrpID]) : '' ?>
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
              'dscgrpCreatedAt:jalaliWithTime',
              [
                'attribute' => 'dscgrpCreatedBy_User',
                'format' => 'raw',
                'value' => $model->createdByUser->actorName ?? '-',
              ],
              'dscgrpUpdatedAt:jalaliWithTime',
              [
                'attribute' => 'dscgrpUpdatedBy_User',
                'format' => 'raw',
                'value' => $model->updatedByUser->actorName ?? '-',
              ],
              'dscgrpRemovedAt:jalaliWithTime',
              [
                'attribute' => 'dscgrpRemovedBy_User',
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
            'dscgrpID',
            'dscgrpName',
          ],
        ]);
      ?>
    </div>
  </div>
</div>
