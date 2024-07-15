<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\helpers\Json;
use shopack\base\common\helpers\Url;
use shopack\base\frontend\common\widgets\Select2;
use shopack\base\frontend\common\widgets\DepDrop;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\frontend\common\widgets\ActiveForm;
use shopack\base\frontend\common\widgets\FormBuilder;
// use shopack\cmn\common\enums\enuLanguageStatus;
use yii\web\JsExpression;

// \shopack\base\frontend\common\DynamicParamsFormAsset::register($this);
?>

<div class='language-form'>
	<?php
		$form = ActiveForm::begin([
			'model' => $model,
		]);

		$builder = $form->getBuilder();

		if ((empty($model->lngPrivs) == false) && is_array($model->lngPrivs))
			$model->lngPrivs = Json::encode($model->lngPrivs);

		$builder->fields([
			// [
			// 	'lngStatus',
			// 	'type' => FormBuilder::FIELD_RADIOLIST,
			// 	'data' => enuLanguageStatus::listData('form'),
			// 	'widgetOptions' => [
			// 		'inline' => true,
			// 	],
			// ],
			['lngName'],
			['lngPrivs',
				'type' => FormBuilder::FIELD_TEXTAREA,
				'widgetOptions' => [
					'rows' => 4,
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
