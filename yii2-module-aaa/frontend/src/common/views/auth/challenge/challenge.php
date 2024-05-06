<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

use shopack\base\common\helpers\Url;
use shopack\base\frontend\common\widgets\ActiveForm;
use shopack\base\frontend\common\helpers\Html;

$this->title = Yii::t('aaa', 'Two Factor Authentication');
// $this->title = Yii::t('aaa', 'Challenge');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="challenge-form w-100">
	<h1 class="mb-4"><?= Yii::t('aaa', 'Enter Two Factor Authentication Code') ?></h1>

	<?php
		$model = $params['model'];

		$challengeData = $model->challengeData();

		$inputs = [
			'email'		=> 'ایمیل',
			'mobile'	=> 'شماره موبایل',
			'ssid'		=> 'کد ملی',
		];
		foreach ($inputs as $k => $v) {
			if (isset($challengeData[$k])) {
				list($input, $label) = [$challengeData[$k], $v];
				break;
			}
		}
		if (isset($label)) {
			echo Html::tag('div', $label . ': '
				. Html::tag('div', $input, ['class' => 'dir-ltr d-inline-block'])
			);
		}

		$challengeUrl = Url::to(['challenge',
			'donelink' => $_GET['donelink'] ?? null,
		]);
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

		$fieldConfig = [];
		if (str_starts_with($model->realm, 'login')) {
			$fieldConfig = [
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
			];
		}

		$form = ActiveForm::begin([
			'id' => 'challenge-form',
			'model' => $model,
			'action' => $challengeUrl,
			// 'layout' => 'horizontal',
			'fieldConfig' => $fieldConfig,
			// 'options' => [
			// 	'data-turbo' => 'true',
			// ],
		]);

		$builder = $form->getBuilder();

		$params['builder'] = $builder;
		$params['form'] = $form;

		$form->registerActiveHiddenInput($model, 'realm');
		$form->registerActiveHiddenInput($model, 'token');
		// echo Html::hiddenInput(Html::getInputName($model, 'type'), $model->type);
		// echo Html::hiddenInput(Html::getInputName($model, 'key'), $model->key);
		// echo Html::hiddenInput(Html::getInputName($model, 'rememberMe'), $model->rememberMe);

		// echo Html::tag('div', $model->realm);
		// echo Html::tag('div', $model->type);

    $type = $challengeData['type'];
		echo $this->render('_form_' . $type, $params);

		$builder->render();
		$form->endForm(); //ActiveForm::end();
	?>

	<div>
		<?php
			if (empty($params['message']) == false)
				echo $params['message'];
		?>
	</div>
</div>
