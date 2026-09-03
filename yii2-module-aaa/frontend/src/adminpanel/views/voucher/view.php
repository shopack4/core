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
use shopack\aaa\common\enums\enuVoucherType;
use shopack\aaa\frontend\common\models\VoucherModel;

$this->title = Yii::t('aaa', 'Voucher') . ': ' . $model->vchID; // . ' - ' . $model->vchName;
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
        // [
        //   'attribute' => 'vchUUID',
        //   'valueColOptions' => ['class' => ['latin-text']],
        // ],
        [
          'attribute' => 'vchOwnerUserID',
          'format' => 'raw',
          'value' => Html::a($model->owner->displayName(), Yii::$app->getModule('aaa')->createUserViewUrl($model->vchOwnerUserID)),
        ],
        [
          'attribute' => 'vchOriginVoucherID',
          'format' => 'raw',
          'value' => Html::a($model->vchOriginVoucherID, ['aaa/voucher/view', 'id' => $model->vchOriginVoucherID]),
        ],
        [
          'attribute' => 'vchType',
          'value' => enuVoucherType::getLabel($model->vchType),
        ],
        'vchAmount:toman',
        'vchItemsDiscounts:toman',
        'vchItemsVATs:toman',
        'vchDeliveryMethodID',
        'vchDeliveryAmount:toman',
        'vchTotalAmount:toman',
        'vchPaidByWallet:toman',
        'vchOnlinePaid:toman',
        'vchOfflinePaid:toman',
        'vchTotalPaid:toman',
        'vchReturnToWallet:toman',
        [
          'attribute' => 'vchStatus',
          'value' => enuVoucherStatus::getLabel($model->vchStatus),
        ],
      ];

      echo DetailView::widget([
        'model' => $model,
        'enableEditMode' => false,
        'cols' => 2,
        // 'isVertical' => false,
        'attributes' => $attributes,
      ]);

      ?>
    </div>
    <div class='card-body'>
      <?php
      $rows = [];
      foreach ($model->vchItems ?? [] as $k => $v) {
        $rows[] = [$k, $v];
      }
      echo Html::asTable($rows);
      ?>
    </div>

  </div>
</div>