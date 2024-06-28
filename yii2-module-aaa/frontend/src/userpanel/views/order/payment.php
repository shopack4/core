<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\frontend\common\helpers\Html;

$this->title = Yii::t('aaa', 'Change Payment Method');
$this->params['breadcrumbs'][] = ['label' =>Yii::t('aaa', 'Order') . ': ' . $model->vchID, 'url' => ['view', 'id' => $model->vchID]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('aaa', 'Orders'), 'url' => ['/aaa/fin', 'fragment' => 'orders']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div id='payment' class='d-flex justify-content-center'>
	<div class='w-sm-75 card border-primary'>

		<div class='card-header bg-primary text-white'>
			<div class='card-title'><?= Html::encode($this->title) ?></div>
		</div>

		<?= $this->render('_payment_form', [
			'model' => $model,
		]) ?>
	</div>
</div>
