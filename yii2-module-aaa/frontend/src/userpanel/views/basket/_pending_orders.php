<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\common\widgets\grid\GridView;
use shopack\aaa\common\enums\enuVoucherType;
use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\aaa\frontend\common\models\VoucherSearchModel;
use shopack\base\frontend\common\helpers\Html;
?>

<?php
  $searchModel = new VoucherSearchModel;
  $dataProvider = $searchModel->search([]);
  $dataProvider->query
    ->andWhere(['vchType' => enuVoucherType::Invoice])
    ->andWhere(['vchStatus' => enuVoucherStatus::WaitForPayment])
  ;

  $totalCount = $dataProvider->getTotalCount();
  if ($totalCount > 0) {
?>
    <div class="order-index w-100">
      <div class='card'>
        <div class='card-header'>
          <div class="float-end"></div>
          <div class='card-title'>شما <?= $totalCount ?> صورتحساب منتظر پرداخت دارید</div>
          <div class="clearfix"></div>
        </div>

        <div class='card-body'>
          <?php
            echo GridView::widget([
              // 'id' => StringHelper::generateRandomId(),
              'dataProvider' => $dataProvider,
              // 'filterModel' => $searchModel,
              'columns' => [
                [
                  'class' => 'kartik\grid\SerialColumn',
                ],
                [
                  'attribute' => 'vchID',
                  'format' => 'raw',
                  'value' => function ($model, $key, $index, $widget) {
                    return Html::a($model->vchID, ['/aaa/order/view', 'id' => $model->vchID]);
                  },
                ],
                'vchTotalAmount:toman',
                'vchCreatedAt:jalaliWithTime',
                [
                  'class' => \shopack\base\frontend\common\widgets\ActionColumn::class,
                  'header' => Yii::t('app', 'Actions'),
                  'template' => '{pay} {cancel}', // {reprocess}',
                  'buttons' => [
                    'pay' => function ($url, $model, $key) {
                      return Html::a(Yii::t('aaa', 'Payment'), [
                        '/aaa/order/pay',
                        'id' => $model->vchID,
                      ], [
                        'class' => 'btn btn-sm btn-success',
                        'modal' => true,
                      ]);
                    },
                    'cancel' => function ($url, $model, $key) {
                      return Html::confirmButton(Yii::t('aaa', 'Cancel Order'), [
                        '/aaa/order/cancel',
                        'id' => $model->vchID,
                      ], Yii::t('aaa', 'Are you sure you want to cancel this order?'), [
                        'class' => 'btn btn-sm btn-danger',
                      ]);
                    },
                    // 'reprocess' => function ($url, $model, $key) {
                    //   return Html::a(Yii::t('aaa', 'Reprocess'), [
                    //     '/aaa/order/reprocess',
                    //     'id' => $model->vchID,
                    //   ], [
                    //     'class' => 'btn btn-sm btn-primary',
                    //     'modal' => true,
                    //   ]);
                    // },
                  ],
                  'visibleButtons' => [
                    'pay' => function ($model, $key, $index) {
                      return $model->canPay();
                    },
                    'cancel' => function ($model, $key, $index) {
                      return $model->canCancel();
                    },
                    // 'reprocess' => function ($model, $key, $index) {
                      // return $model->canReprocess();
                    // },
                  ],
                ],
              ],
            ]);
          ?>
        </div>
      </div>
    </div>
    <p>&nbsp;</p>
<?php
  }
?>
