<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\common\widgets\PopoverX;
use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\frontend\common\widgets\DetailView;
use shopack\base\frontend\common\helpers\Html;
use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\aaa\frontend\common\models\VoucherModel;

$this->title = Yii::t('aaa', 'Voucher') . ': ' . $model->vchID . ' - ' . $model->vchName;
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = ['label' => Yii::t('aaa', 'Vouchers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="voucher-view w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
				<?= VoucherModel::canCreate() ? Html::createButton() : '' ?>
        <?= $model->canUpdate()   ? Html::updateButton(null,   ['id' => $model->vchID]) : '' ?>
        <?= $model->canDelete()   ? Html::deleteButton(null,   ['id' => $model->vchID]) : '' ?>
        <?= $model->canUndelete() ? Html::undeleteButton(null, ['id' => $model->vchID]) : '' ?>
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
              'vchCreatedAt:jalaliWithTime',
              [
                'attribute' => 'vchCreatedBy_User',
                'format' => 'raw',
                'value' => $model->createdByUser->actorName ?? '-',
              ],
              'vchUpdatedAt:jalaliWithTime',
              [
                'attribute' => 'vchUpdatedBy_User',
                'format' => 'raw',
                'value' => $model->updatedByUser->actorName ?? '-',
              ],
              'vchRemovedAt:jalaliWithTime',
              [
                'attribute' => 'vchRemovedBy_User',
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
          'vchID',
          [
            'attribute' => 'vchStatus',
            'value' => enuVoucherStatus::getLabel($model->vchStatus),
          ],
          'vchName',
          [
            'attribute' => 'vchKey',
            'valueColOptions' => ['class' => ['latin-text']],
          ],
          [
            'attribute' => 'vchPluginType',
            'value' => Yii::t('aaa', $model->vchPluginType),
          ],
          'vchPluginName',
          // 'vchRemovedAt',
          // 'vchRemovedBy',
        ];
      ?>
    </div>
  </div>
</div>
