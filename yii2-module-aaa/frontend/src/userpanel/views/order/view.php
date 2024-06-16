<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use yii\data\ArrayDataProvider;
use shopack\base\common\accounting\enums\enuProductType;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\DetailView;
use shopack\base\frontend\common\widgets\grid\GridView;
use shopack\aaa\common\enums\enuVoucherStatus;
use shopack\aaa\common\enums\enuOnlinePaymentStatus;
use shopack\aaa\frontend\common\models\OnlinePaymentSearchModel;
use shopack\aaa\frontend\common\models\WalletTransactionSearchModel;

$this->title = Yii::t('aaa', 'Order') . ': ' . $model->vchID;
$this->params['breadcrumbs'][] = ['label' => Yii::t('aaa', 'Orders'), 'url' => ['/aaa/fin', 'fragment' => 'orders']];
$this->params['breadcrumbs'][] = $this->title;

$physicalCount = 0;
foreach ($model->vchItems ?? [] as $item) {
  if (isset($item['prdType']) && ($item['prdType'] == enuProductType::Physical)) {
    ++$physicalCount;
  }
}
$hasPhysical = ($physicalCount > 0);
$hasDiscount = (empty($model->vchItemsDiscounts) == false);
$hasVAT = (empty($model->vchItemsVATs) == false);
?>

