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
use shopack\aaa\common\enums\enuOfflinePaymentStatus;
use shopack\aaa\frontend\common\models\OfflinePaymentModel;

$this->title = Yii::t('aaa', 'Offline Payment') . ': ' . $model->ofpID . ' - ' . $model->ofpName;
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = ['label' => Yii::t('aaa', 'Offline Payments'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="offline-payment-view w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
        <?= OfflinePaymentModel::canCreate() ? Html::createButton() : '' ?>
        <?= $model->canUpdate()   ? Html::updateButton(null,   ['id' => $model->ofpID]) : '' ?>
        <?= $model->canDelete()   ? Html::deleteButton(null,   ['id' => $model->ofpID]) : '' ?>
        <?= $model->canUndelete() ? Html::undeleteButton(null, ['id' => $model->ofpID]) : '' ?>
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
              'ofpCreatedAt:jalaliWithTime',
              [
                'attribute' => 'ofpCreatedBy_User',
                'format' => 'raw',
                'value' => $model->createdByUser->actorName ?? '-',
              ],
              'ofpUpdatedAt:jalaliWithTime',
              [
                'attribute' => 'ofpUpdatedBy_User',
                'format' => 'raw',
                'value' => $model->updatedByUser->actorName ?? '-',
              ],
              'ofpRemovedAt:jalaliWithTime',
              [
                'attribute' => 'ofpRemovedBy_User',
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
          'ofpID',

          'ofpOwnerUserID',
          'ofpVoucherID',
          'ofpBankOrCart',
          'ofpTrackNumber',
          'ofpReferenceNumber',
          'ofpPayDate',
          'ofpAmount',
          'ofpPayer',
          'ofpSourceCartNumber',
          'ofpImageFileID',
          'ofpWalletID',
          'ofpComment',
          [
            'attribute' => 'ofpStatus',
            'value' => enuOfflinePaymentStatus::getLabel($model->ofpStatus),
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
