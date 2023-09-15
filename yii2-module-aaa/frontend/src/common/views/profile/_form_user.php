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

			[
				'usrCountryID',
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => Select2::class,
				'widgetOptions' => [
					'data' => ArrayHelper::map(GeoCountryModel::find()->asArray()->noLimit()->all(), 'cntrID', 'cntrName'),
					'options' => [
						'placeholder' => Yii::t('app', '-- Choose --'),
						'dir' => 'rtl',
					],
					'pluginOptions' => [
						'allowClear' => true,
					],
				],
			],
			[
				'usrStateID',
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => DepDrop::class,
				'widgetOptions' => [
					'type' => DepDrop::TYPE_SELECT2,
					'options' => [
						'placeholder' => Yii::t('app', '-- Choose --'),
						'dir' => 'rtl',
					],
					'select2Options' => [
						'pluginOptions' => [
							'allowClear' => true,
						],
					],
					'pluginOptions' => [
						'depends' => ["{$formName}-usrcountryid"],
						'initialize' => true,
						// 'initDepends' => ["{$formName}-usrcountryid"],
						'url' => Url::to(['/aaa/geo-state/depdrop-list', 'sel' => $model->usrStateID]),
						'loadingText' => Yii::t('app', 'Loading...'),
					],
				],
			],
			[
				'usrCityOrVillageID',
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => DepDrop::class,
				'widgetOptions' => [
					'type' => DepDrop::TYPE_SELECT2,
					'options' => [
						'placeholder' => Yii::t('app', '-- Choose --'),
						'dir' => 'rtl',
					],
					'select2Options' => [
						'pluginOptions' => [
							'allowClear' => true,
						],
					],
					'pluginOptions' => [
						'depends' => ["{$formName}-usrstateid"],
						'initialize' => true,
						// 'initDepends' => ["{$formName}-usrcountryid", "{$formName}-usrstateid"],
						'url' => Url::to(['/aaa/geo-city-or-village/depdrop-list', 'sel' => $model->usrCityOrVillageID]),
						'loadingText' => Yii::t('app', 'Loading...'),
					],
				],
			],
			[
				'usrTownID',
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => DepDrop::class,
				'widgetOptions' => [
					'type' => DepDrop::TYPE_SELECT2,
					'options' => [
						'placeholder' => Yii::t('app', '-- Choose --'),
						'dir' => 'rtl',
					],
					'select2Options' => [
						'pluginOptions' => [
							'allowClear' => true,
						],
					],
					'pluginOptions' => [
						'depends' => ["{$formName}-usrcityorvillageid"],
						'initialize' => true,
						'initDepends' => ["{$formName}-usrcountryid"], //, "{$formName}-usrstateid", "{$formName}-usrcityorvillageid"],
						'url' => Url::to(['/aaa/geo-town/depdrop-list', 'sel' => $model->usrTownID]),
						'loadingText' => Yii::t('app', 'Loading...'),
					],
				],
			],
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
