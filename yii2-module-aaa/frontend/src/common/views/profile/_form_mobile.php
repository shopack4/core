<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use borales\extensions\phoneInput\PhoneInput;
use shopack\base\frontend\widgets\ActiveForm;
use shopack\base\frontend\helpers\Html;
use shopack\base\frontend\widgets\FormBuilder;
?>

<div class='mobile-form'>
	<?php
		$form = ActiveForm::begin([
			'model' => $model,
			// 'donewait' => 10,
			// 'modalDoneInternalScript_OK' => "setTimeout(function() { $('#mobile-approve-link').click(); }, 500);",
		]);

		$builder = $form->getBuilder();

		$builder->fields([
			[
				'mobile',
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => PhoneInput::class,
				'widgetOptions' => [
					'jsOptions' => [
						'nationalMode' => false,
						'preferredCountries' => ['ir'], //, 'us'],
						'excludeCountries' => ['il'],
					],
					'options' => [
						'style' => 'direction:ltr',
					],
				],
			]
		]);
	?>

	<?php $builder->beginFooter(); ?>
		<div class="card-footer">
			<div class="float-end">
				<?= Html::activeSubmitButton($model) ?>
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
