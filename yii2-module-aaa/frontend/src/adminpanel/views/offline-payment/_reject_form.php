<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\aaa\common\enums\enuBasicDefinitionType;
use shopack\aaa\frontend\common\models\BasicDefinitionModel;
use shopack\base\common\helpers\ArrayHelper;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\ActiveForm;
use shopack\base\frontend\common\widgets\FormBuilder;
?>

<div class='reject-form'>
	<?php
		$form = ActiveForm::begin([
			'model' => $model,
			// 'formConfig' => [
			// 	'labelSpan' => 4,
			// ],
		]);

		$form->registerActiveHiddenInput($model, 'ofpID');

		$builder = $form->getBuilder();

		$builder->fields([
			Yii::t('aaa', 'Are you sure you want to REJECT this item?'),
		]);

		$reasons = ArrayHelper::map(BasicDefinitionModel::find()
			->where(['bdfType' => enuBasicDefinitionType::OfflinePaymentRejectReason])
			->noLimit()
			->asArray()
			->all(),
			'bdfID',
			'bdfName'
		);

		$builder->fields([
			'<hr>',
			[
				'ofpRejectReasonIDs',
				'type' => FormBuilder::FIELD_CHECKBOXLIST,
				'data' => $reasons,
				'widgetOptions' => [
					'inline' => false,
				],
			],
			['ofpComment'],
		]);
	?>

	<?php $builder->beginFooter(); ?>
		<div class="card-footer">
			<div class="float-end">
				<?= Html::activeSubmitButton($model, Yii::t('aaa', 'Reject')) ?>
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
