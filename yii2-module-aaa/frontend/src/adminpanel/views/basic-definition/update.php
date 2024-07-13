<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\frontend\common\helpers\Html;

$this->title = Yii::t('aaa', 'Update Basic Definition');
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = ['label' => Yii::t('aaa', 'Basic Definitions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->bdfName, 'url' => ['view', 'id' => $model->bdfID]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div id='basic-definition-update' class='d-flex justify-content-center'>
	<div class='w-sm-75 card border-primary'>

		<div class='card-header bg-primary text-white'>
			<div class='card-title'><?= Html::encode($this->title) ?></div>
		</div>

		<?= $this->render('_form', [
			'model' => $model,
		]) ?>
	</div>
</div>
