<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\ActiveForm;
?>

<div class='accept-form'>
	<?php
		$form = ActiveForm::begin([
			'model' => $model,
			// 'formConfig' => [
			// 	'labelSpan' => 4,
			// ],
		]);

		$form->registerActiveHiddenInput($model, 'posted');

		$builder = $form->getBuilder();

		if (empty($model->message) == false) {
			$builder->fields([
				$model->message
			]);
		}
	?>

	<?php $builder->beginFooter(); ?>
		<div class="card-footer">
			<div class="float-end">
				<?= Html::activeSubmitButton($model, Yii::t('aaa', 'Approve')) ?>
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
