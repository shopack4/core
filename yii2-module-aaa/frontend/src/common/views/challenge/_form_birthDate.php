<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use borales\extensions\phoneInput\PhoneInput;
use shopack\base\frontend\common\widgets\ActiveForm;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\datetime\DatePicker;
use shopack\base\frontend\common\widgets\FormBuilder;
?>

<div class='challenge-birthDate-form'>
	<?php
		$noForm = false;
		if (isset($form) == false) {
			$noForm = true;
			$form = ActiveForm::begin([
				'model' => $model,
				'fieldConfig' => [
					'labelSpan' => 3,
				],
				// 'donewait' => 10,
				// 'modalDoneInternalScript_OK' => "setTimeout(function() { $('#mobile-approve-link').click(); }, 500);",
			]);

			$builder = $form->getBuilder();
		}

		$builder->fields([
			[
				'code',
				'label' => 'تاریخ تولد',
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => DatePicker::class,
				'fieldOptions' => [
					'addon' => [
						'append' => [
							'content' => '<i class="far fa-calendar-alt"></i>',
						],
					],
				],
			]
		]);
	?>

	<?php $builder->beginFooter(); ?>
		<div class="card-footer">
			<div class="float-end">
				<?= Html::activeSubmitButton($model, Yii::t('aaa', 'Approve'), ['class' => ['btn-sm']]) ?>
			</div>
			<div>
				<?php
					if (empty($messageText) == false)
						echo $messageText;
				?>
				<?= Html::formErrorSummary($model); ?>
			</div>
			<div class="clearfix"></div>
		</div>
	<?php $builder->endFooter(); ?>

	<?php
		if ($noForm) {
			$builder->render();
			$form->endForm(); //ActiveForm::end();
		}
	?>
</div>
