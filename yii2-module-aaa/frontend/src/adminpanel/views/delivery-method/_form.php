<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\ActiveForm;
use shopack\base\frontend\common\widgets\FormBuilder;
use shopack\aaa\common\enums\enuDeliveryMethodType;
use shopack\aaa\common\enums\enuDeliveryMethodStatus;
?>

<div class='delivery-method-form'>
	<?php
		$form = ActiveForm::begin([
			'model' => $model,
			'formConfig' => [
				'labelSpan' => 4,
			],
		]);

		$builder = $form->getBuilder();

		$builder->fields([
			['dlvStatus',
				'type' => FormBuilder::FIELD_RADIOLIST,
				'data' => enuDeliveryMethodStatus::listData('form'),
				'widgetOptions' => [
					'inline' => true,
				],
			],
			['dlvName'],
			[
				'dlvName',
				'type' => FormBuilder::FIELD_TEXT_MULTILANGUAGE,
				'fieldOptions' => [
					'I18NDataFieldName' => 'dlvI18NData',
					// 'generateNoLanguageField' => true,
				],
			],
			['dlvType',
				'type' => FormBuilder::FIELD_RADIOLIST,
				'data' => enuDeliveryMethodType::listData('form'),
				'widgetOptions' => [
					'inline' => true,
				],
			],
			['dlvAmount',
				'fieldOptions' => [
					'addon' => [
						'append' => [
							'content' => 'تومان',
						],
					],
				],
				// 'visibleConditions' => [
				// 	'dlvType' => enuDeliveryMethodType::SendToCustomer,
				// ],
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
