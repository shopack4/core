<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\DetailView;
use shopack\base\frontend\common\widgets\PopoverX;
use shopack\aaa\common\enums\enuWalletStatus;
use shopack\aaa\frontend\common\models\WalletModel;

$this->title = Yii::t('aaa', 'Wallet') . ': ' . $model->walID . ' - ' . $model->walName;
$this->params['breadcrumbs'][] = ['label' => Yii::t('aaa', 'Wallets'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="wallet-view w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
				<?= Html::a(Yii::t('aaa', 'Increase Balance'), [
          '/aaa/wallet/increase',
          'id' => $model->walID,
          // 'ref' => Url::toRoute(['view', 'id' => $model->mbrUserID], true),
        ], [
          'class' => 'btn btn-sm btn-primary',
          'modal' => true,
        ]) ?>
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
              'walCreatedAt:jalaliWithTime',
              [
                'attribute' => 'walCreatedBy_User',
                'format' => 'raw',
                'value' => $model->createdByUser->actorName ?? '-',
              ],
              'walUpdatedAt:jalaliWithTime',
              [
                'attribute' => 'walUpdatedBy_User',
                'format' => 'raw',
                'value' => $model->updatedByUser->actorName ?? '-',
              ],
              'walRemovedAt:jalaliWithTime',
              [
                'attribute' => 'walRemovedBy_User',
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
          'walID',
          [
            'attribute' => 'walStatus',
            'value' => enuWalletStatus::getLabel($model->walStatus),
          ],
          'walName',
          [
            'attribute' => 'walOwnerUserID',
            'format' => 'raw',
            'value' => Html::a($model->owner->displayName(), Yii::$app->getModule('aaa')->createUserViewUrl($model->walOwnerUserID)),
          ],
          'walIsDefault:boolean',
          'walRemainedAmount:toman',
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
