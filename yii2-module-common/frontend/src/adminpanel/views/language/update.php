<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\frontend\common\helpers\Html;

$this->title = Yii::t('cmn', 'Update Language');
$this->params['breadcrumbs'][] = Yii::t('cmn', 'Common');
$this->params['breadcrumbs'][] = ['label' => Yii::t('cmn', 'Languages'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->lngName, 'url' => ['view', 'id' => $model->lngID]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div id='language-update' class='d-flex justify-content-center'>
	<div class='w-sm-75 card border-primary'>

		<div class='card-header bg-primary text-white'>
			<div class='card-title'><?= Html::encode($this->title) ?></div>
		</div>

		<?= $this->render('_form', [
			'model' => $model,
		]) ?>
	</div>
</div>
