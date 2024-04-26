<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\common\helpers\StringHelper;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\grid\GridView;
?>

<?php
  $modelClass = Yii::$app->controller->modelClass;
  $dscusgDiscountID = Yii::$app->request->queryParams['dscusgDiscountID'] ?? null;
?>

<div class='row'>
  <div class='col'>
    <?php
      // echo Alert::widget(['key' => 'shoppingcart']);

      // if (isset($statusReport))
      // 	echo (is_array($statusReport) ? Html::icon($statusReport[0], ['plugin' => 'glyph']) . ' ' . $statusReport[1] : $statusReport);

      $columns = [
        [
          'class' => 'kartik\grid\SerialColumn',
        ],
        'dscusgID',
      ];

      // if (empty($dscusgDiscountID)) {
      //   $columns = array_merge($columns, [
      //     [
      //       'class' => \iranhmusic\shopack\aaa\frontend\common\widgets\grid\MemberDataColumn::class,
      //       'attribute' => 'dscusgDiscountID',
      //       'format' => 'raw',
      //       'value' => function ($model, $key, $index, $widget) {
      //         return Html::a($model->member->displayName(), ['/aaa/member/view', 'id' => $model->dscusgDiscountID]); //, ['class' => ['btn', 'btn-sm', 'btn-outline-secondary']]);
      //       },
      //     ],
      //   ]);
      // }

      $columns = array_merge($columns, [
        'dscusgDiscountID',
        'dscusgDiscountSerialID',
        'dscusgUserID',
        'dscusgUserAssetID',
        'dscusgAmount',

        [
          'class' => \shopack\base\frontend\common\widgets\ActionColumn::class,
          'header' => $modelClass::canCreate() ? Html::createButton(null, [
            'create',
            'dscusgDiscountID' => $dscusgDiscountID ?? $_GET['dscusgDiscountID'] ?? null,
          ]) : Yii::t('app', 'Actions'),
          'template' => '{delete}{undelete}',
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
              $model->dscusgCreatedAt,
              $model->createdByUser,
              $model->dscusgUpdatedAt,
              $model->updatedByUser,
              // $model->dscusgRemovedAt,
              // $model->removedByUser,
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
  </div>
</div>
