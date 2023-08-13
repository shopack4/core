<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\frontend\widgets\ActiveForm;
use shopack\base\frontend\helpers\Html;
use shopack\base\frontend\widgets\FormBuilder;
?>

<div class='email-form'>
	<?php
		$form = ActiveForm::begin([
			'model' => $model,
		]);

		$builder = $form->getBuilder();

		$builder->fields([
			[
				'email',
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => \yii\widgets\MaskedInput::class,
				'widgetOptions' => [
					'options' => [
						'maxlength' => true,
						'style' => 'direction:ltr;',
					],
					'clientOptions' => [
						'alias' => 'email',
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
