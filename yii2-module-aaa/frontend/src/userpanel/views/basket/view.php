<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use yii\data\ArrayDataProvider;
use shopack\base\common\helpers\StringHelper;
use shopack\base\frontend\helpers\Html;
use shopack\base\frontend\widgets\grid\GridView;
use shopack\base\frontend\widgets\DetailView;
use shopack\aaa\common\enums\enuGatewayStatus;
use shopack\aaa\frontend\common\enums\enuCheckoutStep;
use shopack\aaa\frontend\common\models\GatewayModel;

$this->title = Yii::t('aaa', 'Shopping Card');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="shopping-card-view w-100">
  <div class='card border-default'>
		<div class='card-header bg-default'>
			<div class="float-end">
      </div>
      <div class='card-title'><?= Html::encode($this->title) ?></div>
			<div class="clearfix"></div>
		</div>

    <div class='card-body'>
      <div class='row'>
        <div class='col-8'>
          <?php
            $js =<<<JS
function plusQty()
{
}

function minusQty()
{
}

function deleteItem()
{
}
JS;
            $this->registerJs($js, \yii\web\View::POS_END);

            $dataProvider = new ArrayDataProvider([
              'allModels' => $model->voucher->vchItems,
              'key' => 'key',
              'pagination' => false,
            ]);

            echo GridView::widget([
              // 'id' => 'aaaaaaaaaaaaaa',
              'dataProvider' => $dataProvider,
              'columns' => [
                [
                  'class' => 'kartik\grid\SerialColumn',
                ],
                // 'key',
                // 'service',
                // 'slbkey',
                // 'slbid',
                [
                  'attribute' => 'desc',
                  'label' => 'شرح',
                ],
                [
                  'attribute' => 'qty',
                  'label' => 'تعداد / مقدار',
                  'format' => 'raw',
                  'value' => function ($model, $key, $index, $widget) {
                    if (empty($model['qtystep'])) {
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
                  'attribute' => 'unitprice',
                  'label' => 'مبلغ واحد',
                  'format' => 'toman',
                ],
                [
                  'attribute' => 'totalprice',
                  'label' => 'مبلغ کل',
                  'format' => 'toman',
                  'value' => function ($model, $key, $index, $widget) {
                    return $model['unitprice'] * $model['qty'];
                  },
                ],
                [
                  'class' => \shopack\base\frontend\widgets\ActionColumn::class,
                  'template' => '{delete}',
                  'buttons' => [
                    'delete' => function ($url, $model, $key) {
                      return Html::deleteButton("<i class='indicator fas fa-trash'></i>", [
                        // '/' . $model['service'] . '/basket/remove-item',
                        'remove-item',
                        'key' => $model['key'],
                      ], [
                        'class' => 'btn btn-sm btn-outline-danger',
                      ]);
                    },
                  ],
                ],
              ],
            ]);
          ?>
        </div>

        <div class='col-4'>
          <div>
            <?php
              echo Yii::$app->controller->renderPartial('_summery', [
                'model' => $model,
              ]);
            ?>
          </div>
          <p></p>
          <div>
            <?php
              if ($model->total == 0) {
                echo Html::a('ثبت سفارش', ['checkout'], [
                  'class' => ['btn', 'btn-sm', 'btn-success', 'd-block'],
                ]);
              } else {
                echo Html::a('بررسی و پرداخت', ['checkout'], [
                  'class' => ['btn', 'btn-sm', 'btn-primary', 'd-block'],
                ]);
              }
            ?>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
