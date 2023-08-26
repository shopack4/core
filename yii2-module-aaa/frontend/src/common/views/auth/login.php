<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

use shopack\base\frontend\widgets\ActiveForm;
use shopack\base\frontend\helpers\Html;
// use yii\bootstrap5\Html;

$this->title = Yii::t('aaa', 'Login');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-login w-100">
	<h1 class="mb-4"><?= Html::encode($this->title) ?></h1>

	<?php $form = ActiveForm::begin([
		'id' => 'login-form',
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
	]); ?>
		<?= $form->field($model, 'username')->textInput([
			'autofocus' => true,
			'class' => ['form-control', 'latin-text', 'dir-ltr'],
		]) ?>

		<?= $form->field($model, 'password')->passwordInput([
			'class' => ['form-control', 'latin-text', 'dir-ltr'],
		]) ?>

		<div class="form-group">
			<div class='row'>
				<div class="col">
					<?= $form->field($model, 'rememberMe')->checkbox([], true) ?>
				</div>
				<div class="col text-end">
					<?= Html::a(Yii::t('aaa', 'Forgot Password'), [
						'request-forgot-password',
						'donelink' => $_GET['donelink'] ?? null,
					], ['class' => 'btn btn-outline-primary btn-sm', 'name' => 'request-forgot-password-button']) ?>
					<?= Html::submitButton(Yii::t('aaa', 'Login'), ['class' => 'btn btn-primary btn-sm', 'name' => 'login-button']); ?>
				</div>
			</div>
			<div>
				<?php
					if (empty($message) == false)
						echo $message;
				?>
			</div>
			<hr>
			<div class="col">
				<?php
					if (Yii::$app->controller->module->allowSignup) {
						echo Html::a(Yii::t('aaa', 'Signup'), [
							'signup',
							'donelink' => $_GET['donelink'] ?? null,
						], ['class' => 'btn btn-outline-primary btn-sm', 'name' => 'login-button']);
					}
				?>
				<?= Html::a(Yii::t('aaa', 'Login By Mobile'), [
					'login-by-mobile',
					'donelink' => $_GET['donelink'] ?? null,
				], ['class' => 'btn btn-outline-primary btn-sm', 'name' => 'login-button']) ?>
			</div>
		</div>

	<?php ActiveForm::end(); ?>
</div>

<?php
/*
$this->registerJs(<<<JS
if ('serviceWorker' in navigator) {
	navigator.serviceWorker
		.register('/js/add-token-to-request.js')
		.then((registration) => {
				console.log('Service worker registered with scope: ', registration.scope);
		}, (err) => {
				console.log('Service worker registration failed: ', err);
		});
} else {
	console.error('Service workers are not supported.');
}
JS
	, \yii\web\View::POS_LOAD);

// index.html
window.addEventListener('load', function () {
    navigator
        .serviceWorker
        .register('/request-interceptor.js')
        .then(function (registration) {
            console.log('Service worker registered with scope: ', registration.scope);
        }, function (err) {
            console.log('ServiceWorker registration failed: ', err);
        });
});

// request-interceptor.js
self.addEventListener('fetch', function (event) {
    event.respondWith(async function () {
        let headers = new Headers()
        headers.append("X-Custom-Header", "Random value")
        return fetch(event.request, {headers: headers})
    }());
});
*/

?>
