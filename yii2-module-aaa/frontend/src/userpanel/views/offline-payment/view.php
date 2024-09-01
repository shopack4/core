<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\common\helpers\ArrayHelper;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\PopoverX;
use shopack\base\frontend\common\widgets\DetailView;
use shopack\aaa\common\enums\enuBasicDefinitionType;
use shopack\aaa\common\enums\enuOfflinePaymentStatus;
use shopack\aaa\common\enums\enuOfflinePaymentType;
use shopack\aaa\frontend\common\models\BasicDefinitionModel;
use shopack\aaa\frontend\common\models\OfflinePaymentModel;

$this->title = Yii::t('aaa', 'Offline Payment') . ': ' . $model->ofpID;
$this->params['breadcrumbs'][] = ['label' => Yii::t('aaa', 'Offline Payments'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="offline-payment-view w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
        <?= OfflinePaymentModel::canCreate() ? Html::createButton(null, null, [
          'data-popup-size' => 'lg',
          'title' => Yii::t('aaa', 'Create Offline Payment'),
        ]) : '' ?>
        <?= $model->canUpdate()   ? Html::updateButton(null,   ['id' => $model->ofpID], [
          'modal' => true,
          'data-popup-size' => 'lg',
        ]) : '' ?>
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
              // [
              //   'attribute' => 'ofpCreatedBy_User',
              //   'format' => 'raw',
              //   'value' => $model->createdByUser->actorName ?? '-',
              // ],
              'ofpUpdatedAt:jalaliWithTime',
              // [
              //   'attribute' => 'ofpUpdatedBy_User',
              //   'format' => 'raw',
              //   'value' => $model->updatedByUser->actorName ?? '-',
              // ],
              // 'ofpRemovedAt:jalaliWithTime',
              // [
              //   'attribute' => 'ofpRemovedBy_User',
              //   'format' => 'raw',
              //   'value' => $model->removedByUser->actorName ?? '-',
              // ],
            ],
          ]);

          PopoverX::end();
        ?>
			</div>
      <div class='card-title'><?= Html::encode($this->title) ?></div>
			<div class="clearfix"></div>
		</div>

    <div class='card-body'>
      <div class='row'>
        <div class='col-9'>

          <?php
            $rejectReasons = null;

            if (empty($model->ofpRejectReasonIDs) == false) {
              $rejectReasonModels = ArrayHelper::map(BasicDefinitionModel::find()
                ->where(['bdfType' => enuBasicDefinitionType::OfflinePaymentRejectReason])
                ->noLimit()
                ->asArray()
                ->all(),
                'bdfID',
                'bdfName'
              );

              $rejectReasons = [];

              foreach ($model->ofpRejectReasonIDs as $r) {
                if (isset($rejectReasonModels[$r]))
                  $rejectReasons[] = $rejectReasonModels[$r];
              }

              $rejectReasons = implode(' - ', $rejectReasons);
            }

            $attributes = [
              'ofpID',
              [
                'attribute' => 'ofpStatus',
                'value' => enuOfflinePaymentStatus::getLabel($model->ofpStatus),
              ],
              [
                'attribute' => 'ofpVoucherID',
                'format' => 'raw',
                'value' => Html::a($model->ofpVoucherID, ['aaa/voucher/view', 'id' => $model->ofpVoucherID]),
              ],
              'ofpAmount:toman',
              [
                'attribute' => 'ofpWalletID',
                'format' => 'raw',
                'value' => Html::a($model->ofpWalletID . ' - ' . $model->wallet->walName, ['aaa/wallet/view', 'id' => $model->ofpWalletID]),
              ],
              'ofpPayer',
              'ofpPayDate:jalaliWithTime',
              [
                'attribute' => 'ofpType',
                'value' => enuOfflinePaymentType::getLabel($model->ofpType),
              ],
              [
                'attribute' => 'ofpDestCartID',
                'value' => (empty($model->ofpDestCartID) ? null : $model->destBankKart->bdfName),
              ],
              'ofpDestAccountNumber',
              'ofpDestISBN',
              [
                'attribute' => 'ofpDestBankID',
                'value' => (empty($model->ofpDestBankID) ? null : $model->destBank->bdfName),
              ],
              'ofpDestName',
              'ofpDueDate:jalali',
              'ofpTrackNumber',
              'ofpReferenceNumber',
              'ofpSourceCartNumber',
              [
                'attribute' => 'ofpRejectReasonIDs',
                'format' => 'raw',
                'value' => $rejectReasons,
              ],
              'ofpComment',
            ];

            echo DetailView::widget([
              'model' => $model,
              'enableEditMode' => false,
              'cols' => 2,
              'isVertical' => false,
              'attributes' => $attributes,
            ]);
          ?>
        </div>
        <div class='col-3'>
          <div class='card'>
            <div class='card-header'>
              <div class='card-title'><?= $model->getAttributeLabel('ofpImageFileID') ?></div>
            </div>
            <div class='card-body text-center'>
              <?= Html::asUploadedImage($model->imageFile, '100%', true) ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
