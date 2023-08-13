<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\helpers\Url;
use shopack\base\frontend\widgets\ActiveForm;
use shopack\base\frontend\helpers\Html;
use shopack\base\common\helpers\ArrayHelper;
use shopack\base\frontend\widgets\FormBuilder;
use borales\extensions\phoneInput\PhoneInput;
use shopack\aaa\common\enums\enuGender;
use shopack\base\frontend\widgets\Select2;
use shopack\base\frontend\widgets\DepDrop;
use shopack\aaa\frontend\common\models\GeoCountryModel;
use shopack\aaa\frontend\common\models\RoleModel;
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
			['usrRoleID',
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => Select2::class,
				'widgetOptions' => [
					'data' => ArrayHelper::map(RoleModel::find()->asArray()->all(), 'rolID', 'rolName'),
					'options' => [
						'placeholder' => Yii::t('app', '-- Choose --'),
						'dir' => 'rtl',
					],
				],
			],
			['@static' => '<hr>'],
			['usrEmail'],
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
			['usrSSID']
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

		$builder->fields([
			['usrCountryID',
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => Select2::class,
				'widgetOptions' => [
					'data' => ArrayHelper::map(GeoCountryModel::find()->asArray()->all(), 'cntrID', 'cntrName'),
					'options' => [
						'placeholder' => Yii::t('app', '-- Choose --'),
						'dir' => 'rtl',
					],
					'pluginOptions' => [
						'allowClear' => true,
					],
				],
			],
			['usrStateID',
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
			['usrCityOrVillageID',
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
						'url' => Url::to(['/aaa/geo-city-or-village/depdrop-list', 'sel' => $model->usrStateID]),
						'loadingText' => Yii::t('app', 'Loading...'),
					],
				],
			],
			['usrTownID',
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
						'url' => Url::to(['/aaa/geo-town/depdrop-list', 'sel' => $model->usrStateID]),
						'loadingText' => Yii::t('app', 'Loading...'),
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
