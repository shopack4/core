<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\FormBuilder;
use shopack\base\frontend\common\widgets\datetime\DatePicker;
?>

<div class='challenge-birthDate-form'>
	<?php
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
				<?= Html::formErrorSummary($model); ?>
			</div>
			<div class="clearfix"></div>
		</div>
	<?php $builder->endFooter(); ?>
</div>
