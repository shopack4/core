<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\ArrayHelper;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\ActiveForm;
use shopack\base\frontend\common\widgets\FormBuilder;
use shopack\base\frontend\common\widgets\Select2;
use shopack\base\frontend\common\widgets\DepDrop;
use shopack\base\frontend\common\widgets\datetime\DatePicker;
use shopack\aaa\common\enums\enuGender;
use shopack\aaa\frontend\common\models\GeoCountryModel;
use shopack\aaa\common\enums\enuUserEducationLevel;
use shopack\aaa\common\enums\enuUserMaritalStatus;
use shopack\aaa\common\enums\enuUserMilitaryStatus;
use shopack\aaa\frontend\common\widgets\form\GeoCityOrVillageChooseFormField;
use shopack\aaa\frontend\common\widgets\form\GeoCountryChooseFormField;
use shopack\aaa\frontend\common\widgets\form\GeoStateChooseFormField;
use shopack\aaa\frontend\common\widgets\form\GeoTownChooseFormField;

?>

<div class='user-form'>
	<?php
		$form = ActiveForm::begin([
			'model' => $model,
		]);

		$formName = strtolower($model->formName());

		$builder = $form->getBuilder();

		$builder->fields([
			['usrGender',
			'type' => FormBuilder::FIELD_RADIOLIST,
				'data' => enuGender::listData(),
				'widgetOptions' => [
					'inline' => true,
				],
			],
			['@col' => 2],
			['usrFirstName'],
			['usrFirstName_en'],
			['usrLastName'],
			['usrLastName_en'],
			['usrFatherName'],
			['usrFatherName_en'],
			['usrSSID'],
			['usrBirthCertID'],

			GeoCityOrVillageChooseFormField::field($this, $model, 'usrBirthCityID'),
			[
				'usrBirthDate',
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
			['usrZipCode'],
			['@col' => 1],
			[
				'usrHomeAddress',
				'type' => FormBuilder::FIELD_TEXTAREA,
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
