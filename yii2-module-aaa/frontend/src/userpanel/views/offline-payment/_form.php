<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use yii\web\JsExpression;
use shopack\base\common\helpers\Url;
use shopack\base\frontend\common\widgets\Select2;
use shopack\base\frontend\common\widgets\DepDrop;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\frontend\common\widgets\ActiveForm;
use shopack\base\frontend\common\widgets\FormBuilder;
use shopack\aaa\frontend\common\models\UserModel;
use shopack\base\frontend\common\widgets\datetime\DatePicker;
?>

<div class='offline-payment-form'>
	<?php
		$form = ActiveForm::begin([
			'model' => $model,
		]);

		$builder = $form->getBuilder();

		$builder->fields([
			['@cols' => 2, 'vertical' => true],
			[
				'ofpAmount',
				'fieldOptions' => [
					'addon' => [
						'append' => [
							'content' => 'تومان',
						],
					],
				],
			],
			['ofpBankOrCart'],
			['ofpTrackNumber'],
			['ofpReferenceNumber'],
			[
				'ofpPayDate',
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => DatePicker::class,
				'fieldOptions' => [
					'addon' => [
						'append' => [
							'content' => '<i class="far fa-calendar-alt"></i>',
						],
					],
				],
				// 'widgetOptions' => [
				// 	'withTime' => true,
				// ],
			],
			['@col-break'],
			['ofpPayer'],
			['ofpSourceCartNumber'],
			// ['ofpWalletID'],
			[
				'ofpImageFileID',
				'type' => FormBuilder::FIELD_FILE,
				'widgetOptions' => [
					'accept' => 'image/png, image/gif, image/jpg, image/jpeg',
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
