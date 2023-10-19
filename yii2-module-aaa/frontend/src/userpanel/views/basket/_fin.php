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

<div class='card'>
  <div class='card-header'>
    <div class="float-end"></div>
    <div class='card-title'><?= Yii::t('aaa', 'Checkout') ?></div>
    <div class="clearfix"></div>
  </div>

  <div class='card-body'>
    <?php
      if ($model->physicalCount > 0) {
        echo "<p>delivery</p>";
      }

      if (empty($model->walletID) == false) {
        echo "<p>wallet</p>";
      }

      if (empty($model->gatewayType) == false) {
        echo "<p>gateway</p>";
      }

      echo Html::submitButton('ثبت سفارش', [
        'class' => ['btn', 'btn-sm', 'btn-success'],
      ]);
      // echo Html::a('پرداخت و ثبت سفارش', ['checkout'], [
      //   'class' => ['btn', 'btn-sm', 'btn-success', 'd-block'],
      // ]);
    ?>
  </div>

</div>
