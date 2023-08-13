<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\widgets\grid\GridView;
use shopack\base\frontend\helpers\Html;
use shopack\base\common\helpers\StringHelper;
// use shopack\aaa\common\enums\enuGeoTownStatus;
use shopack\aaa\frontend\common\models\GeoTownModel;

$this->title = Yii::t('aaa', 'Towns');
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="geo-town-index w-100">
  <div class='card border-default'>
		<div class='card-header bg-default'>
			<div class="float-end">
        <?= GeoTownModel::canCreate() ? Html::createButton(null, [
          'twnCityID' => $twnCityID ?? $_GET['twnCityID'] ?? null
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
					'twnCityID' => $twnCityID ?? $_GET['twnCityID'] ?? null,
				]);
			?>
    </div>
  </div>
</div>
