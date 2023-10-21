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

<div class='card border-success'>
  <div class='card-header bg-success text-white'>
    <div class="float-end"></div>
    <div class='card-title'><?= Yii::t('aaa', 'Delivery Type') ?></div>
    <div class="clearfix"></div>
  </div>

  <div class='card-body'>
    <?php
      $deliveryMethodModels = DeliveryMethodModel::find()
        ->andWhere(['dlvStatus' => enuDeliveryMethodStatus::Active])
        ->all();

      if (empty($deliveryMethodModels)) {
        throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', 'Delivery methods are not defined.'));
      }

      $deliveryMethods = [];
      foreach ($deliveryMethodModels as $item) {
        $name = $item->dlvName;

        // if ($item->dlvType == enuDeliveryMethodType::SendToCustomer)
        if (empty($item->dlvAmount) == false)
          $name .= " : " . Yii::$app->formatter->asToman($item->dlvAmount);

        $deliveryMethods += [
          $item->dlvID => $name
        ];
      }

      if ((count($deliveryMethods) == 1) && empty($model->deliveryMethod)) {
        $model->deliveryMethod = array_keys($deliveryMethods)[0];
      }

      echo $form
        ->field($model, 'deliveryMethod')
        ->label(false)
        ->radioList($deliveryMethods);
    ?>
  </div>

  <div class='card-footer justify-content-end'>
    <?php
      echo Html::submitButton('بعدی', [
        'class' => ['btn', 'btn-sm', 'btn-success'],
      ]);
    ?>
  </div>

</div>
