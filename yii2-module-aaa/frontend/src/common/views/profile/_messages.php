<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\common\helpers\Json;
use shopack\base\frontend\common\widgets\grid\GridView;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\common\helpers\StringHelper;
use shopack\aaa\common\enums\enuMessageStatus;
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
      'class' => 'shopack\base\frontend\common\widgets\grid\ExpandRowColumn',
      'value' => function ($model, $key, $index, $column) {
        return GridView::ROW_COLLAPSED;
      },
      'detail' => function ($model) {
        $details = [];

        if (empty($model->msgInfo) == false) {
          $details[] = '<pre class="dir-ltr">Info: ' . Json::encode($model->msgInfo) . '</pre>';
        }

        if (empty($model->msgResult) == false) {
          $details[] = '<pre class="dir-ltr">Result: ' . Json::encode($model->msgResult) . '</pre>';
        }

        return implode('', $details);
      },
    ],
    'msgID',
  ];

  if (empty($msgUserID)) {
    $columns = array_merge($columns, [
      [
        'class' => \shopack\aaa\frontend\common\widgets\grid\UserDataColumn::class,
        'attribute' => 'msgUserID',
        // 'label' => 'مالک',
        'format' => 'raw',
        'value' => function($model) {
          return Html::a($model->user->displayName(),
            ['/aaa/user/view', 'id' => $model->msgUserID],
            //Yii::$app->getModule('aaa')->createUserViewUrl($model->msgUserID)
            );
        },
      ],
    ]);
  }

  $columns = array_merge($columns, [
    [
      'attribute' => 'msgTarget',
      'contentOptions' => ['class' => 'dir-ltr text-start'],
    ],
    // 'msgApprovalRequestID',
    // 'msgForgotPasswordRequestID',
    'msgTypeKey',
    // 'msgInfo',
    // 'msgIssuer',
    // 'msgLockedAt',
    // 'msgLockedBy',
    [
      'attribute' => 'msgLastTryAt',
      'contentOptions' => ['class' => 'small'],
      'format' => 'jalaliWithTime',
    ],
    [
      'attribute' => 'msgSentAt',
      'contentOptions' => ['class' => 'small'],
      'format' => 'jalaliWithTime',
    ],
    [
      'class' => \shopack\base\frontend\common\widgets\grid\EnumDataColumn::class,
      'enumClass' => enuMessageStatus::class,
      'attribute' => 'msgStatus',
    ],
    [
      'class' => \shopack\base\frontend\common\widgets\ActionColumn::class,
      'template' => '',
    ],
    [
      'attribute' => 'rowDate',
      'noWrap' => true,
      'format' => 'raw',
      'label' => 'ایجاد / ویرایش',
      'value' => function($model) {
        return Html::formatRowDates(
          $model->msgCreatedAt,
          $model->createdByUser,
          $model->msgUpdatedAt,
          $model->updatedByUser,
          $model->msgRemovedAt,
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
