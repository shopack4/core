<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

 use shopack\base\common\helpers\ArrayHelper;
 use shopack\base\frontend\common\helpers\Html;
 use shopack\base\frontend\common\widgets\Select2;
 use shopack\base\frontend\common\widgets\ActiveForm;
 use shopack\base\frontend\common\widgets\FormBuilder;
 use shopack\base\frontend\common\widgets\datetime\DatePicker;
 use shopack\aaa\common\enums\enuBasicDefinitionType;
 use shopack\aaa\common\enums\enuOfflinePaymentType;
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
