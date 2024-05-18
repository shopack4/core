<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\common\helpers\Html;
use shopack\aaa\frontend\common\models\VoucherModel;

$this->title = Yii::t('aaa', 'Orders');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="order-index w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
			<?= VoucherModel::canCreate() ? Html::createButton(null, [
					'create',
					'vchOwnerUserID' => $vchOwnerUserID ?? $_GET['vchOwnerUserID'] ?? null,
				]) : '' ?>
			</div>
      <div class='card-title'><?= Html::encode($this->title) ?></div>
			<div class="clearfix"></div>
		</div>

    <div class='card-body'>
      <?php
				echo Yii::$app->controller->renderPartial('_index.php', [
					'searchModel' => $searchModel,
					'dataProvider' => $dataProvider,
				]);
			?>
    </div>
  </div>
</div>
