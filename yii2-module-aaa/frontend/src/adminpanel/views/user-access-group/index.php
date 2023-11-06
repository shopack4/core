<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\common\widgets\grid\GridView;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\common\helpers\StringHelper;
// use shopack\aaa\common\enums\enuUserAccessGroupStatus;
use shopack\aaa\frontend\common\models\UserAccessGroupModel;

$this->title = Yii::t('aaa', 'User Access Groups');
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="user-access-group-index w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
        <?= UserAccessGroupModel::canCreate() ? Html::createButton(null, [
          'create',
          'usragpUserID' => $usragpUserID ?? $_GET['usragpUserID'] ?? null,
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
