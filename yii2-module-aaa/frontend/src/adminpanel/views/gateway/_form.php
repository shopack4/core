<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use yii\web\JsExpression;
use shopack\base\common\helpers\Url;
use shopack\base\frontend\widgets\Select2;
use shopack\base\frontend\widgets\DepDrop;
use shopack\base\frontend\helpers\Html;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\frontend\widgets\ActiveForm;
use shopack\base\frontend\widgets\FormBuilder;
use shopack\aaa\common\enums\enuGatewayStatus;

// \shopack\base\frontend\DynamicParamsFormAsset::register($this);
?>

<div class='gateway-form'>
	<?php
		$form = ActiveForm::begin([
			'model' => $model,
		]);

		$builder = $form->getBuilder();

		$builder->fields([
			[
				'gtwStatus',
				'type' => FormBuilder::FIELD_RADIOLIST,
				'data' => enuGatewayStatus::listData('form'),
				'widgetOptions' => [
					'inline' => true,
				],
			],
			['gtwName'],
		]);

		$pluginCategories = [];
		$result = HttpHelper::callApi('aaa/gateway/plugin-list');
		if ($result[0] == 200) {
			foreach ($result[1] as $k => $v) {
				$pluginCategories = array_merge($pluginCategories, [
					$k => Yii::t('aaa', $k), //array_keys($v),
				]);
			}
		}

		$builder->fields([
			[
				'gtwPluginType',
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

		$getGatewayParamsSchemaUrl = Url::to(['plugin-params-schema']) . '?key=';
		$strGtwPluginParameters = '{}';
		if (($model->gtwPluginName !== null) && ($model->gtwPluginParameters !== null))
			$strGtwPluginParameters = json_encode($model->gtwPluginParameters);

		$getGatewayRestrictionsSchemaUrl = Url::to(['plugin-restrictions-schema']) . '?key=';
		$strGtwRestrictions = '{}';
		if (($model->gtwPluginName !== null) && ($model->gtwRestrictions !== null))
			$strGtwRestrictions = json_encode($model->gtwRestrictions);

		$builder->fields([
			'gtwPluginName',
			'type' => FormBuilder::FIELD_WIDGET,
			'widget' => DepDrop::class,
			'widgetOptions' => [
				'type' => DepDrop::TYPE_SELECT2,
				'options' => [
					'placeholder' => Yii::t('app', '-- Choose --'),
					'dir' => 'rtl',
				],
				'pluginOptions' => [
					'depends' => ['gatewaymodel-gtwplugintype'],
					'initialize' => true,
					'initDepends' => ['gatewaymodel-gtwplugintype'],
					'url' => Url::to(['plugin-list', 'sel' => $model->gtwPluginName]),
					'loadingText' => Yii::t('app', 'Loading...'),
				],
				'select2Options' => [
					// 'hideSearch' => true,
					'pluginEvents' => [
						'depdrop:afterChange' => "function(e) {
							var sel = $('#gatewaymodel-gtwpluginname').val();
							if ((sel == null) || (sel == '')) {
								$('#params-container').empty();
								$('#restrictions-container').empty();
							} else {
								$('#gatewaymodel-gtwpluginname').trigger('select2:select');
							}
							return true;
						}",
						'select2:select' => "function(e) {
							createDynamicParamsFormUI($(this).val(), \"{$loadingText}\", '{$getGatewayParamsSchemaUrl}', 'gatewaymodel', 'gtwpluginparameters', 'GatewayModel', 'gtwPluginParameters', {$strGtwPluginParameters}, 'params-container', 3);

							createDynamicParamsFormUI($(this).val(), \"{$loadingText}\", '{$getGatewayRestrictionsSchemaUrl}', 'gatewaymodel', 'gtwrestrictions', 'GatewayModel', 'gtwRestrictions', {$strGtwRestrictions}, 'restrictions-container', 3);

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
