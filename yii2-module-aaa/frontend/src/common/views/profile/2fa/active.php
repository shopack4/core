<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\frontend\common\helpers\Html;

$this->title = Yii::t('aaa', 'Active Two Factor Authentication');
$this->params['breadcrumbs'][] = $this->title;
?>

<div id='2fa-active' class='d-flex justify-content-center'>
	<div class='w-sm-75 card border-primary'>

		<div class='card-header bg-primary text-white'>
			<div class='card-title'><?= Html::encode($this->title) ?></div>
		</div>

		<?= $this->render('_form_' . $type, $params) ?>
	</div>
</div>
