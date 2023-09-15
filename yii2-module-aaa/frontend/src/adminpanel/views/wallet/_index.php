<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\common\widgets\grid\GridView;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\common\helpers\StringHelper;
use shopack\aaa\frontend\common\models\WalletModel;
use shopack\aaa\common\enums\enuWalletStatus;
?>

<?php
  $walOwnerUserID = Yii::$app->request->queryParams['walOwnerUserID'] ?? null;
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
    'walID',
  ];

  if (empty($walOwnerUserID)) {
    $columns = array_merge($columns, [
      [
        'class' => \shopack\aaa\frontend\common\widgets\grid\UserDataColumn::class,
        'attribute' => 'walOwnerUserID',
        'format' => 'raw',
        'value' => function ($model, $key, $index, $widget) {
          return Html::a($model->owner->displayName(), Yii::$app->getModule('aaa')->createUserViewUrl($model->walOwnerUserID)); //, ['class' => ['btn', 'btn-sm', 'btn-outline-secondary']]);
        },
      ],
    ]);
  }

  $columns = array_merge($columns, [
    [
      'attribute' => 'walName',
      'format' => 'raw',
      'value' => function ($model, $key, $index, $widget) {
        return Html::a($model->walName, ['view', 'id' => $model->walID]);
      },
    ],
    [
      'attribute' => 'walRemainedAmount',
      'format' => 'toman',
      'contentOptions' => [
        'class' => ['text-nowrap', 'tabular-nums'],
      ],
    ],
    'walIsDefault:boolean',
    [
      'class' => \shopack\base\frontend\common\widgets\grid\EnumDataColumn::class,
      'enumClass' => enuWalletStatus::class,
      'attribute' => 'walStatus',
    ],
    [
      'attribute' => 'rowDate',
      'noWrap' => true,
      'format' => 'raw',
      'label' => 'ایجاد / ویرایش',
      'value' => function($model) {
        return Html::formatRowDates(
          $model->walCreatedAt,
          $model->createdByUser,
          $model->walUpdatedAt,
          $model->updatedByUser,
          $model->walRemovedAt,
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
