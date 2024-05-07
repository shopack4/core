<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use borales\extensions\phoneInput\PhoneInput;
use shopack\base\frontend\common\widgets\ActiveForm;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\FormBuilder;
?>

<div class='2fa-smsOtp-form'>
	<?php
		$form = ActiveForm::begin([
			'model' => $model,
			'fieldConfig' => [
				'labelSpan' => 3,
			],
			// 'donewait' => 10,
			// 'modalDoneInternalScript_OK' => "setTimeout(function() { $('#mobile-approve-link').click(); }, 500);",
		]);

		$builder = $form->getBuilder();

		$builder->fields([
			[
				'code',
				'widgetOptions' => [
					'options' => [
						'style' => 'direction:ltr',
					],
				],
			]
		]);

		// $generateResult
		$waitMessage = Yii::t('aaa', 'Resend Code {0}');
		$resendMessage = Yii::t('aaa', 'Resend Code');
		$ttl = $generateResult['ttl'] ?? 0;
		$js =<<<JS
function resendCode(event) {
	$('#resend').val('1');
	$('#{$form->id}').on('beforeValidateAttribute', function (event, attribute) {
		return false;
	});
	$('#{$form->id}').submit();
}

remainedSecs = {$ttl};
timerInterval = null;
function countdownResendButton(event) {
	if (remainedSecs > 0) {
		m = Math.floor(remainedSecs / 60);
		s = remainedSecs % 60;
		msg = "{$waitMessage}".replace('{0}', m + ":" + s);
		$("#btn-resend-code").html(msg);
		--remainedSecs;
	} else {
		remainedSecs = 0;
		$("#btn-resend-code").prop('disabled', false);
		$("#btn-resend-code").html('{$resendMessage}');
		window.clearInterval(timerInterval);
	}
}
JS;
		$this->registerJs($js, \yii\web\View::POS_END);

		if (empty($generateResult) == false)
			$this->registerJs('timerInterval = window.setInterval(countdownResendButton, 1000);', \yii\web\View::POS_READY);

		$btnOptions = [
			'class' => 'btn btn-outline-primary btn-sm',
			'id' => 'btn-resend-code',
			'name' => 'btn-resend-code',
			'onclick' => 'resendCode();',
		];

		if (empty($generateResult)) {
			$msg = $resendMessage;
		} else {
			$btnOptions['disabled'] = true;
			$msg = strtr($waitMessage, [
				'{0}' => $generateResult['remained'],
			]);
		}
		$builder->fields([
			Html::hiddenInput('resend', 0, ['id' => 'resend']),
			Html::div(Html::button($resendMessage, $btnOptions), [
				'class' => ['float-end']
			])
		]);
	?>

	<?php $builder->beginFooter(); ?>
		<div class="card-footer">
			<div class="float-end">
				<?= Html::activeSubmitButton($model, Yii::t('aaa', 'Active')) ?>
			</div>
			<div>
				<?php
					if (empty($messageText) == false)
						echo $messageText;
				?>
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
