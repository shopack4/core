<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\widgets\grid\GridView;
use shopack\base\frontend\helpers\Html;
use shopack\base\common\helpers\StringHelper;
use shopack\aaa\frontend\common\models\ApprovalRequestModel;
use shopack\aaa\common\enums\enuApprovalRequestStatus;
?>

<?php
	// echo Alert::widget(['key' => 'shoppingcart']);

	// if (isset($statusReport))
	// 	echo (is_array($statusReport) ? Html::icon($statusReport[0], ['plugin' => 'glyph']) . ' ' . $statusReport[1] : $statusReport);

  echo GridView::widget([
    'id' => StringHelper::generateRandomId(),
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,

    'columns' => [
      [
        'class' => 'kartik\grid\SerialColumn',
      ],
      'aprID',
      'aprUserID',
      // 'aprKeyType',
      [
        'attribute' => 'aprKey',
        'contentOptions' => ['class' => 'dir-ltr text-start'],
      ],
      // 'aprCode',
      'aprLastRequestAt:jalaliWithTime',
      'aprExpireAt:jalaliWithTime',
      'aprSentAt:jalaliWithTime',
      'aprApplyAt:jalaliWithTime',
      [
        'class' => \shopack\base\frontend\widgets\grid\EnumDataColumn::class,
        'enumClass' => enuApprovalRequestStatus::class,
        'attribute' => 'aprStatus',
      ],
      [
        'attribute' => 'rowDate',
        'noWrap' => true,
        'format' => 'raw',
        'label' => 'ایجاد / ویرایش',
        'value' => function($model) {
          return Html::formatRowDates(
            $model->aprCreatedAt,
            $model->createdByUser,
            $model->aprUpdatedAt,
            $model->updatedByUser,
            $model->aprRemovedAt,
            $model->removedByUser,
          );
        },
      ],
    ],
  ]);

?>
