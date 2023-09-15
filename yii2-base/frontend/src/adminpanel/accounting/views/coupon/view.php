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

$this->title = Yii::t('aaa', 'Coupon') . ': ' . $model->cpnID . ' - ' . $model->cpnName;
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = ['label' => Yii::t('aaa', 'Coupons'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="coupon-view w-100">
  <div class='card border-default'>
		<div class='card-header bg-default'>
			<div class="float-end">
				<?= $modelClass::canCreate() ? Html::createButton() : '' ?>
        <?= $model->canUpdate()   ? Html::updateButton(null,   ['id' => $model->cpnID]) : '' ?>
        <?= $model->canDelete()   ? Html::deleteButton(null,   ['id' => $model->cpnID]) : '' ?>
        <?= $model->canUndelete() ? Html::undeleteButton(null, ['id' => $model->cpnID]) : '' ?>
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
              'cpnCreatedAt:jalaliWithTime',
              [
                'attribute' => 'cpnCreatedBy_User',
                'format' => 'raw',
                'value' => $model->createdByUser->actorName ?? '-',
              ],
              'cpnUpdatedAt:jalaliWithTime',
              [
                'attribute' => 'cpnUpdatedBy_User',
                'format' => 'raw',
                'value' => $model->updatedByUser->actorName ?? '-',
              ],
              'cpnRemovedAt:jalaliWithTime',
              [
                'attribute' => 'cpnRemovedBy_User',
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
            'cpnID',
            // [
            //   'attribute' => 'cpnStatus',
            //   'value' => enuGeoCountryStatus::getLabel($model->cpnStatus),
            // ],
            'cpnName',
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
            'sttCountryID' => $model->cpnID,
          ],
        ]));
      ?>
    </div>
  </div>
</div>
