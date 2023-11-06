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
?>

<?php
  $usragpUserID = Yii::$app->request->queryParams['usragpUserID'] ?? null;
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
    'usragpID',
  ];

  if (empty($usragpUserID)) {
    $columns = array_merge($columns, [
      [
        'class' => \shopack\aaa\frontend\common\widgets\grid\UserDataColumn::class,
        'attribute' => 'usragpUserID',
        // 'label' => 'مالک',
        'format' => 'raw',
        'value' => function($model) {
          return Html::a($model->user->displayName(), Yii::$app->getModule('aaa')->createUserViewUrl($model->usragpUserID));
        },
      ],
    ]);
  }

  $columns = array_merge($columns, [
    [
      'attribute' => 'usragpAccessGroupID',
      'format' => 'raw',
      'value' => function ($model, $key, $index, $widget) {
        return Html::a($model->accessGroup->agpName, ['/aaa/access-group/view', 'id' => $model->usragpAccessGroupID]);
      },
    ],
    'usragpStartAt:jalaliWithTime',
    'usragpEndAt:jalaliWithTime',
    // [
    //   'class' => \shopack\base\frontend\common\widgets\grid\EnumDataColumn::class,
    //   'enumClass' => enuUserAccessGroupStatus::class,
    //   'attribute' => 'usragpStatus',
    // ],
    [
      'class' => \shopack\base\frontend\common\widgets\ActionColumn::class,
      'header' => UserAccessGroupModel::canCreate() ? Html::createButton(null, [
        'create',
        'usragpUserID' => $usragpUserID ?? $_GET['usragpUserID'] ?? null,
      ]) : Yii::t('app', 'Actions'),
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
          $model->usragpCreatedAt,
          $model->createdByUser,
          $model->usragpUpdatedAt,
          $model->updatedByUser,
          $model->usragpRemovedAt,
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
