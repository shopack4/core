<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\helpers\Url;
use shopack\base\frontend\common\widgets\Select2;
use shopack\base\frontend\common\widgets\DepDrop;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\frontend\common\widgets\ActiveForm;
use shopack\base\frontend\common\widgets\FormBuilder;
use shopack\aaa\common\enums\enuDeliveryMethodType;
use shopack\aaa\common\enums\enuDeliveryMethodStatus;

// \shopack\base\frontend\common\DynamicParamsFormAsset::register($this);
?>

<div class='delivery-method-form'>
	<?php
		$form = ActiveForm::begin([
			'model' => $model,
		]);

		$builder = $form->getBuilder();

		$builder->fields([
			['dlvStatus',
				'type' => FormBuilder::FIELD_RADIOLIST,
				'data' => enuDeliveryMethodStatus::listData('form'),
				'widgetOptions' => [
					'inline' => true,
				],
			],
			['dlvName'],
			['dlvType',
				'type' => FormBuilder::FIELD_RADIOLIST,
				'data' => enuDeliveryMethodType::listData('form'),
				'widgetOptions' => [
					'inline' => true,
				],
			],
			['dlvAmount',
				'fieldOptions' => [
					'addon' => [
						'append' => [
							'content' => 'تومان',
						],
					],
				],
				// 'visibleConditions' => [
				// 	'dlvType' => enuDeliveryMethodType::SendToCustomer,
				// ],
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
