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
// use shopack\aaa\common\enums\enuAccessGroupStatus;
use yii\web\JsExpression;

// \shopack\base\frontend\common\DynamicParamsFormAsset::register($this);
?>

<div class='access-group-form'>
	<?php
		$form = ActiveForm::begin([
			'model' => $model,
		]);

		$builder = $form->getBuilder();

		if ((empty($model->agpPrivs) == false) && is_array($model->agpPrivs))
			$model->agpPrivs = Json::encode($model->agpPrivs);

		$builder->fields([
			// [
			// 	'agpStatus',
			// 	'type' => FormBuilder::FIELD_RADIOLIST,
			// 	'data' => enuAccessGroupStatus::listData('form'),
			// 	'widgetOptions' => [
			// 		'inline' => true,
			// 	],
			// ],
			['agpName'],
			['agpPrivs',
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
