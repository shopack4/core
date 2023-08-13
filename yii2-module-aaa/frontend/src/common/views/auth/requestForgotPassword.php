<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

 use shopack\base\frontend\widgets\ActiveForm;
 use shopack\base\frontend\helpers\Html;

$this->title = Yii::t('aaa', 'Request Forgot Password Code');
$this->params['breadcrumbs'][] = $this->title;
?>

<div id='request-forgot-password' class='w-100'>
	<h1 class="mb-4"><?= Html::encode($this->title) ?></h1>

	<?php $form = ActiveForm::begin([
		'id' => 'request-forgot-password-form',
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
		<?= $form->field($model, 'input')->textInput([
			'autofocus' => true,
			'class' => ['form-control', 'latin-text', 'dir-ltr'],
		]) ?>

		<div class="form-group">
			<div class='row'>
				<div class="col text-end">
					<?= Html::submitButton(Yii::t('aaa', 'Request'), ['class' => 'btn btn-primary btn-sm', 'name' => 'request-button']); ?>
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
				<?= Html::a(Yii::t('aaa', 'Signup'), 'signup', ['class' => 'btn btn-outline-primary btn-sm', 'name' => 'login-button']) ?>
				<?= Html::a(Yii::t('aaa', 'Login By Password'), 'login', ['class' => 'btn btn-outline-primary btn-sm', 'name' => 'login-button']) ?>
				<?= Html::a(Yii::t('aaa', 'Login By Mobile'), 'login-by-mobile', ['class' => 'btn btn-outline-primary btn-sm', 'name' => 'login-button']) ?>
			</div>
		</div>

	<?php ActiveForm::end(); ?>
</div>
