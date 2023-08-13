<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use borales\extensions\phoneInput\PhoneInput;
use shopack\base\frontend\helpers\Html;
use shopack\base\frontend\widgets\ActiveForm;
use shopack\base\frontend\widgets\FormBuilder;
use shopack\aaa\common\enums\enuGender;
?>

<div class='user-form'>
	<?php
		$form = ActiveForm::begin([
			'model' => $model,
		]);

		$builder = $form->getBuilder();

		//https://github.com/Borales/yii2-phone-input
		$builder->fields([
			[
				'usrEmail',
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => \yii\widgets\MaskedInput::class,
				'widgetOptions' => [
					'options' => [
						'maxlength' => true,
						'style' => 'direction:ltr;',
					],
					'clientOptions' => [
						'alias' => 'email',
					],
				],
			],
			[
				'usrMobile',
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => PhoneInput::class,
				'widgetOptions' => [
					'jsOptions' => [
						'nationalMode' => false,
						'preferredCountries' => ['ir'], //, 'us'],
						'excludeCountries' => ['il'],
					],
					'options' => [
						'style' => 'direction:ltr',
					],
				],
			],
			[
				'usrSSID',
			],
		]);

		// echo $form->field($model, 'usrGender')
		// 	->radioList(enuGender::listData(), [
		// 		'inline' => true,
		// 	]);
		// echo $form->field($model, 'usrBirthDate')->widget(DatePicker::className());

		$builder->fields([
			['@static' => '<hr>'],
			['usrGender',
				'type' => FormBuilder::FIELD_RADIOLIST,
				'data' => enuGender::listData(),
				'widgetOptions' => [
					'inline' => true,
				],
			],
			['usrFirstName'],
			['usrLastName'],
		]);

		if ($model->isNewRecord) {
			$builder->fields([
				['usrPassword',
					'type' => FormBuilder::FIELD_PASSWORD,
					'widgetOptions' => [
						'style' => 'direction:ltr',
					],
				],
				['usrRetypePassword',
					'type' => FormBuilder::FIELD_PASSWORD,
					'widgetOptions' => [
						'style' => 'direction:ltr',
					],
				],
			]);
		}

		//------------------- GDV --------------------
		// echo $form->field($model, 'usrGdvID', [
		// 	'options' => [
		// 		'class' => 'form-group form-control-double-height',
		// 	],
		// ])
		// ->widget(GdvWidget::className(), [
		// 	// '50,60,70,90'
		// 	'gdts' => implode(',', [
		// 		// enuGdt::GROUP,
		// 		// enuGdt::COUNTRY,
		// 		// enuGdt::STATE,
		// 		// enuGdt::SUBSTATE,
		// 		// enuGdt::BAKHSH,
		// 		enuGdt::CITY,
		// 		enuGdt::DEHESTAN,
		// 		enuGdt::DISTRICT,
		// 		// enuGdt::VILLAGE,
		// 		enuGdt::COUNTY,
		// 	]),
		// ]);

		// echo $form->field($model, 'usrHomeAddress')->textArea(['rows' => '3', 'maxlength' => true]);
		// echo $form->field($model, 'usrZipCode')->textInput(['maxlength' => true, 'style' => 'direction:ltr']);
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
