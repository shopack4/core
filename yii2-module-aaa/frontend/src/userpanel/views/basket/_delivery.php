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
?>

<div class='card <?= $editmode ? 'border-success' : '' ?>'>
  <div class='card-header <?= $editmode ? 'bg-success text-white' : '' ?>'>
    <div class="float-end"></div>
    <div class='card-title'><?= Yii::t('aaa', 'Delivery Type') ?></div>
    <div class="clearfix"></div>
  </div>

  <div class='card-body'>
    <?php
      $deliveryTypes = [
        BasketCheckoutForm::DELIVERY_ReceiveByCustomer => 'دریافت توسط مشتری',
        'd_1' => 'ارسال با پست (50/000 تومان)',
      ];

      if ((count($deliveryTypes) == 1) && empty($model->deliveryType)) {
        $model->deliveryType = array_keys($deliveryTypes)[0];
      }

      echo $form
        ->field($model, 'deliveryType')
        ->label(false)
        ->radioList($deliveryTypes);
    ?>
  </div>

  <?php
    if ($editmode) {
  ?>
    <div class='card-footer justify-content-end'>
      <?php
        echo Html::submitButton('بعدی', [
          'class' => ['btn', 'btn-sm', 'btn-success'],
        ]);
      ?>
    </div>
  <?php
    }
  ?>

</div>