<div class="order-view w-100">

  <div class='card'>
		<div class='card-body'>
			<div class="float-end">
				<?php
          $buttons = [];

          if ($model->canPay()) {
            $buttons[] = Html::a(Yii::t('aaa', 'Payment'), [
              'pay',
              'id' => $model->vchID,
            ], [
              'class' => 'btn btn-sm btn-success',
              'modal' => true,
            ]);
          }

          if ($model->canCancel()) {
            $buttons[] = Html::confirmButton(Yii::t('aaa', 'Cancel Order'), [
              'cancel',
              'id' => $model->vchID,
            ], Yii::t('aaa', 'Are you sure you want to cancel this order?'), [
              'class' => 'btn btn-sm btn-danger',
            ]);
          }

          echo implode(' ', $buttons);
        ?>
        <?php
        /*
          $attributes = [];
          if (empty($model->vchCreatedAt) == false) $attributes[] = 'vchCreatedAt:jalaliWithTime';
          if (empty($model->vchUpdatedAt) == false) $attributes[] = 'vchUpdatedAt:jalaliWithTime';
          if (empty($model->vchRemovedAt) == false) $attributes[] = 'vchRemovedAt:jalaliWithTime';
          // [
          //   'attribute' => 'vchCreatedBy_User',
          //   'format' => 'raw',
          //   'value' => $model->createdByUser->actorName ?? '-',
          // ],
          // [
          //   'attribute' => 'vchUpdatedBy_User',
          //   'format' => 'raw',
          //   'value' => $model->updatedByUser->actorName ?? '-',
          // ],
          // [
          //   'attribute' => 'vchRemovedBy_User',
          //   'format' => 'raw',
          //   'value' => $model->removedByUser->actorName ?? '-',
          // ],

          if (empty($attributes) == false) {
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
              'attributes' => $attributes,
            ]);

            PopoverX::end();
          }
        */
        ?>
			</div>
      <div class='card-title'><?php
        echo implode('<br>', [
          Yii::t('aaa', 'Order Number') . ': ' . $model->vchID,
          Yii::t('aaa', 'Order Date') . ': ' . Yii::$app->formatter->asJalaliWithTime($model->vchCreatedAt),
          Yii::t('aaa', 'Order Status') . ': ' . enuVoucherStatus::getLabel($model->vchStatus),
        ]);
        // Html::encode($this->title)
      ?></div>
			<div class="clearfix"></div>
		</div>
  </div>

  <div style='margin-top: 25px;'>
      <?php
        $vchItems = $model->vchItems;

        // if (empty($model->vchDeliveryAmount) == false) {
        //   $vchItems[] = [
        //     'key' => 'dlv',
        //     'desc'       => 'هزینه ارسال',
        //     'qty'        => 1,
        //     'unit'       => null,
        //     'unitPrice'  => $model->vchDeliveryAmount,
        //     'discount'   => 0,
        //     'vat'        => 0,
        //     'totalPrice' => $model->vchDeliveryAmount,
        //   ];
        // }

        $dataProvider = new ArrayDataProvider([
          'allModels' => $vchItems,
          'key' => 'key',
          'pagination' => false,
        ]);

        $panelAfterItems = [];

        if ($hasPhysical && empty($model->vchDeliveryAmount) == false) {
          $panelAfterItems += [
            'هزینه ارسال' => Yii::$app->formatter->asToman($model->vchDeliveryAmount)
          ];
        }

        if ($hasDiscount || $hasVAT || $hasPhysical) {
          $panelAfterItems += [
            'جمع کل' => Yii::$app->formatter->asToman($model->vchTotalAmount)
          ];
        }

        if (empty($model->vchTotalPaid) == false) {
          $panelAfterItems += [
            'پرداخت شده' => Yii::$app->formatter->asToman($model->vchTotalPaid)
          ];
        }

        $panelAfterItems += [
          'مانده قابل پرداخت' => [
            Yii::$app->formatter->asToman($model->vchTotalAmount - ($model->vchTotalPaid ?? 0)),
            'class' => 'bg-light',
          ],
        ];

        $panelAfter = [];
        foreach ($panelAfterItems as $k => $v) {
          $class = '';
          if (isset($v['class']))
            $class = $v['class'];

          if (is_array($v))
            $v = $v[0];

//           $panelAfter[] =<<<HTML
// <div class='row ms-1 me-1 {$class}'>
//   <div class='col-8 text-end'>{$k}:</div>
//   <div class='col-4 text-nowrap'>{$v}</div>
// </div>
// HTML;

          $panelAfter[] =<<<HTML
<tr class='{$class}'>
  <td class='w-md-100 text-end'>{$k}:</td>
  <td class='text-nowrap'>{$v}</td>
</tr>
HTML;
        }
        $panelAfter = "<table class='w-100' cellpadding='2px'>" . implode('', $panelAfter) . '</table>';

        echo GridView::widget([
          // 'id' => 'aaaaaaaaaaaaaa',
          'dataProvider' => $dataProvider,

          'showPageSummary' => true,
          // 'showFooter' => true,
          // 'placeFooterAfterBody' => true,
          'panel' => [
            'before' => false,
            'after' => $panelAfter,
            // 'heading' => false,
            'footer' => false,
          ],

          'columns' => [
            [
              'class' => 'kartik\grid\SerialColumn',
              'pageSummary' => 'جمع:',
              'pageSummaryOptions' => ['colspan' => 4],
            ],
            // 'key',
            // 'service',
            // 'slbkey',
            // 'slbid',
            [
              'attribute' => 'desc',
              'label' => Yii::t('app', 'Description'),
            ],
            [
              'attribute' => 'qty',
              'label' => Yii::t('aaa', 'Qty'),
              'format' => 'raw',
              'value' => function ($model, $key, $index, $widget) {
                if (empty($model['qtyStep'])) {
                  return Yii::$app->formatter->asDecimal($model['qty']);
                }

                $items = [];

                $items[] = "<div class='input-group input-group-sm'>";

                $items[] = "<div class='input-group-prepend'>";
                $items[] = "<button id='plus-button' type='button' class='btn btn-sm btn-outline-success' title='بیشتر' onclick='plusQty()'><i class='indicator fas fa-plus'></i></button>";
                $items[] = "</div>";

                $items[] = "<input type='text' class='form-control text-center' style='max-width:50px' data-key='{$model['key']}' value='{$model['qty']}'></input>";

                $items[] = "<div class='input-group-append'>";
                $items[] = "<button id='minus-button' type='button' class='btn btn-sm btn-outline-success' title='کمتر' onclick='minusQty()'><i class='indicator fas fa-minus'></i></button>";
                $items[] = "</div>";

                $items[] = "</div>";

                return implode('', $items);
              },
            ],
            [
              'attribute' => 'unit',
              'label' => 'واحد', //Yii::t('aaa', 'Unit'),
            ],
            [
              'attribute' => 'unitPrice',
              'label' => Yii::t('aaa', 'Unit Price'),
              'format' => 'toman',
              'pageSummary' => true,
            ],
            [
              'attribute' => 'discount',
              'label' => Yii::t('aaa', 'Discount Amount'),
              'format' => 'toman',
              'pageSummary' => true,
            ],
            [
              'attribute' => 'vat',
              'label' => Yii::t('aaa', 'VAT Amount'),
              'format' => 'toman',
              'pageSummary' => true,
            ],
            [
              'attribute' => 'totalPrice',
              'label' => Yii::t('aaa', 'Total Amount'),
              'format' => 'toman',
              'pageSummary' => true,
            ],
            // [
            //   'class' => \shopack\base\frontend\common\widgets\ActionColumn::class,
            //   'template' => '{delete}',
            //   'buttons' => [
            //     'delete' => function ($url, $model, $key) {
            //       return Html::deleteButton("<i class='indicator fas fa-trash'></i>", [
            //         // '/' . $model['service'] . '/basket/remove-item',
            //         'remove-item',
            //         'key' => $model['service'] . '/' . $model['key'],
            //       ], [
            //         'class' => 'btn btn-sm btn-outline-danger',
            //         'title' => Yii::t('app', 'Delete'),
            //       ]);
            //     },
            //   ],
            // ],
          ],
        ]);
      ?>
  </div>

  <?php
    if ($hasPhysical) {
  ?>
    <div class='card'>
      <div class='card-header'>
        <div class="float-end">
          <?php
            if ($model->canPay()) {
              echo Html::a(Yii::t('aaa', 'Change Delivery Method'), [
                'change-delivery-method',
                'id' => $model->vchID,
              ], [
                'class' => 'btn btn-sm btn-success',
                'modal' => true,
              ]);
            }
          ?>
        </div>
        <div class='card-title'><?= Yii::t('aaa', 'Delivery Method') ?></div>
        <div class="clearfix"></div>
      </div>
      <div class='card-body'>
        <?php
          echo $model->deliveryMethod->dlvName . ' : '
            . (empty($model->vchDeliveryAmount) ? 'بدون هزینه' : Yii::$app->formatter->asToman($model->vchDeliveryAmount));
        ?>
      </div>
    </div>
  <?php
    }
  ?>

  <?php
    $onlinePaymentsDataProvider = (new OnlinePaymentSearchModel())->search([]);
    $onlinePaymentsDataProvider->query->andWhere(['onpVoucherID' => $model->vchID]);

    if ($onlinePaymentsDataProvider->getTotalCount() > 0) {
  ?>
    <div class='card'>
      <div class='card-header'>
        <div class='card-title'><?= Yii::t('aaa', 'Online Payments') ?></div>
      </div>
      <div class='card-body'>
        <?php
          echo GridView::widget([
            // 'id' => 'aaaaaaaaaaaaaa',
            'dataProvider' => $onlinePaymentsDataProvider,
            'columns' => [
              [
                'class' => 'kartik\grid\SerialColumn',
              ],
              'onpID',
              [
                'attribute' => 'onpAmount',
                'format' => 'toman',
                'contentOptions' => [
                  'class' => ['text-nowrap', 'tabular-nums'],
                ],
              ],
              // 'onpGatewayID',
              [
                'attribute' => 'onpTrackNumber',
                'contentOptions' => [
                  'class' => ['small'],
                ],
              ],
              [
                'attribute' => 'onpRRN',
                'contentOptions' => [
                  'class' => ['small'],
                ],
              ],
              [
                'attribute' => 'onpStatus',
                'value' => function ($model, $key, $index, $widget) {
                  return enuOnlinePaymentStatus::getLabel($model->onpStatus);
                },
              ],
              [
                'attribute' => 'onpCreatedAt',
                'format' => 'jalaliWithTime',
                'contentOptions' => [
                  'class' => ['text-nowrap', 'small'],
                ],
              ],
            ],
          ]);
        ?>
      </div>
    </div>
  <?php
    }
  ?>

  <?php
    if (empty($model->vchPaidByWallet) == false) {
      $walletTransactionDataProvider = (new WalletTransactionSearchModel())->search([]);
      $walletTransactionDataProvider->query->andWhere(['wtrVoucherID' => $model->vchID]);
  ?>
    <div class='card'>
      <div class='card-header'>
        <div class='card-title'><?= Yii::t('aaa', 'Wallet Transactions') ?></div>
      </div>
      <div class='card-body'>
        <?php
          echo GridView::widget([
            // 'id' => StringHelper::generateRandomId(),
            'dataProvider' => $walletTransactionDataProvider,
            // 'filterModel' => $searchModel,

            'columns' => [
              [
                'class' => 'kartik\grid\SerialColumn',
              ],
              'wtrID',
              [
                'attribute' => 'wtrWalletID',
                'value' => function($model) {
                  return Yii::t('app', $model->wallet->walName);
                },
              ],
              [
                'attribute' => 'credit',
                'label' => 'واریز',
                'contentOptions' => [
                  'class' => ['text-nowrap', 'tabular-nums'],
                ],
                'value' => function($model) {
                  if ($model->wtrAmount > 0)
                    return Yii::$app->formatter->asToman($model->wtrAmount);
                  return '';
                },
              ],
              [
                'attribute' => 'debit',
                'label' => 'برداشت',
                'contentOptions' => [
                  'class' => ['text-nowrap', 'tabular-nums'],
                ],
                'value' => function($model) {
                  if ($model->wtrAmount < 0)
                    return Yii::$app->formatter->asToman(abs($model->wtrAmount));
                  return '';
                },
              ],
              'wtrOnlinePaymentID',
              'wtrOfflinePaymentID',
              // [
              //   'class' => \shopack\base\frontend\common\widgets\grid\EnumDataColumn::class,
              //   'enumClass' => enuWalletTransactionStatus::class,
              //   'attribute' => 'wtrStatus',
              // ],
              [
                'attribute' => 'wtrCreatedAt',
                'format' => 'jalaliWithTime',
                'contentOptions' => [
                  'class' => ['text-nowrap', 'small'],
                ],
              ],
            ],
          ]);
        ?>
      </div>
    </div>
  <?php
    }
  ?>

</div>
