<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\ActiveForm;
use shopack\base\frontend\common\widgets\FormBuilder;
use shopack\base\common\helpers\GeneralHelper;

$this->title = Yii::t('aaa', 'Reset Password');
$this->params['breadcrumbs'][] = $this->title;

$model        = $params['model'] ?? null;
$timerInfo    = $params['timerInfo'] ?? null;
$resultStatus = $params['resultStatus'] ?? null;
$resultData   = $params['resultData'] ?? null;
$message      = $params['message'] ?? null;
$keyType      = $params['keyType'] ?? null;
?>

<div id='passwordResetByForgotCode' class='d-flex justify-content-center'>
	<div class='w-sm-75 card border-primary'>

		<div class='card-header bg-primary text-white'>
			<div class='card-title'><?= Html::encode($this->title) ?></div>
		</div>

		<div class='password-reset-by-forgot-code-form'>
			<?php
				$form = ActiveForm::begin([
					'model' => $model,
				]);

				$builder = $form->getBuilder();

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
				]);

				if ($keyType == GeneralHelper::PHRASETYPE_MOBILE) {
					echo Html::hiddenInput('resend', 0, ['id' => 'resend']);

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
						$button = Html::button($resendMessage, $btnOptions);
					else {
						$btnOptions['disabled'] = true;
						$msg = strtr($waitMessage, [
							'{0}' => $timerInfo['remained'],
						]);
						$button = Html::button($msg, $btnOptions);
					}

					$builder->fields([
						[
							'code',
							'widgetOptions' => [
								'class' => ['text-center', 'latin-text', 'dir-ltr'],
							],
							'fieldOptions' => [
								'addon' => [
									'append' => [
										'content' => $button,
									],
								],
							],
						],
					]);
				}

				$builder->fields([
					['newPassword',
						'type' => FormBuilder::FIELD_PASSWORD,
						'widgetOptions' => [
							'style' => 'direction:ltr',
						],
					],
					['retypePassword',
						'type' => FormBuilder::FIELD_PASSWORD,
						'widgetOptions' => [
							'style' => 'direction:ltr',
						],
					],
				]);
			?>

			<?php $builder->beginFooter(); ?>
				<div class="card-footer">
					<div class="float-end">
						<?php
							echo Html::activeSubmitButton($model, 'تغییر');
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
			?>
		</div>

	</div>
</div>
