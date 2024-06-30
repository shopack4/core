<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

// use NumberFormatter;
use shopack\base\frontend\common\widgets\ActiveForm;
use shopack\base\frontend\common\helpers\Html;
use shopack\aaa\frontend\common\models\WalletModel;
use shopack\aaa\frontend\common\models\OnlinePaymentModel;
use shopack\aaa\frontend\userpanel\models\BasketCheckoutForm;
use shopack\aaa\frontend\common\models\PaymentMethodModel;
use shopack\aaa\common\enums\enuwalletItemstatus;
use shopack\aaa\common\enums\enuPaymentMethodType;
use shopack\base\frontend\common\widgets\FormBuilder;
?>

<div class='payment-form'>
	<?php
		$form = ActiveForm::begin([
			'model' => $model,
			'fieldConfig' => [
				'labelSpan' => 3,
			],
		]);

    $formName = $model->formName();
    $formNameLower = strtolower($formName);

    $currencyFormatter = Yii::$app->formatter->getCurrencyFormatter();
    $thousandSeparator = $currencyFormatter->getSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL);

    // $modelWalletID_id = Html::getInputId($model, 'walletID');
    $modelGatewayType_id = Html::getInputId($model, 'gatewayType');

		$builder = $form->getBuilder();

    //wallet
    $walletModels = WalletModel::find()
      ->andWhere(['>', 'walRemainedAmount', 0])
      ->all();

    if (empty($walletModels) == false) {
      $walletItemsByIndex = [];
      $walletItems = [];

      foreach ($walletModels as $walletModel) {
        $name = Yii::t('app', $walletModel->walName) . ' (موجودی: ' . Yii::$app->formatter->asToman($walletModel->walRemainedAmount) . ')';

        $walletItemsByIndex[] =
        [
          'walId' => $walletModel->walID,
          'walRemAmount' => $walletModel->walRemainedAmount,
        ];

        $walletItems += [
          $walletModel->walID => $name,
        ];
      }

      // $form->registerActiveHiddenInput($model, 'walletID');

      $builder->fields([
        "<p>برداشت از کیف پول:</p>",
        [
          'walletID',
          'type' => FormBuilder::FIELD_CHECKBOXLIST,
          'data' => $walletItems,
          'label' => false,
          'widgetOptions' => [
            'inline' => true,
          ],
        ],
      ]);

      $voucherTotalAmount = $model->voucher['vchTotalAmount'] - ($model->voucher['vchTotalPaid'] ?? 0);
      $walletItemsByIndex = json_encode($walletItemsByIndex);

      $js =<<<JS
var walletItemsByIndex = {$walletItemsByIndex};
var _lock_nullableRadioCheckChanged = false;
function nullableRadioCheckChanged(e, prefix/*, hiddenid*/) {
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
      // if (el.is(':checked')) {
      //   checkidx = el.attr('id').substr(prefix.length + 1);
      //   walId = walletItemsByIndex[checkidx].walId;
      //   $('#' + hiddenid).val(walId);
      // } else {
      //   $('#' + hiddenid).val('');
      // }
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
  return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator) + (parts[1] ? "." + parts[1] : "");
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
var remainAfterWallet = {$voucherTotalAmount};
function checkWalletSelection() {
  var el = $('input:checkbox[id^="{$formNameLower}-walletid-"]:checked');
  // console.log(el);

  walAmount = 0;
  if (el.length == 1)
    walAmount = walletItemsByIndex[el.data('index')].walRemAmount;

  // console.log(walAmount);

  remainAfterWallet = {$voucherTotalAmount};
  if (walAmount == 0) {
    // $('#row-walletamount').hide();
  } else {
    if (walAmount > remainAfterWallet)
      walAmount = remainAfterWallet;

    // $('#row-walletamount').show();
    // $('#spn-walletamount').html(asCurrency(walAmount));

    remainAfterWallet = remainAfterWallet - walAmount;
  }
  // $('#spn-total').html(asCurrency(remainAfterWallet));

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

      $js =<<<JS
$('[id^="{$formNameLower}-walletid--"]').each(function() { $(this).on('change', function(e) {
  nullableRadioCheckChanged(e, '{$formNameLower}-walletid-'); //, '{ modelWalletID_id}');
  checkWalletSelection();
}); });
checkWalletSelection();
JS;
      $this->registerJs($js, \yii\web\View::POS_READY);
    }

    //online payment
    $builder->fields([
      "<div id='row-gatewaytype' "
        . (empty($walletItems) ? '' : "style='display:none'")
        . ">",
    ]);

    if (empty($walletItems) == false) {
      $builder->fields(["<hr>"]);
    }

    $gatewayTypes = OnlinePaymentModel::getAllowedTypes();
    // if ((count($gatewayTypes) == 1) && empty($model->gatewayType)) {
    //   $model->gatewayType = array_keys($gatewayTypes)[0];
    // }

    $builder->fields([
      "<p>روش پرداخت:</p>",
      [
        'gatewayType',
        'type' => FormBuilder::FIELD_RADIOLIST,
        'data' => $gatewayTypes,
        'label' => false,
        // 'widgetOptions' => [
        //   'inline' => true,
        // ],
      ],
      "</div>",
    ]);
  ?>

  <?php $builder->beginFooter(); ?>
    <div class="card-footer">
      <div class="float-end">
        <?= Html::activeSubmitButton($model, Yii::t('aaa', 'Payment')) ?>
      </div>
      <div>
        <?= Html::formErrorSummary($model); ?>
      </div>
      <div class="clearfix"></div>
    </div>
  <?php $builder->endFooter(); ?>

  <?php
    $builder->render();
    $form->endForm(); //ActiveForm::end();
  ?>
</div>
