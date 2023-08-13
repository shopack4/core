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
// use shopack\aaa\common\enums\enuGeoCityOrVillageStatus;
use yii\web\JsExpression;

// \shopack\base\frontend\DynamicParamsFormAsset::register($this);
?>

<div class='geo-city-or-village-form'>
	<?php
		$form = ActiveForm::begin([
			'model' => $model,
		]);

		$builder = $form->getBuilder();

		$builder->fields([
			// [
			// 	'ctvStatus',
			// 	'type' => FormBuilder::FIELD_RADIOLIST,
			// 	'data' => enuGeoCityOrVillageStatus::listData('form'),
			// 	'widgetOptions' => [
			// 		'inline' => true,
			// 	],
			// ],
			[
				'ctvStateID',
				'type' => FormBuilder::FIELD_STATIC,
				'staticValue' => $model->state->sttName,
			],
			['ctvName'],
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
