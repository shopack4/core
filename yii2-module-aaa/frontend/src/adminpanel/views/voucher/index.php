<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\common\widgets\grid\GridView;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\common\helpers\StringHelper;
use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\aaa\frontend\common\models\VoucherModel;

$this->title = Yii::t('aaa', 'Vouchers');
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="voucher-index w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
        <?= VoucherModel::canCreate() ? Html::createButton() : '' ?>
			</div>
      <div class='card-title'><?= Html::encode($this->title) ?></div>
			<div class="clearfix"></div>
		</div>

    <div class='card-body'>
      <?php
      echo GridView::widget([
        'id' => StringHelper::generateRandomId(),
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

        'columns' => [
          [
            'class' => 'kartik\grid\SerialColumn',
          ],
          'vchID',
          [
            'attribute' => 'vchOwnerUserID',
            'value' => function($model) {
              return $model->owner->displayName();
            },
          ],
          'vchAmount',
          'vchDiscountAmount',
          'vchDeliveryAmount',
          'vchTotalAmount',
          [
            'class' => \shopack\base\frontend\common\widgets\ActionColumn::class,
            'header' => VoucherModel::canCreate() ? Html::createButton() : Yii::t('app', 'Actions'),
            'template' => '{update} {delete}{undelete}',
            'visibleButtons' => [
              'update' => function ($model, $key, $index) {
                return $model->canUpdate();
              },
              'delete' => function ($model, $key, $index) {
                return $model->canDelete();
              },
              'undelete' => function ($model, $key, $index) {
                return $model->canUndelete();
              },
            ],
          ],
          [
            'attribute' => 'rowDate',
            'noWrap' => true,
            'format' => 'raw',
            'label' => 'ایجاد / ویرایش',
            'value' => function($model) {
              return Html::formatRowDates(
                $model->vchCreatedAt,
                $model->createdByUser,
                $model->vchUpdatedAt,
                $model->updatedByUser,
                $model->vchRemovedAt,
                $model->removedByUser,
              );
            },
          ],
        ],
      ]);
      ?>
    </div>
  </div>
</div>
