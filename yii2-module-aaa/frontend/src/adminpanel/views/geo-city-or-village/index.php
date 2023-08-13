<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\widgets\grid\GridView;
use shopack\base\frontend\helpers\Html;
use shopack\base\common\helpers\StringHelper;
// use shopack\aaa\common\enums\enuGeoCityOrVillageStatus;
use shopack\aaa\frontend\common\models\GeoCityOrVillageModel;

$this->title = Yii::t('aaa', 'Cities Or Villages');
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="geo-city-or-village-index w-100">
  <div class='card border-default'>
		<div class='card-header bg-default'>
			<div class="float-end">
        <?= GeoCityOrVillageModel::canCreate() ? Html::createButton(null, [
          'ctvStateID' => $ctvStateID ?? $_GET['ctvStateID'] ?? null
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
					'ctvStateID' => $ctvStateID ?? $_GET['ctvStateID'] ?? null,
				]);
			?>
    </div>
  </div>
</div>
