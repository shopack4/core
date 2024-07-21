<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\common\widgets\PopoverX;
use shopack\base\frontend\common\widgets\DetailView;
use shopack\base\frontend\common\helpers\Html;
use shopack\aaa\frontend\common\models\BasicDefinitionModel;
use shopack\aaa\common\enums\enuBasicDefinitionType;
use shopack\aaa\common\enums\enuBasicDefinitionStatus;
use shopack\cmn\frontend\common\helpers\I18NHelper;

$this->title = Yii::t('app', 'Basic Definition') . ': ' . $model->bdfID . ' - ' . $model->bdfName;
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Basic Definitions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="basic-definition-view w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
				<?= BasicDefinitionModel::canCreate() ? Html::createButton() : '' ?>
        <?= $model->canUpdate()   ? Html::updateButton(null,   ['id' => $model->bdfID]) : '' ?>
        <?= $model->canDelete()   ? Html::deleteButton(null,   ['id' => $model->bdfID]) : '' ?>
        <?= $model->canUndelete() ? Html::undeleteButton(null, ['id' => $model->bdfID]) : '' ?>
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
              'bdfCreatedAt:jalaliWithTime',
              [
                'attribute' => 'bdfCreatedBy_User',
                'format' => 'raw',
                'value' => $model->createdByUser->actorName ?? '-',
              ],
              'bdfUpdatedAt:jalaliWithTime',
              [
                'attribute' => 'bdfUpdatedBy_User',
                'format' => 'raw',
                'value' => $model->updatedByUser->actorName ?? '-',
              ],
              'bdfRemovedAt:jalaliWithTime',
              [
                'attribute' => 'bdfRemovedBy_User',
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
          'bdfID',
          [
            'attribute' => 'bdfStatus',
            'value' => enuBasicDefinitionStatus::getLabel($model->bdfStatus),
          ],
          [
            'attribute' => 'bdfType',
            'value' => enuBasicDefinitionType::getLabel($model->bdfType),
          ],
          'bdfName',
        ];

        $attributes = array_merge($attributes, I18NHelper::getMultiLanguageAttributs($model, 'bdfName', 'bdfI18NData'));

        echo DetailView::widget([
          'model' => $model,
          'enableEditMode' => false,
          'attributes' => $attributes,
        ]);
      ?>
    </div>
  </div>
</div>
