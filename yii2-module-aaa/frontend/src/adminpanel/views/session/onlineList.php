<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\common\helpers\Html;

$this->title = Yii::t('aaa', 'Online Sessions');
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="session-index w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
			</div>
      <div class='card-title'><?= Html::encode($this->title) ?></div>
			<div class="clearfix"></div>
		</div>

    <div class='card-body'>
      <?php
				echo Yii::$app->controller->renderPartial('_online-list.php', [
					'searchModel' => $searchModel,
					'dataProvider' => $dataProvider,
				]);
			?>
    </div>
  </div>
</div>
