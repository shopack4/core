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
use shopack\base\frontend\common\widgets\FormBuilder;

?>

<div class='delivery-method-form'>
	<?php
		$form = ActiveForm::begin([
			'model' => $model,
			'fieldConfig' => [
				'labelSpan' => 3,
			],
		]);

		$builder = $form->getBuilder();

    $deliveryMethodModels = DeliveryMethodModel::find()
      ->andWhere(['dlvStatus' => enuDeliveryMethodStatus::Active])
      ->all();

    if (empty($deliveryMethodModels)) {
      throw new \yii\web\HttpException($resultStatus, Yii::t('aaa', 'Delivery methods are not defined.'));
    }

    $deliveryMethods = [];
    foreach ($deliveryMethodModels as $item) {
      $name = $item->dlvName . ' : ';

      // if ($item->dlvType == enuDeliveryMethodType::SendToCustomer)
      if (empty($item->dlvAmount))
        $name .= 'بدون هزینه';
      else
        $name .= Yii::$app->formatter->asToman($item->dlvAmount);

      $deliveryMethods += [
        $item->dlvID => $name
      ];
    }

    if ((count($deliveryMethods) == 1) && empty($model->deliveryMethod)) {
      $model->deliveryMethod = array_keys($deliveryMethods)[0];
    }

    $builder->fields([
      [
        'deliveryMethod',
				'type' => FormBuilder::FIELD_RADIOLIST,
				'data' => $deliveryMethods,
        'label' => false,
				'widgetOptions' => [
					'inline' => true,
				],
      ],
    ]);

    // echo $form
    //   ->field($model, 'deliveryMethod')
    //   ->label(false)
    //   ->radioList($deliveryMethods);
  ?>

  <?php $builder->beginFooter(); ?>
    <div class="card-footer">
      <div class="float-end">
        <?= Html::activeSubmitButton($model, Yii::t('app', 'Save')) ?>
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
