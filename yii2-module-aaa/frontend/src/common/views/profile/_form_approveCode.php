<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\frontend\common\widgets\ActiveForm;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\common\helpers\Url;
use shopack\base\frontend\common\widgets\FormBuilder;
use shopack\base\common\helpers\GeneralHelper;
?>

<div class='mobile-approve-form'>
	<?php
		$model        = $params['model'];
		$timerInfo    = $params['timerInfo'] ?? null;
		$resultStatus = $params['resultStatus'] ?? null;
		$resultData   = $params['resultData'] ?? null;
		$message      = $params['message'] ?? null;

		$form = ActiveForm::begin([
			'action' => Url::to(['/aaa/profile/approve-code',
				'kt' => $model->keyType,
				'input' => $model->input,
			]),
			'model' => $model,
			// 'donewait' => 10,
			// 'modalDoneInternalScript_OK' => "setTimeout(function() { $('#mobile-approve-link').click(); }, 500);",
		]);

		$builder = $form->getBuilder();

		echo Html::hiddenInput('resend', 0, ['id' => 'resend']);

		$keyType = $model->keyType;

		$builder->fields([
			[
				'input',
				'label' => ($keyType == GeneralHelper::PHRASETYPE_EMAIL
					? 'ایمیل'
					: ($keyType == GeneralHelper::PHRASETYPE_MOBILE
						? 'موبایل'
						: 'ایمیل / موبایل')),
				'type' => FormBuilder::FIELD_STATIC,
				'staticValue' => Html::div($model->input, ['class' => ['dir-ltr', 'text-start']]),
			],
			[
				'code',
				'widgetOptions' => [
					'class' => ['text-center', 'latin-text', 'dir-ltr'],
				],
			],
		]);
	?>

	<?php $builder->beginFooter(); ?>
		<div class="card-footer">
			<div class="float-end">
				<?php
					if (empty($timerInfo) == false) {
						echo Html::activeSubmitButton($model, Yii::t('aaa', 'Verify'), ['class' => ['btn', 'btn-primary', 'btn-sm']]) . ' ';
					}

					//---------------------
					$waitMessage = Yii::t('aaa', 'Resend Code {0}');
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
			<div>
				<?php
					if (empty($message) == false)
						echo $message;
				?>
				<?= Html::formErrorSummary($model); ?>
			</div>
			<div class="clearfix"></div>
		</div>
	<?php $builder->endFooter(); ?>

	<?php
		$builder->render();
		$form->endForm(); //ActiveForm::end();

// var_dump([
// 	'timerInfo'    => $timerInfo,
// 	'resultStatus' => $resultStatus,
// 	'resultData'   => $resultData,
// 	'message'      => $message,
// ]);

	?>
</div>
