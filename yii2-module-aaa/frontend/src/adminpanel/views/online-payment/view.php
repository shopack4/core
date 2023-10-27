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
use shopack\aaa\common\enums\enuOnlinePaymentStatus;
use shopack\aaa\frontend\common\models\OnlinePaymentModel;

$this->title = Yii::t('aaa', 'Online Payment') . ': ' . $model->onpID . ' - ' . $model->onpName;
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = ['label' => Yii::t('aaa', 'Online Payments'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="online-payment-view w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
        <?= $model->canDelete()   ? Html::deleteButton(null,   ['id' => $model->onpID]) : '' ?>
        <?= $model->canUndelete() ? Html::undeleteButton(null, ['id' => $model->onpID]) : '' ?>
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
              'onpCreatedAt:jalaliWithTime',
              [
                'attribute' => 'onpCreatedBy_User',
                'format' => 'raw',
                'value' => $model->createdByUser->actorName ?? '-',
              ],
              'onpUpdatedAt:jalaliWithTime',
              [
                'attribute' => 'onpUpdatedBy_User',
                'format' => 'raw',
                'value' => $model->updatedByUser->actorName ?? '-',
              ],
              'onpRemovedAt:jalaliWithTime',
              [
                'attribute' => 'onpRemovedBy_User',
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
          'onpID',
          [
            'attribute' => 'onpStatus',
            'value' => enuOnlinePaymentStatus::getLabel($model->onpStatus),
          ],
          'onpName',
          [
            'attribute' => 'onpUUID',
            'valueColOptions' => ['class' => ['latin-text']],
          ],
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
