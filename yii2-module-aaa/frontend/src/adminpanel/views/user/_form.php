<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use borales\extensions\phoneInput\PhoneInput;
use shopack\base\frontend\common\widgets\ActiveForm;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\common\helpers\ArrayHelper;
use shopack\base\frontend\common\widgets\FormBuilder;
use shopack\aaa\common\enums\enuGender;
use shopack\aaa\common\enums\enuUserEducationLevel;
use shopack\aaa\common\enums\enuUserMaritalStatus;
use shopack\aaa\common\enums\enuUserMilitaryStatus;
use shopack\base\frontend\common\widgets\Select2;
use shopack\aaa\frontend\common\models\RoleModel;
use shopack\aaa\frontend\common\widgets\form\GeoCityOrVillageChooseFormField;
use shopack\aaa\frontend\common\widgets\form\GeoCountryChooseFormField;
use shopack\aaa\frontend\common\widgets\form\GeoStateChooseFormField;
use shopack\aaa\frontend\common\widgets\form\GeoTownChooseFormField;
use shopack\base\frontend\common\widgets\datetime\DatePicker;

// 'usrBirthCertID',
?>

<div class='user-form'>
	<?php
		$form = ActiveForm::begin([
			'model' => $model,
		]);

		$formName = strtolower($model->formName());

		$builder = $form->getBuilder();

		//https://github.com/Borales/yii2-phone-input
		$builder->fields([
			['usrGender',
				'type' => FormBuilder::FIELD_RADIOLIST,
				'data' => enuGender::listData(),
				'widgetOptions' => [
					'inline' => true,
				],
			],
			['@col' => 2],
			[
				'usrEmail',
				'widgetOptions' => [
					'style' => 'direction:ltr',
				],
			],
			['usrMobile',
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
				'widgetOptions' => [
					'style' => 'direction:ltr',
				],
			],
			['usrRoleID',
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => Select2::class,
				'widgetOptions' => [
					'data' => ArrayHelper::map(RoleModel::find()->asArray()->noLimit()->all(), 'rolID', 'rolName'),
					'options' => [
						'placeholder' => Yii::t('app', '-- Choose --'),
						'dir' => 'rtl',
					],
				],
			],
		]);

		// echo $form->field($model, 'usrGender')
		// 	->radioList(enuGender::listData(), [
		// 		'inline' => true,
		// 	]);
		// echo $form->field($model, 'usrBirthDate')->widget(DatePicker::className());

		$builder->fields([
			['@static' => '<hr>'],
			['usrFirstName'],
			[
				'usrFirstName_en',
				'widgetOptions' => [
					'style' => 'direction:ltr',
				],
			],
			['usrLastName'],
			[
				'usrLastName_en',
				'widgetOptions' => [
					'style' => 'direction:ltr',
				],
			],
			['usrFatherName'],
			[
				'usrFatherName_en',
				'widgetOptions' => [
					'style' => 'direction:ltr',
				],
			],

			GeoCityOrVillageChooseFormField::field($this, $model, 'usrBirthCityID'),
			['usrBirthDate',
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => DatePicker::class,
			],
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

		$builder->fields([
			['usrEducationLevel',
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => Select2::class,
				'widgetOptions' => [
					'data' => enuUserEducationLevel::getList(),
					'options' => [
						'placeholder' => Yii::t('app', '-- Choose --'),
						'dir' => 'rtl',
					],
					'pluginOptions' => [
						'allowClear' => true,
					],
				],
			],
			['usrFieldOfStudy'],
			['usrYearOfGraduation'],
			['usrEducationPlace'],
			['usrMaritalStatus',
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => Select2::class,
				'widgetOptions' => [
					'data' => enuUserMaritalStatus::getList(),
					'options' => [
						'placeholder' => Yii::t('app', '-- Choose --'),
						'dir' => 'rtl',
					],
					'pluginOptions' => [
						'allowClear' => true,
					],
				],
			],
			['usrMilitaryStatus',
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => Select2::class,
				'widgetOptions' => [
					'data' => enuUserMilitaryStatus::getList(),
					'options' => [
						'placeholder' => Yii::t('app', '-- Choose --'),
						'dir' => 'rtl',
					],
					'pluginOptions' => [
						'allowClear' => true,
					],
				],
			],
		]);

		$builder->fields([
			GeoCountryChooseFormField::field($this, $model, 'usrCountryID', true, false),
			GeoStateChooseFormField::field($this, $model, 'usrStateID', true, false, 'usrCountryID'),
			GeoCityOrVillageChooseFormField::field($this, $model, 'usrCityOrVillageID', true, false, 'usrStateID'),
			GeoTownChooseFormField::field($this, $model, 'usrTownID', true, false, 'usrCityOrVillageID'),
		]);

		$builder->fields([
			[
				'usrZipCode',
				'widgetOptions' => [
					'style' => 'direction:ltr',
				],
			],
			['usrBirthCertID'],
			['@col' => 1],
			[
				'usrHomeAddress',
				'type' => FormBuilder::FIELD_TEXTAREA,
			],
			['usrPhones'],

			[
				'usrWorkAddress',
				'type' => FormBuilder::FIELD_TEXTAREA,
			],
			['usrWorkPhones'],

			[
				'usrWebsite',
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
