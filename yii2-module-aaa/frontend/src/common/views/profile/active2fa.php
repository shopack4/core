<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\ActiveForm;

$this->title = Yii::t('aaa', 'Active Two Factor Authentication');
$this->params['breadcrumbs'][] = $this->title;
?>

<div id='2fa-active' class='d-flex justify-content-center'>
	<div class='w-sm-75 card border-primary'>

		<div class='card-header bg-primary text-white'>
			<div class='card-title'><?= Html::encode($this->title) ?></div>
		</div>

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

			$params['builder'] = $builder;
			$params['form'] = $form;

			$this->render('..' . DIRECTORY_SEPARATOR
				. '..' . DIRECTORY_SEPARATOR
				. 'challenge' . DIRECTORY_SEPARATOR
				. '_form_' . $type,
			$params);

			$builder->render();
			$form->endForm(); //ActiveForm::end();
		?>

	</div>
</div>
