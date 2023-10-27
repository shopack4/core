<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\frontend\common\helpers\Html;

$this->title = Yii::t('mha', 'Send Message To User');
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = ['label' => Yii::t('aaa', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('aaa', 'User') . ' ' . $model->usrID, 'url' => ['view', 'id' => $model->usrID]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div id='user-sendmessage-create' class='d-flex justify-content-center'>
	<div class='w-sm-75 card border-primary'>

		<div class='card-header bg-primary text-white'>
			<div class='card-title'><?= Html::encode($this->title) ?></div>
		</div>

		<?= $this->render('_form_sendMessage', [
			'model' => $model,
		]) ?>
	</div>
</div>
