<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\common\helpers\Json;
use shopack\base\frontend\widgets\grid\GridView;
use shopack\base\frontend\helpers\Html;
use shopack\base\common\helpers\StringHelper;
use shopack\aaa\common\enums\enuOnlinePaymentStatus;
use shopack\aaa\frontend\common\models\OnlinePaymentModel;
?>

<?php
  $vchOwnerUserID = Yii::$app->request->queryParams['vchOwnerUserID'] ?? null;
?>

<?php
	// echo Alert::widget(['key' => 'shoppingcart']);

	if (isset($statusReport))
		echo $statusReport;

    // (is_array($statusReport) ? Html::icon($statusReport[0], ['plugin' => 'glyph']) . ' ' . $statusReport[1] : $statusReport);

  $columns = [
    [
      'class' => 'kartik\grid\SerialColumn',
    ],
    [
      'class' => 'kartik\grid\ExpandRowColumn',
      'value' => function ($model, $key, $index, $column) {
        return GridView::ROW_COLLAPSED;
      },
      'expandOneOnly' => true,
      'detailAnimationDuration' => 150,
      'detail' => function ($model) {
        if (empty($model->onpResult))
          return '';

        return '<pre class="dir-ltr">' . Json::encode($model->onpResult) . '</pre>';
      },
    ],
  ];

  if (empty($vchOwnerUserID)) {
    $columns = array_merge($columns, [
      [
        'class' => \shopack\aaa\frontend\common\widgets\grid\UserDataColumn::class,
        'attribute' => 'vchOwnerUserID',
        // 'label' => 'مالک',
        'format' => 'raw',
        'value' => function($model) {
          return Html::a($model->voucher->owner->displayName(), Yii::$app->getModule('aaa')->createUserViewUrl($model->voucher->vchOwnerUserID));
        },
      ],
    ]);
  }

  $columns = array_merge($columns, [
    'onpID',
    'onpAmount:toman',
    [
      'attribute' => 'onpGatewayID',
      'value' => function($model) {
        return $model->gateway->gtwName;
      },
    ],
    [
      'attribute' => 'onpTrackNumber',
      'contentOptions' => [
        'class' => ['small'],
      ],
    ],
    [
      'attribute' => 'onpRRN',
      'contentOptions' => [
        'class' => ['small'],
      ],
    ],
    [
      'class' => \shopack\base\frontend\widgets\grid\EnumDataColumn::class,
      'enumClass' => enuOnlinePaymentStatus::class,
      'attribute' => 'onpStatus',
    ],
    [
      'class' => \shopack\base\frontend\widgets\ActionColumn::class,
      // 'header' => OnlinePaymentModel::canCreate() ? Html::createButton() : Yii::t('app', 'Actions'),
      'template' => '',
    ],
    [
      'attribute' => 'rowDate',
      'noWrap' => true,
      'format' => 'raw',
      'label' => 'ایجاد / ویرایش',
      'value' => function($model) {
        return Html::formatRowDates(
          $model->onpCreatedAt,
          $model->createdByUser,
          $model->onpUpdatedAt,
          $model->updatedByUser,
          $model->onpRemovedAt,
          $model->removedByUser,
        );
      },
    ],
  ]);

  echo GridView::widget([
    'id' => StringHelper::generateRandomId(),
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $columns,
  ]);

?>
