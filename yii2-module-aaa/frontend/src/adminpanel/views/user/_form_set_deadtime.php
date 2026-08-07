<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\ActiveForm;
use shopack\base\frontend\common\widgets\FormBuilder;
use shopack\base\frontend\common\widgets\datetime\DatePicker;
use shopack\aaa\frontend\common\models\UserModel;
?>

<div class='set-deadtime-form'>
	<?php
	$form = ActiveForm::begin([
		'model' => $model,
	]);

	$builder = $form->getBuilder();

	$userModel = UserModel::findOne($model->userID);

	$builder->fields([
		[
			'userID',
			'type' => FormBuilder::FIELD_STATIC,
			'staticValue' => $userModel->displayName(),
		],
	]);

	$builder->fields([
		[
			'deadAt',
			'type' => FormBuilder::FIELD_WIDGET,
			'widget' => DatePicker::class,
			'fieldOptions' => [
				'addon' => [
					'append' => [
						'content' => '<i class="far fa-calendar-alt"></i>',
					],
				],
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