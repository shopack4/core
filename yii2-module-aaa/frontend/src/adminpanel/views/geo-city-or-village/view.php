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
use shopack\aaa\frontend\common\models\GeoCityOrVillageModel;

$this->title = Yii::t('aaa', 'City Or Village') . ': ' . $model->ctvID . ' - ' . $model->ctvName;
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = ['label' => Yii::t('aaa', 'Countries'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="geo-city-or-village-view w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
				<?= GeoCityOrVillageModel::canCreate() ? Html::createButton() : '' ?>
        <?= $model->canUpdate()   ? Html::updateButton(null,   ['id' => $model->ctvID]) : '' ?>
        <?= $model->canDelete()   ? Html::deleteButton(null,   ['id' => $model->ctvID]) : '' ?>
        <?= $model->canUndelete() ? Html::undeleteButton(null, ['id' => $model->ctvID]) : '' ?>
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
          'cols' => 2,
          // 'isVertical' => false,
          'attributes' => [
            'ctvID',
            // [
            //   'attribute' => 'ctvStatus',
            //   'value' => enuGeoCityOrVillageStatus::getLabel($model->ctvStatus),
            // ],
            'ctvName',
            [
              'attribute' => 'ctvStateID',
              'value' => $model->state->sttName,
            ],
            'ctvCreatedAt:jalaliWithTime',
            [
              'attribute' => 'ctvCreatedBy_User',
              'format' => 'raw',
              'value' => $model->createdByUser->actorName ?? '-',
            ],
            'ctvUpdatedAt:jalaliWithTime',
            [
              'attribute' => 'ctvUpdatedBy_User',
              'format' => 'raw',
              'value' => $model->updatedByUser->actorName ?? '-',
            ],
            'ctvRemovedAt:jalaliWithTime',
            [
              'attribute' => 'ctvRemovedBy_User',
              'format' => 'raw',
              'value' => $model->removedByUser->actorName ?? '-',
            ],
          ],
        ]);
      ?>
    </div>
		<div class='card-header'>
      مناطق و محله‌ها:
    </div>
    <div class='card-body'>
      <?php
        echo Yii::$app->runAction('/aaa/geo-town/index', ArrayHelper::merge($_GET, [
          'isPartial' => true,
          'params' => [
            'twnCityID' => $model->ctvID,
          ],
        ]));
      ?>
    </div>
  </div>
</div>
