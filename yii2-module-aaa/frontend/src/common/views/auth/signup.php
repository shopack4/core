<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

use shopack\base\frontend\widgets\ActiveForm;
use shopack\base\frontend\helpers\Html;
// use yii\bootstrap5\Html;

$this->title = Yii::t('aaa', 'Signup');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-signup w-100">
	<h1 class="mb-4"><?= Html::encode($this->title) ?></h1>

	<?php $form = ActiveForm::begin([
		'id' => 'signup-form',
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
		<?= $form->field($model, 'email')->textInput([
			'autofocus' => true,
			'class' => ['form-control', 'latin-text', 'dir-ltr'],
		]) ?>

		<?= $form->field($model, 'mobile')->textInput([
			'autofocus' => true,
			'class' => ['form-control', 'latin-text', 'dir-ltr'],
		]); ?>

		<?= $form->field($model, 'password')->passwordInput([
			'class' => ['form-control', 'latin-text', 'dir-ltr'],
		]) ?>

		<?= $form->field($model, 'retypePassword')->passwordInput([
			'class' => ['form-control', 'latin-text', 'dir-ltr'],
		]) ?>

		<div class="form-group">
			<div class='row'>
				<div class="col">
					<?= $form->field($model, 'rememberMe')->checkbox([], true) ?>
				</div>
				<div class="col text-end">
					<?php
						echo Html::submitButton(Yii::t('aaa', 'Signup'), ['class' => 'btn btn-primary btn-sm', 'name' => 'signup-button']);
					?>
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
				<?= Html::a(Yii::t('aaa', 'Login By Password'), 'login', ['class' => 'btn btn-outline-primary btn-sm', 'name' => 'login-button']) ?>
				<?= Html::a(Yii::t('aaa', 'Login By Mobile'), 'login-by-mobile', ['class' => 'btn btn-outline-primary btn-sm', 'name' => 'login-button']) ?>
			</div>
		</div>

	<?php ActiveForm::end(); ?>
</div>
