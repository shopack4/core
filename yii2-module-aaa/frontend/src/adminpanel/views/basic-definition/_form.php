<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\frontend\common\widgets\Select2;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\ActiveForm;
use shopack\base\frontend\common\widgets\FormBuilder;
use shopack\aaa\common\enums\enuBasicDefinitionType;
use shopack\aaa\common\enums\enuBasicDefinitionStatus;
?>

<div class='basic-definition-form'>
	<?php
		$form = ActiveForm::begin([
			'model' => $model,
			'formConfig' => [
				'labelSpan' => 4,
			],
		]);

		$builder = $form->getBuilder();

		$builder->fields([
			[
				'bdfStatus',
				'type' => FormBuilder::FIELD_RADIOLIST,
				'data' => enuBasicDefinitionStatus::listData('form'),
				'widgetOptions' => [
					'inline' => true,
				],
			],
			[
				'bdfType',
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => Select2::class,
				'widgetOptions' => [
					'data' => enuBasicDefinitionType::listData(),
					'options' => [
						'placeholder' => Yii::t('app', '-- Choose --'),
						'dir' => 'rtl',
					],
				]
			],
			[
				'bdfName',
			],
			[
				'bdfName',
				'type' => FormBuilder::FIELD_TEXT_MULTILANGUAGE,
				'fieldOptions' => [
					'I18NDataFieldName' => 'bdfI18NData',
					// 'generateNoLanguageField' => true,
				],
			],
		]);
	?>

	<?php $builder->beginField(); ?>
		<div id='params-container' class='row offset-md-2'></div>
	<?php $builder->endField(); ?>

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
