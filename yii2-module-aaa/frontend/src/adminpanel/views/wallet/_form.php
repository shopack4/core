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
use shopack\aaa\common\enums\enuWalletStatus;
use yii\web\JsExpression;

// \shopack\base\frontend\DynamicParamsFormAsset::register($this);
?>

<div class='wallet-form'>
	<?php
		$form = ActiveForm::begin([
			'model' => $model,
		]);

		$builder = $form->getBuilder();

		$builder->fields([
			[
				'walStatus',
				'type' => FormBuilder::FIELD_RADIOLIST,
				'data' => enuWalletStatus::listData('form'),
				'widgetOptions' => [
					'inline' => true,
				],
			],
			['walName'],
		]);

		$pluginCategories = [];
		$result = HttpHelper::callApi('aaa/wallet/plugin-list');
		if ($result[0] == 200) {
			foreach ($result[1] as $k => $v) {
				$pluginCategories = array_merge($pluginCategories, [
					$k => Yii::t('aaa', $k), //array_keys($v),
				]);
			}
		}

		$builder->fields([
			[
				'walPluginType',
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

		$getWalletParamsSchemaUrl = Url::to(['plugin-params-schema']) . '?key=';
		$strGtwPluginParameters = '{}';
		if (($model->walPluginName !== null) && ($model->walPluginParameters !== null))
			$strGtwPluginParameters = json_encode($model->walPluginParameters);

		$getWalletRestrictionsSchemaUrl = Url::to(['plugin-restrictions-schema']) . '?key=';
		$strGtwRestrictions = '{}';
		if (($model->walPluginName !== null) && ($model->walRestrictions !== null))
			$strGtwRestrictions = json_encode($model->walRestrictions);

		$builder->fields([
			'walPluginName',
			'type' => FormBuilder::FIELD_WIDGET,
			'widget' => DepDrop::class,
			'widgetOptions' => [
				'type' => DepDrop::TYPE_SELECT2,
				'options' => [
					'placeholder' => Yii::t('app', '-- Choose --'),
					'dir' => 'rtl',
				],
				'pluginOptions' => [
					'depends' => ['walletmodel-walplugintype'],
					'initialize' => true,
					'initDepends' => ['walletmodel-walplugintype'],
					'url' => Url::to(['plugin-list', 'sel' => $model->walPluginName]),
					'loadingText' => Yii::t('app', 'Loading...'),
				],
				'select2Options' => [
					// 'hideSearch' => true,
					'pluginEvents' => [
						'depdrop:afterChange' => "function(e) {
							var sel = $('#walletmodel-walpluginname').val();
							if ((sel == null) || (sel == '')) {
								$('#params-container').empty();
								$('#restrictions-container').empty();
							} else {
								$('#walletmodel-walpluginname').trigger('select2:select');
							}
							return true;
						}",
						'select2:select' => "function(e) {
							createDynamicParamsFormUI($(this).val(), \"{$loadingText}\", '{$getWalletParamsSchemaUrl}', 'walletmodel', 'walpluginparameters', 'WalletModel', 'walPluginParameters', {$strGtwPluginParameters}, 'params-container', 3);

							createDynamicParamsFormUI($(this).val(), \"{$loadingText}\", '{$getWalletRestrictionsSchemaUrl}', 'walletmodel', 'walrestrictions', 'WalletModel', 'walRestrictions', {$strGtwRestrictions}, 'restrictions-container', 3);

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
