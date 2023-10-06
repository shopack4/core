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
use shopack\aaa\frontend\common\models\GeoStateModel;

$this->title = Yii::t('aaa', 'State') . ': ' . $model->sttID . ' - ' . $model->sttName;
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = ['label' => Yii::t('aaa', 'Countries'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="geo-state-view w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
				<?= GeoStateModel::canCreate() ? Html::createButton() : '' ?>
        <?= $model->canUpdate()   ? Html::updateButton(null,   ['id' => $model->sttID]) : '' ?>
        <?= $model->canDelete()   ? Html::deleteButton(null,   ['id' => $model->sttID]) : '' ?>
        <?= $model->canUndelete() ? Html::undeleteButton(null, ['id' => $model->sttID]) : '' ?>
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
            'sttID',
            // [
            //   'attribute' => 'sttStatus',
            //   'value' => enuGeoStateStatus::getLabel($model->sttStatus),
            // ],
            'sttName',
            [
              'attribute' => 'sttCountryID',
              'value' => $model->country->cntrName,
            ],
            'sttCreatedAt:jalaliWithTime',
            [
              'attribute' => 'sttCreatedBy_User',
              'format' => 'raw',
              'value' => $model->createdByUser->actorName ?? '-',
            ],
            'sttUpdatedAt:jalaliWithTime',
            [
              'attribute' => 'sttUpdatedBy_User',
              'format' => 'raw',
              'value' => $model->updatedByUser->actorName ?? '-',
            ],
            'sttRemovedAt:jalaliWithTime',
            [
              'attribute' => 'sttRemovedBy_User',
              'format' => 'raw',
              'value' => $model->removedByUser->actorName ?? '-',
            ],
          ],
        ]);
      ?>
    </div>
		<div class='card-header'>
      شهرها:
    </div>
    <div class='card-body'>
      <?php
        echo Yii::$app->runAction('/aaa/geo-city-or-village/index', ArrayHelper::merge($_GET, [
          'isPartial' => true,
          'params' => [
            'ctvStateID' => $model->sttID,
          ],
        ]));
      ?>
    </div>
  </div>
</div>
