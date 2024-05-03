<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\frontend\common\helpers\Html;
?>

<?php
	echo Html::hiddenInput(Html::getInputName($model, 'mobile'), $model->mobile);
	echo Html::hiddenInput(Html::getInputName($model, 'rememberMe'), $model->rememberMe);
	echo Html::hiddenInput('resend', 0, [
		'id' => 'resend'
	]);
?>

<div class='row'>
	<div class='col'>
		<?php
			echo Html::tag('div', 'شماره موبایل: ' . $model->mobile);
		?>
	</div>
</div>

<div class='row'>
	<div class='col'>
		<?php
			echo $form->field($model, 'code')->textInput([
				'autofocus' => true,
				'class' => ['form-control', 'latin-text', 'dir-ltr'],
			]);
		?>
	</div>
</div>

<div class='row'>
	<div class="col text-end">
		<?php
			if (empty($timerInfo) == false)
				echo Html::submitButton(Yii::t('aaa', 'Verify'), ['class' => 'btn btn-primary btn-sm', 'name' => 'verify-code']);

			echo " ";

			//---------------------
			$waitMessage = Yii::t('aaa', 'For resend code, please wait {0}');
			$resendMessage = Yii::t('aaa', 'Resend Code');
			$ttl = $timerInfo['ttl'] ?? 0;
			$js =<<<JS
function resendCode(event) {
	$('#resend').val('1');
	$('#{$form->id}').on('beforeValidateAttribute', function (event, attribute) {
		return false;
	});
	$('#{$form->id}').submit();
}

let remainedSecs = {$ttl};
let timerInterval = null;
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

			if (empty($timerInfo) == false)
				$this->registerJs('timerInterval = window.setInterval(countdownResendButton, 1000);', \yii\web\View::POS_READY);

			$btnOptions = [
				'class' => 'btn btn-outline-primary btn-sm',
				'id' => 'btn-resend-code',
				'name' => 'btn-resend-code',
				'onclick' => 'resendCode();',
			];

			if (empty($timerInfo))
				echo Html::button($resendMessage, $btnOptions);
			else {
				$btnOptions['disabled'] = true;
				$msg = strtr($waitMessage, [
					'{0}' => $timerInfo['remained'],
				]);
				echo Html::button($msg, $btnOptions);
			}
		?>
	</div>
</div>

<div class='row'>
	<div class='col'>
		<?php
			if (empty($message) == false)
				echo $message;
		?>
	</div>
</div>
