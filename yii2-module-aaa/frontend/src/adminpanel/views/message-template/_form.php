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
use shopack\aaa\common\enums\enuMessageTemplateStatus;
use shopack\aaa\common\enums\enuMessageTemplateMedia;
?>

<div class='message-template-form'>
	<?php
		$form = ActiveForm::begin([
			'model' => $model,
		]);

		$builder = $form->getBuilder();

		$builder->fields([
			['mstStatus',
				'type' => FormBuilder::FIELD_RADIOLIST,
				'data' => enuMessageTemplateStatus::listData('form'),
				'widgetOptions' => [
					'inline' => true,
				],
			],
		]);

		if (($model->isNewRecord == false) && ($model->mstIsSystem)) {
			//static
			$builder->fields([
				['mstKey',
					'type' => FormBuilder::FIELD_STATIC,
					'staticValue' => $model->mstKey,
				],
				['@col' => 2],
				['mstMedia',
					'type' => FormBuilder::FIELD_STATIC,
					'staticValue' => enuMessageTemplateMedia::getLabel($model->mstMedia),
				],
				['mstLanguage',
					'type' => FormBuilder::FIELD_STATIC,
					'staticValue' => $model->mstLanguage,
				],
			]);
		} else {
			$builder->fields([
				['mstKey'],
				['@col' => 2],
				['mstMedia',
					'type' => FormBuilder::FIELD_RADIOLIST,
					'data' => enuMessageTemplateMedia::listData('form'),
					'widgetOptions' => [
						'inline' => true,
					],
				],
				['mstLanguage'],
			]);
		}

		$builder->fields([
			['mstParamsPrefix',
				'widgetOptions' => ['class' => ['dir-ltr']],
			],
			['mstParamsSuffix',
				'widgetOptions' => ['class' => ['dir-ltr']],
			],
			['@col' => 1],
			['mstTitle'],
			['mstBody',
				'type' => FormBuilder::FIELD_TEXTAREA,
				'widgetOptions' => [
					'rows' => 5,
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
