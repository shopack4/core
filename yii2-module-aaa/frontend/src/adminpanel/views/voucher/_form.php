<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\helpers\Url;
use shopack\base\frontend\widgets\Select2;
use shopack\base\frontend\widgets\DepDrop;
use shopack\base\frontend\helpers\Html;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\frontend\widgets\ActiveForm;
use shopack\base\frontend\widgets\FormBuilder;
use shopack\aaa\common\enums\enuVoucherStatus;
use yii\web\JsExpression;

// \shopack\base\frontend\DynamicParamsFormAsset::register($this);
?>

<div class='voucher-form'>
	<?php
		$form = ActiveForm::begin([
			'model' => $model,
		]);

		$builder = $form->getBuilder();

		$builder->fields([
			[
				'vchStatus',
				'type' => FormBuilder::FIELD_RADIOLIST,
				'data' => enuVoucherStatus::listData('form'),
				'widgetOptions' => [
					'inline' => true,
				],
			],
			['vchName'],
		]);

		$pluginCategories = [];
		$result = HttpHelper::callApi('aaa/voucher/plugin-list');
		if ($result[0] == 200) {
			foreach ($result[1] as $k => $v) {
				$pluginCategories = array_merge($pluginCategories, [
					$k => Yii::t('aaa', $k), //array_keys($v),
				]);
			}
		}

		$builder->fields([
			[
				'vchPluginType',
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => Select2::class,
				'widgetOptions' => [
					'data' => $pluginCategories,
					'options' => [
						'placeholder' => Yii::t('app', '-- Choose --'),
						'dir' => 'rtl',
					],
				],
			],
		]);

		$loadingText = Yii::t('app', 'Loading...'); //"<div class='text-center'><img src='/images/loading17.gif' alt='Loading...'></div>";

		$getVoucherParamsSchemaUrl = Url::to(['plugin-params-schema']) . '?key=';
		$strGtwPluginParameters = '{}';
		if (($model->vchPluginName !== null) && ($model->vchPluginParameters !== null))
			$strGtwPluginParameters = json_encode($model->vchPluginParameters);

		$getVoucherRestrictionsSchemaUrl = Url::to(['plugin-restrictions-schema']) . '?key=';
		$strGtwRestrictions = '{}';
		if (($model->vchPluginName !== null) && ($model->vchRestrictions !== null))
			$strGtwRestrictions = json_encode($model->vchRestrictions);

		$builder->fields([
			'vchPluginName',
			'type' => FormBuilder::FIELD_WIDGET,
			'widget' => DepDrop::class,
			'widgetOptions' => [
				'type' => DepDrop::TYPE_SELECT2,
				'options' => [
					'placeholder' => Yii::t('app', '-- Choose --'),
					'dir' => 'rtl',
				],
				'pluginOptions' => [
					'depends' => ['vouchermodel-vchplugintype'],
					'initialize' => true,
					'initDepends' => ['vouchermodel-vchplugintype'],
					'url' => Url::to(['plugin-list', 'sel' => $model->vchPluginName]),
					'loadingText' => Yii::t('app', 'Loading...'),
				],
				'select2Options' => [
					// 'hideSearch' => true,
					'pluginEvents' => [
						'depdrop:afterChange' => "function(e) {
							var sel = $('#vouchermodel-vchpluginname').val();
							if ((sel == null) || (sel == '')) {
								$('#params-container').empty();
								$('#restrictions-container').empty();
							} else {
								$('#vouchermodel-vchpluginname').trigger('select2:select');
							}
							return true;
						}",
						'select2:select' => "function(e) {
							createDynamicParamsFormUI($(this).val(), \"{$loadingText}\", '{$getVoucherParamsSchemaUrl}', 'vouchermodel', 'vchpluginparameters', 'VoucherModel', 'vchPluginParameters', {$strGtwPluginParameters}, 'params-container', 3);

							createDynamicParamsFormUI($(this).val(), \"{$loadingText}\", '{$getVoucherRestrictionsSchemaUrl}', 'vouchermodel', 'vchrestrictions', 'VoucherModel', 'vchRestrictions', {$strGtwRestrictions}, 'restrictions-container', 3);

							return true;
						}",
					],
				],
			],
		]);
	?>

	<?php $builder->beginField(); ?>
		<hr>پارامترها<hr>
		<div id='params-container' class='row'></div>
		<hr>محدودیت‌ها<hr>
		<div id='restrictions-container' class='row'></div>
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
