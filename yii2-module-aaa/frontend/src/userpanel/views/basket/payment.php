<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

// use NumberFormatter;
use shopack\base\frontend\widgets\ActiveForm;
use shopack\base\frontend\helpers\Html;
use shopack\aaa\frontend\common\models\WalletModel;
use shopack\aaa\frontend\common\models\OnlinePaymentModel;
use shopack\aaa\frontend\userpanel\models\BasketCheckoutForm;

$this->title = Yii::t('aaa', 'Shopping Card - Payment Method');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="shopping-card-payment w-100">
  <div class='card border-default'>
		<div class='card-header bg-default'>
			<div class="float-end">
      </div>
      <div class='card-title'><?= Html::encode($this->title) ?></div>
			<div class="clearfix"></div>
		</div>

    <div class='card-body'>
      <?php
        $form = ActiveForm::begin([
          'model' => $model,
        ]);

        $model->currentStep = BasketCheckoutForm::STEP_PAYMENT;
        echo $form
          ->field($model, 'currentStep')
          ->label(false)
          ->hiddenInput();
      ?>

      <div class='row'>
        <div class='col-8'>
          <?php
            $currencyFormatter = Yii::$app->formatter->getCurrencyFormatter();
            $thousandSeparator = $currencyFormatter->getSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL);

            $modelGatewayType_id = Html::getInputId($model, 'gatewayType');

            $js =<<<JS
var _lock_nullableRadioCheckChanged = false;
function nullableRadioCheckChanged(prefix, hiddenid, e) {
  if (_lock_nullableRadioCheckChanged)
    return;

  _lock_nullableRadioCheckChanged = true;

  var sender = null;
  if (e != null) {
    if (e.target !== undefined)
      sender = e.target;
    else if (e.input !== undefined) {
      if (e.input.length !== undefined)
        sender = e.input[0];
      else
        sender = e.input;
    }
  }

  $('input:checkbox[id^="' + prefix + '-"]').each(function() {
    var el = $(this);

    if (el.attr('id') == sender.id) {
      if (el.is(':checked')) {
        checkid = el.attr('id').substr(prefix.length + 1);
        $('#' + hiddenid).val(checkid);
      } else
        $('#' + hiddenid).val('');
    } else if (el.is(':checked')) {
      el.prop('checked', false);
    }
  });

  _lock_nullableRadioCheckChanged = false;
}
var thousandSeparator = "{$thousandSeparator}";
var currencySign = "تومان";
function asDecimal(val) {
  var parts = val.toString().split(".");
  var num = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator) + (parts[1] ? "." + parts[1] : "");
  return num;
}
function asCurrency(val) {
  if (val == 0)
    return val;

  return asDecimal(val) + " " + currencySign;
}
JS;
            $this->registerJs($js, \yii\web\View::POS_END);

            $js =<<<JS
var walAmount = 0;
var remainAfterWallet = {$model['total']};
function checkWalletSelection() {
  var el = $('input:checkbox[id^="walletid-"]:checked');

  walAmount = 0;
  if (el.length == 1)
    walAmount = el.data('wal-amount');

  // console.log(el);
  // console.log(walAmount);

  remainAfterWallet = {$model['total']};
  if (walAmount == 0) {
    $('#row-walletamount').hide();
  } else {
    if (walAmount > remainAfterWallet)
      walAmount = remainAfterWallet;

    $('#row-walletamount').show();
    $('#spn-walletamount').html(asCurrency(walAmount));

    remainAfterWallet = remainAfterWallet - walAmount;
  }
  $('#spn-total').html(asCurrency(remainAfterWallet));

  if (remainAfterWallet > 0) {
    $('#row-gatewaytype').show();
  } else {
    $('#row-gatewaytype').hide();
    $('input:radio[id^="{$modelGatewayType_id}-"]:checked').each(function() {
      var rdo = $(this);
      rdo.prop('checked', false);
      // rdo.attr('checked', null);
    });
    $('#{$modelGatewayType_id}').val('');
  }
}
JS;
            $this->registerJs($js, \yii\web\View::POS_END);

            $walletModels = WalletModel::find()
              ->andWhere(['>', 'walRemainedAmount', 0])
              ->all();

            $walletItems = [];
            if (empty($walletModels) == false) {
              foreach ($walletModels as $walletModel) {
                if (empty($model->walletID)
                  && $walletModel->walIsDefault
                  && $walletModel->walRemainedAmount > 0
                ) {
                  $model->walletID = $walletModel->walID;
                  $checked = true;
                } else
                  $checked = ($model->walletID == $walletModel->walID);

                $walletItems[] = Html::checkbox('walletid-' . $walletModel->walID, $checked, [
                  'label' => Yii::t('app', $walletModel->walName) . ' (موجودی: ' . Yii::$app->formatter->asToman($walletModel->walRemainedAmount) . ')',
                  'id' => 'walletid-' . $walletModel->walID,
                  'data' => [
                    'wal-amount' => $walletModel->walRemainedAmount,
                  ],
                  'labelOptions' => [
                    'class' => $walletModel->walIsDefault ? ['fw-bold'] : [],
                  ],
                ]);
              }

              if (empty($walletItems) == false) {
                echo "<p>برداشت از کیف پول:</p>";
                echo "<p>" . implode("</p><p>", $walletItems) . "</p>";

                $modelWalletID_id = Html::getInputId($model, 'walletID');
                $modelPatewayType_id = Html::getInputId($model, 'gatewayType');

                $js =<<<JS
$('[id^="walletid-"]').each(function() { $(this).on('change', function(e) {
  nullableRadioCheckChanged('walletid', '{$modelWalletID_id}', e);
  checkWalletSelection();
}); });
checkWalletSelection();
JS;
                $this->registerJs($js, \yii\web\View::POS_READY);
              }
            }

            // echo $form
            //   ->field($model, 'walletID')
            //   ->label(false)
            //   ->hiddenInput();
            echo Html::activeHiddenInput($model, 'walletID');
          ?>

          <div id='row-gatewaytype' <?= empty($walletItems) ? "" : "style='display:none'" ?>>
            <?php
              if (empty($walletItems) == false) {
                echo "<hr>";
              }
            ?>
            <p>روش پرداخت:</p>
            <?php
              $gatewayTypes = OnlinePaymentModel::getAllowedTypes();
              if ((count($gatewayTypes) == 1) && empty($model->gatewayType)) {
                $model->gatewayType = array_keys($gatewayTypes)[0];
              }

              echo $form
                ->field($model, 'gatewayType')
                ->label(false)
                ->radioList($gatewayTypes);

              // echo Html::activeRadioList($model, 'gatewayType', $gatewayTypes);
            ?>
          </div>
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
              echo Html::submitButton('پرداخت و ثبت سفارش', [
                'class' => ['btn', 'btn-sm', 'btn-success', 'w-100'],
              ]);
              // echo Html::a('پرداخت و ثبت سفارش', ['checkout'], [
              //   'class' => ['btn', 'btn-sm', 'btn-success', 'd-block'],
              // ]);
            ?>
          </div>
          <div>
            <?= Html::formErrorSummary($model); ?>
          </div>
        </div>

      </div>

      <?php
    		$form->endForm(); //ActiveForm::end();
	    ?>
    </div>
  </div>
</div>
