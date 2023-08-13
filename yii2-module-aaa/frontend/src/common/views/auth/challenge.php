<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

use shopack\base\frontend\widgets\ActiveForm;
use shopack\base\frontend\helpers\Html;
// use yii\bootstrap5\Html;
use shopack\base\common\helpers\Url;
use yii\web\View;

$this->title = Yii::t('aaa', 'Challenge');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-login w-100">
	<h1 class="mb-4">ورود کد عبور یکبار مصرف</h1>

	<?php
		$challengeUrl = Url::to(['challenge']);
	// 	$currentUrl = Url::to();
	// 	if ($currentUrl != $challengeUrl) {
	// 		$js =<<<JS
	// function shallowGoTo(page, title, url) {
	//   if ("undefined" !== typeof history.pushState) {
	//     history.replaceState({page: page}, title, url);
	//   } else {
	//     window.location.assign(url);
	//   }
	// }
	// shallowGoTo("challenge", "{$this->title}", '{$challengeUrl}');
	// JS;
	// 		$this->registerJs($js, View::POS_READY);
	// 	}

		$form = ActiveForm::begin([
			'id' => 'challenge-form',
			'action' => $challengeUrl,
			// 'layout' => 'horizontal',
			'fieldConfig' => [
				'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
				// 'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{endWrapper}",
				// 'template' => "{label}\n{input}\n{error}",
				// 'labelOptions' => ['class' => 'col-lg-12 col-form-label mr-lg-3'],
				// 'inputOptions' => ['class' => 'col-lg-12 form-control'],
				// 'errorOptions' => ['class' => 'col-lg-12 invalid-feedback'],
				'horizontalCssClasses' => [
					'label' => 'col-md-12 mb-1',
					'offset' => '',
					'wrapper' => 'col-md-12',
					'error' => '',
					'hint' => '',
				],
			],
			// 'options' => [
			// 	'data-turbo' => 'true',
			// ],
		]);

		echo Html::hiddenInput(Html::getInputName($model, 'realm'), $model->realm);
		echo Html::hiddenInput(Html::getInputName($model, 'type'), $model->type);
		echo Html::hiddenInput(Html::getInputName($model, 'key'), $model->key);
		echo Html::hiddenInput(Html::getInputName($model, 'rememberMe'), $model->rememberMe);
		echo Html::hiddenInput('resend', 0, [
			'id' => 'resend'
		]);

		// echo Html::tag('div', $model->realm);
		// echo Html::tag('div', $model->type);
		echo Html::tag('div', 'شماره موبایل: ' . $model->key);

		echo $form->field($model, 'value')->textInput([
			'autofocus' => true,
			'class' => ['form-control', 'text-center', 'latin-text', 'dir-ltr'],
		]);
	?>

	<div class="form-group">
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
		<div>
		<?php
			if (empty($message) == false)
				echo $message;
		?>
		</div>
		<hr>
		<div class="col">
			<?= Html::a(Yii::t('aaa', 'Signup'), 'signup', ['class' => 'btn btn-outline-primary btn-sm', 'name' => 'login-button']) ?>
			<?= Html::a(Yii::t('aaa', 'Login By Password'), 'login', ['class' => 'btn btn-outline-primary btn-sm', 'name' => 'login-button']) ?>
		</div>
	</div>

	<?php ActiveForm::end(); ?>
</div>
