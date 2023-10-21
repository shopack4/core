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
    <div class='card-title'><?= Yii::t('aaa', 'Payment Method') ?></div>
    <div class="clearfix"></div>
  </div>

  <div class='card-body'>
    <?php
      echo Html::activeHiddenInput($model, 'walletID');
      $walletModel = $model->walletModel();
      if ($walletModel != null) {
        echo 'برداشت از کیف پول : ' . $walletModel->walName . "<br>";
      }

      echo Html::activeHiddenInput($model, 'gatewayType');
      if (empty($model->gatewayType) == false) {
        $gatewayTypes = OnlinePaymentModel::getAllowedTypes();
        echo 'روش پرداخت : ' . $gatewayTypes[$model->gatewayType] . "<br>";
      }
    ?>
  </div>

</div>
