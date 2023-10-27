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
use shopack\aaa\frontend\common\models\GeoCountryModel;

$this->title = Yii::t('aaa', 'Country') . ': ' . $model->cntrID . ' - ' . $model->cntrName;
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = ['label' => Yii::t('aaa', 'Countries'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="geo-country-view w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
				<?= GeoCountryModel::canCreate() ? Html::createButton() : '' ?>
        <?= $model->canUpdate()   ? Html::updateButton(null,   ['id' => $model->cntrID]) : '' ?>
        <?= $model->canDelete()   ? Html::deleteButton(null,   ['id' => $model->cntrID]) : '' ?>
        <?= $model->canUndelete() ? Html::undeleteButton(null, ['id' => $model->cntrID]) : '' ?>
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
              'cntrCreatedAt:jalaliWithTime',
              [
                'attribute' => 'cntrCreatedBy_User',
                'format' => 'raw',
                'value' => $model->createdByUser->actorName ?? '-',
              ],
              'cntrUpdatedAt:jalaliWithTime',
              [
                'attribute' => 'cntrUpdatedBy_User',
                'format' => 'raw',
                'value' => $model->updatedByUser->actorName ?? '-',
              ],
              'cntrRemovedAt:jalaliWithTime',
              [
                'attribute' => 'cntrRemovedBy_User',
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
            'cntrID',
            // [
            //   'attribute' => 'cntrStatus',
            //   'value' => enuGeoCountryStatus::getLabel($model->cntrStatus),
            // ],
            'cntrName',
          ],
        ]);
      ?>
    </div>
		<div class='card-header'>
      استان‌ها:
    </div>
    <div class='card-body'>
      <?php
        echo Yii::$app->runAction('/aaa/geo-state/index', ArrayHelper::merge($_GET, [
          'isPartial' => true,
          'params' => [
            'sttCountryID' => $model->cntrID,
          ],
        ]));
      ?>
    </div>
  </div>
</div>
