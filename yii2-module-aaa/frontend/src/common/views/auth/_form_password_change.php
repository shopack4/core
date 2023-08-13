<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\frontend\helpers\Html;
use shopack\base\frontend\widgets\ActiveForm;
use shopack\base\frontend\widgets\FormBuilder;
?>

<div class='password-change-form'>
	<?php
		$form = ActiveForm::begin([
			'model' => $model,
		]);

		$builder = $form->getBuilder();

		$builder->fields([
			['curPassword',
				'type' => FormBuilder::FIELD_PASSWORD,
				'widgetOptions' => [
					'style' => 'direction:ltr',
				],
			],
			['newPassword',
				'type' => FormBuilder::FIELD_PASSWORD,
				'widgetOptions' => [
					'style' => 'direction:ltr',
				],
			],
			['retypePassword',
				'type' => FormBuilder::FIELD_PASSWORD,
				'widgetOptions' => [
					'style' => 'direction:ltr',
				],
			],
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
