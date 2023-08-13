<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\frontend\helpers\Html;
use shopack\base\frontend\widgets\ActiveForm;
use shopack\base\frontend\widgets\FormBuilder;

$this->title = Yii::t('aaa', 'Reset Password');
$this->params['breadcrumbs'][] = $this->title;
?>

<div id='passwordResetByForgotCode' class='d-flex justify-content-center'>
	<div class='w-sm-75 card border-primary'>

		<div class='card-header bg-primary text-white'>
			<div class='card-title'><?= Html::encode($this->title) ?></div>
		</div>

		<div class='password-reset-by-forgot-code-form'>
			<?php
				$form = ActiveForm::begin([
					'model' => $model,
				]);

				$builder = $form->getBuilder();

				$builder->fields([
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

	</div>
</div>
