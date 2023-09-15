<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\common\helpers\Html;
use shopack\aaa\frontend\common\models\OfflinePaymentModel;

$this->title = Yii::t('aaa', 'Offline Payments');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="offline-payment-index w-100">
  <div class='card border-default'>
		<div class='card-header bg-default'>
			<div class="float-end">
        <?php echo OfflinePaymentModel::canCreate() ? Html::createButton(null, [
          'justForMe' => $justForMe ?? $_GET['justForMe'] ?? null
        ]) : ''; ?>
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
