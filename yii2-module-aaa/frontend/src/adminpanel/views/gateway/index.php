<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\common\helpers\HttpHelper;
use shopack\base\frontend\widgets\grid\GridView;
use shopack\base\frontend\helpers\Html;
use shopack\base\common\helpers\StringHelper;
use shopack\aaa\common\enums\enuGatewayStatus;
use shopack\aaa\frontend\common\models\GatewayModel;

$this->title = Yii::t('aaa', 'Gateways');
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="gateway-index w-100">
  <div class='card border-default'>
		<div class='card-header bg-default'>
			<div class="float-end">
        <?= GatewayModel::canCreate() ? Html::createButton() : '' ?>
			</div>
      <div class='card-title'><?= Html::encode($this->title) ?></div>
			<div class="clearfix"></div>
		</div>

    <div class='card-body'>
      <?php
        $pluginCategories = [];
        $result = HttpHelper::callApi('aaa/gateway/plugin-list');
        if ($result[0] == 200) {
          foreach ($result[1] as $k => $v) {
            $pluginCategories = array_merge($pluginCategories, [
              $k => Yii::t('aaa', $k), //array_keys($v),
            ]);
          }
        }

        echo GridView::widget([
          'id' => StringHelper::generateRandomId(),
          'dataProvider' => $dataProvider,
          'filterModel' => $searchModel,

          'columns' => [
            [
              'class' => 'kartik\grid\SerialColumn',
            ],
            'gtwID',
            [
              'attribute' => 'gtwName',
              'format' => 'raw',
              'value' => function ($model, $key, $index, $widget) {
                return Html::a($model->gtwName, ['view', 'id' => $model->gtwID]);
              },
            ],
            // 'gtwUUID',

            // [
            //   'attribute' => 'gtwPluginType',
            //   'value' => function ($model, $key, $index, $widget) {
            //     return Yii::t('aaa', $model->gtwPluginType);
            //   },
            // ],
            [
              'class' => \shopack\base\frontend\widgets\grid\LookupDataColumn::class,
              'lookupData' => $pluginCategories,
              'attribute' => 'gtwPluginType',
            ],

            'gtwPluginName',
            // 'gtwPluginParameters',

            [
              'class' => \shopack\base\frontend\widgets\grid\EnumDataColumn::class,
              'enumClass' => enuGatewayStatus::class,
              'attribute' => 'gtwStatus',
            ],
            [
              'class' => \shopack\base\frontend\widgets\ActionColumn::class,
              'header' => GatewayModel::canCreate() ? Html::createButton() : Yii::t('app', 'Actions'),
              'template' => '{update} {delete}{undelete}',
              'visibleButtons' => [
                'update' => function ($model, $key, $index) {
                  return $model->canUpdate();
                },
                'delete' => function ($model, $key, $index) {
                  return $model->canDelete();
                },
                'undelete' => function ($model, $key, $index) {
                  return $model->canUndelete();
                },
              ],
            ],
            [
              'attribute' => 'rowDate',
              'noWrap' => true,
              'format' => 'raw',
              'label' => 'ایجاد / ویرایش',
              'value' => function($model) {
                return Html::formatRowDates(
                  $model->gtwCreatedAt,
                  $model->createdByUser,
                  $model->gtwUpdatedAt,
                  $model->updatedByUser,
                  $model->gtwRemovedAt,
                  $model->removedByUser,
                );
              },
            ],
          ],
        ]);
      ?>
    </div>
  </div>
</div>
