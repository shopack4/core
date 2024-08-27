<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use yii\web\JsExpression;
use shopack\base\common\helpers\ArrayHelper;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\Select2;
use shopack\base\frontend\common\widgets\ActiveForm;
use shopack\base\frontend\common\widgets\FormBuilder;
use shopack\base\frontend\common\widgets\datetime\DatePicker;
use shopack\aaa\common\enums\enuBasicDefinitionType;
use shopack\aaa\common\enums\enuOfflinePaymentType;
use shopack\aaa\frontend\common\models\UserModel;
use shopack\aaa\frontend\common\models\BasicDefinitionModel;
?>

<div class='offline-payment-form'>
	<?php
		$form = ActiveForm::begin([
			'model' => $model,
			'fieldConfig' => [
				'labelSpan' => 2,
			],
		]);

		$builder = $form->getBuilder();

		//from member view or side bar?
		if (empty($model->ofpOwnerUserID)) {
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

			if (empty($model->ofpOwnerUserID))
				$initValueText = null;
			else {
				$userModel = UserModel::findOne($model->ofpOwnerUserID);
				$initValueText = $userModel->usrFirstName . ' ' . $userModel->usrLastName . ' - ' . $userModel->usrEmail;
			}

			$builder->fields([
				[
					'ofpOwnerUserID',
					'type' => FormBuilder::FIELD_WIDGET,
					'widget' => Select2::class,
					'widgetOptions' => [
						'initValueText' => $initValueText,
						'value' => $model->ofpOwnerUserID,
						'pluginOptions' => [
							'allowClear' => false,
							'minimumInputLength' => 2, //qom, rey
							'ajax' => [
								'url' => Yii::$app->getModule('aaa')->getSearchUserForSelect2ListUrl(),
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
					'ofpOwnerUserID',
					'type' => FormBuilder::FIELD_STATIC,
					'staticValue' => $model->owner->displayName(),
				],
			]);
		}

		$builder->fields([
			// 'ofpVoucherID',
			[
				'ofpType',
				'type' => FormBuilder::FIELD_RADIOLIST,
				// 'widget' => Select2::class,
				'data' => enuOfflinePaymentType::listData(),
				'widgetOptions' => [
					'inline' => true,
					// 'options' => [
					// 	'placeholder' => Yii::t('app', '-- Choose --'),
					// 	'dir' => 'rtl',
					// ],
				]
			],

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
			['ofpPayer'],
			['ofpSourceCartNumber'],
			// ['ofpWalletID'],
			['@col-break'],

			[
				'ofpDestCartNumber',
				'visibleConditions' => [
					'ofpType' => [
						enuOfflinePaymentType::ToCart,
					],
				],
			],
			[
				'ofpDestAccountNumber',
				'visibleConditions' => [
					'ofpType' => [
						enuOfflinePaymentType::ToAccountNumber,
					],
				],
			],
			[
				'ofpDestISBN',
				'visibleConditions' => [
					'ofpType' => [
						enuOfflinePaymentType::ToISBN,
					],
				],
			],
			[
				'ofpDestBankID',
				'visibleConditions' => [
					'ofpType' => [
						enuOfflinePaymentType::Pos,
						enuOfflinePaymentType::ToCart,
						enuOfflinePaymentType::ToAccountNumber,
						enuOfflinePaymentType::ToISBN,
					],
				],
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => Select2::class,
				'widgetOptions' => [
					'data' => ArrayHelper::map(BasicDefinitionModel::find()
						->where(['bdfType' => enuBasicDefinitionType::Bank])
						->noLimit()
						->asArray()
						->all(),
						'bdfID',
						'bdfName'
					),
					'options' => [
						'placeholder' => Yii::t('app', '-- Choose --'),
						'dir' => 'rtl',
					],
				]
			],

			[
				'ofpDueDate',
				'visibleConditions' => [
					'ofpType' => [
						enuOfflinePaymentType::Cheque,
					],
				],
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => DatePicker::class,
				'fieldOptions' => [
					'addon' => [
						'append' => [
							'content' => '<i class="far fa-calendar-alt"></i>',
						],
					],
				],
			],
			[
				'ofpDestName',
				'visibleConditions' => [
					'ofpType' => [
						enuOfflinePaymentType::Cash,
						enuOfflinePaymentType::ToAccountNumber,
						enuOfflinePaymentType::ToISBN,
						enuOfflinePaymentType::Cheque,
					],
				],
			],
			[
				'ofpTrackNumber',
				'visibleConditions' => [
					'ofpType' => [
						enuOfflinePaymentType::Pos,
						enuOfflinePaymentType::ToCart,
						enuOfflinePaymentType::ToAccountNumber,
						enuOfflinePaymentType::ToISBN,
					],
				],
			],
			[
				'ofpReferenceNumber',
				'visibleConditions' => [
					'ofpType' => [
						enuOfflinePaymentType::Pos,
						enuOfflinePaymentType::ToCart,
						enuOfflinePaymentType::ToAccountNumber,
						enuOfflinePaymentType::ToISBN,
					],
				],
			],

			['@reset-cols'],
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
