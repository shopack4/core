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

$this->title = Yii::t('aaa', 'Shopping Card - Checkout');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="checkout w-100">
  <div class='card'>
		<div class='card-header'>
			<!-- <div class="float-end"></div> -->
      <div class='card-title'><?= Html::encode($this->title) ?></div>
			<!-- <div class="clearfix"></div> -->
		</div>

    <div class='card-body'>
			<?php
				$form = ActiveForm::begin([
					'model' => $model,
				]);

				echo $form
					->field($model, 'currentStep')
					->label(false)
					->hiddenInput();
			?>

			<div class='row'>
        <div class='col-8'>
					<?php
						foreach ($model->steps as $step) {
							echo Yii::$app->controller->renderPartial('_' . $step . '_' . ($step == $model->currentStep ? 'edit' : 'view'), [
								'form' => $form,
								'model' => $model,
								'editmode' => ($step == $model->currentStep),
							]);
						}
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
              // echo Html::submitButton('ثبت سفارش', [
              //   'class' => ['btn', 'btn-sm', 'btn-success', 'w-100'],
              // ]);
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
