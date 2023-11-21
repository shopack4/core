<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use yii\web\JsExpression;
use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\ArrayHelper;
use shopack\base\frontend\common\widgets\Select2;
use shopack\base\frontend\common\widgets\DepDrop;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\frontend\common\widgets\ActiveForm;
use shopack\base\frontend\common\widgets\FormBuilder;
// use shopack\aaa\common\enums\enuUserAccessGroupStatus;
use shopack\aaa\frontend\common\models\UserModel;
use shopack\aaa\frontend\common\models\AccessGroupModel;
use shopack\base\frontend\common\widgets\datetime\DatePicker;

// \shopack\base\frontend\common\DynamicParamsFormAsset::register($this);
?>

<div class='user-access-group-form'>
	<?php
		$form = ActiveForm::begin([
			'model' => $model,
			'formConfig' => [
				'labelSpan' => 4,
			],
		]);

		$builder = $form->getBuilder();

		// $builder->fields([
			// [
			// 	'usragpStatus',
			// 	'type' => FormBuilder::FIELD_RADIOLIST,
			// 	'data' => enuUserAccessGroupStatus::listData('form'),
			// 	'widgetOptions' => [
			// 		'inline' => true,
			// 	],
			// ],
		// ]);

		//from member view or side bar?
		if (empty($model->usragpUserID)) {
			$formatJs =<<<JS
var formatUser = function(user) {
	if (user.loading)
		return 'در حال جستجو...'; //item.text;
	return '<div style="overflow:hidden;">' + '<b>' + user.firstname + ' ' + user.lastname + '</b> - ' + user.email + '</div>';
};
var formatUserSelection = function(user) {
	if (user.text)
		return user.text;
	return user.firstname + ' ' + user.lastname + ' - ' + user.email;
}
JS;
			$this->registerJs($formatJs, \yii\web\View::POS_HEAD);

			// script to parse the results into the format expected by Select2
			$resultsJs =<<<JS
function(data, params) {
	if ((data == null) || (params == null))
		return;

	// params.page = params.page || 1;
	if (params.page == null)
		params.page = 0;

	return {
		results: data.items,
		pagination: {
			more: ((params.page + 1) * 20) < data.total_count
		}
	};
}
JS;

			if (empty($model->usragpUserID))
				$initValueText = null;
			else {
				$userModel = UserModel::findOne($model->usragpUserID);
				$initValueText = $userModel->usrFirstName . ' ' . $userModel->usrLastName . ' - ' . $userModel->usrEmail;
			}

			$builder->fields([
				[
					'usragpUserID',
					'type' => FormBuilder::FIELD_WIDGET,
					'widget' => Select2::class,
					'widgetOptions' => [
						'initValueText' => $initValueText,
						'value' => $model->usragpUserID,
						'pluginOptions' => [
							'allowClear' => false,
							'minimumInputLength' => 2, //qom, rey
							'ajax' => [
								'url' => Yii::$app->getModule('aaa')->searchUserForSelect2ListUrl(),
								'dataType' => 'json',
								'delay' => 50,
								'data' => new JsExpression('function(params) { return {q:params.term, page:params.page}; }'),
								'processResults' => new JsExpression($resultsJs),
								'cache' => true,
							],
							'escapeMarkup' => new JsExpression('function(markup) { return markup; }'),
							'templateResult' => new JsExpression('formatUser'),
							'templateSelection' => new JsExpression('formatUserSelection'),
						],
						'options' => [
							'placeholder' => Yii::t('app', '-- Search (*** for all) --'),
							'dir' => 'rtl',
							// 'multiple' => true,
						],
					],
				],
			]);

		} else {
			$builder->fields([
				[
					'usragpUserID',
					'type' => FormBuilder::FIELD_STATIC,
					'staticValue' => $model->user->displayName(),
				],
			]);
		}

		$builder->fields([
			['usragpAccessGroupID',
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => Select2::class,
				'widgetOptions' => [
					'data' => ArrayHelper::map(AccessGroupModel::find()->asArray()->noLimit()->all(), 'agpID', 'agpName'),
					'options' => [
						'placeholder' => Yii::t('app', '-- Choose --'),
						'dir' => 'rtl',
					],
				],
			],
			['usragpStartAt',
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => DatePicker::class,
				'fieldOptions' => [
					'addon' => [
						'append' => [
							'content' => '<i class="far fa-calendar-alt"></i>',
						],
					],
				],
				'widgetOptions' => [
					'withTime' => true,
				],
			],
			['usragpEndAt',
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => DatePicker::class,
				'fieldOptions' => [
					'addon' => [
						'append' => [
							'content' => '<i class="far fa-calendar-alt"></i>',
						],
					],
				],
				'widgetOptions' => [
					'withTime' => true,
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
