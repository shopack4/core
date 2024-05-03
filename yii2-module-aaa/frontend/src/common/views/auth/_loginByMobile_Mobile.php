<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\frontend\common\helpers\Html;
?>

<?php
	echo $form->field($model, 'mobile')->textInput([
		'autofocus' => true,
		'class' => ['form-control', 'latin-text', 'dir-ltr'],
	]);
?>

<div class='row'>
	<div class="col">
		<?= $form->field($model, 'rememberMe')->checkbox([], true) ?>
	</div>
	<div class="col text-end">
		<?php
			echo Html::submitButton(Yii::t('aaa', 'Login'), ['class' => 'btn btn-primary btn-sm', 'name' => 'login-button']);
		?>
	</div>
</div>

<div class='row'>
	<div class="col">
		<?php
			if (empty($message) == false)
				echo $message;

			if ($showCreateNewUser) {
				$js =<<<JS
function submitWithSignup(event) {
	var form = $('form#login-form'); //.clone(true);
	$('<input>').attr({
		type : 'hidden',
		id   : 'loginbymobileform-signupIfNotExists',
		name : 'LoginByMobileForm[signupIfNotExists]',
		value: 1
	}).appendTo(form);
	form.submit();
}
JS;
				$this->registerJs($js, \yii\web\View::POS_END);

				echo '<br>آیا می‌خواهید کاربر جدیدی با این شماره موبایل ایجاد شود؟';
				echo ' ';
				echo Html::button('بلی', [
					'class' => 'btn btn-primary btn-sm',
					'id' => 'btn-submit-with-signup',
					'name' => 'btn-submit-with-signup',
					'onclick' => 'submitWithSignup();',
				]);
				echo ' ';
				echo Html::a('خیر', ['login-by-mobile'], [
					'class' => 'btn btn-success btn-sm',
				]);
			}
		?>
	</div>
</div>
