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
use shopack\aaa\frontend\common\models\DeliveryMethodModel;
use shopack\aaa\common\enums\enuDeliveryMethodStatus;
use shopack\aaa\common\enums\enuDeliveryMethodType;
?>

<div class='card'>
  <div class='card-header'>
    <div class="float-end"></div>
    <div class='card-title'><?= Yii::t('aaa', 'Delivery Type') ?></div>
    <div class="clearfix"></div>
  </div>

  <div class='card-body'>
    <?php
      echo Html::activeHiddenInput($model, 'deliveryMethod');

      $deliveryMethodModel = $model->deliveryMethodModel();

      echo $deliveryMethodModel->dlvName;

      if (empty($deliveryMethodModel->dlvAmount) == false)
        echo " : " . Yii::$app->formatter->asToman($deliveryMethodModel->dlvAmount);
    ?>
  </div>

</div>
