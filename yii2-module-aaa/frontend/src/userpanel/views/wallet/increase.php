<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\frontend\helpers\Html;

$this->title = Yii::t('aaa', 'Increase Wallet');
$this->params['breadcrumbs'][] = ['label' => Yii::t('aaa', 'Wallets'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div id='wallet-create' class='d-flex justify-content-center'>
	<div class='w-sm-75 card border-primary'>

		<div class='card-header bg-primary text-white'>
			<div class='card-title'><?= Html::encode($this->title) ?></div>
		</div>

		<?= $this->render('_increase', [
			'model' => $model,
		]) ?>
	</div>
</div>
