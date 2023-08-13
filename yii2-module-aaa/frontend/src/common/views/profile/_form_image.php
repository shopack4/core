<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\frontend\helpers\Html;
use shopack\base\frontend\widgets\ActiveForm;
use shopack\base\frontend\widgets\FormBuilder;
?>

<div class='image-form'>
	<?php
		$form = ActiveForm::begin([
			'model' => $model,
		]);

		$model->postback = 123;
		echo Html::activeHiddenInput($model, 'postback');

		$builder = $form->getBuilder();

		$builder->fields([
			[
				'image',
				'type' => FormBuilder::FIELD_FILE,
			],
		]);
	?>

	<?php $builder->beginFooter(); ?>
		<div class="card-footer">
			<div>
				<?= Html::formErrorSummary($model); ?>
			</div>
			<div class="float-end">
				<?= Html::activeSubmitButton($model) ?>
			</div>
			<div class="clearfix"></div>
		</div>
	<?php $builder->endFooter(); ?>

	<?php
		$builder->render();
		$form->endForm(); //ActiveForm::end();
	?>
</div>
