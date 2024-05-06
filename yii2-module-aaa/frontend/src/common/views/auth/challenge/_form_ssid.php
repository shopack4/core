<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\frontend\common\helpers\Html;
?>

<div class='challenge-ssid-form'>
	<?php
		$builder->fields([
			[
				'code',
				'label' => 'کد ملی',
				'widgetOptions' => [
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
				<?= Html::activeSubmitButton($model, Yii::t('aaa', 'Approve'), ['class' => ['btn-sm']]) ?>
			</div>
			<div>
				<?= Html::formErrorSummary($model); ?>
			</div>
			<div class="clearfix"></div>
		</div>
	<?php $builder->endFooter(); ?>
</div>
