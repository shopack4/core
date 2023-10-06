<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\common\widgets\grid\GridView;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\common\helpers\StringHelper;
use shopack\aaa\common\enums\enuUserStatus;
use shopack\aaa\frontend\common\models\UserModel;

$this->title = Yii::t('aaa', 'Users');
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="user-index w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
				<?= Html::a(Yii::t('app', 'Create'), ['create'], ['class' => 'btn btn-sm btn-success']) ?>
			</div>
      <div class='card-title'><?= Html::encode($this->title) ?></div>
			<div class="clearfix"></div>
		</div>

    <div class='card-body'>
      <?php
        echo GridView::widget([
          'id' => StringHelper::generateRandomId(),
          'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
				'rowOptions' => function($model) {
					if ($model->usrStatus == enuUserStatus::Removed)
						return ['class' => 'table-danger'];
				},
        'columns' => [
          [
            'class' => 'kartik\grid\SerialColumn',
            // 'contentOptions' => ['class' => 'kartik-sheet-style'],
            // 'width' => '36px',
            // 'pageSummary' => 'Total',
            // 'pageSummaryOptions' => ['colspan' => 6],
            // 'header' => '',
            // 'headerOptions' => ['class' => 'kartik-sheet-style']
          ],
          [
            'attribute' => 'image',
            'label' => '',
            'format' => 'raw',
            'value' => function ($model, $key, $index, $widget) {
              if ($model->usrImageFileID == null)
                return '';
              elseif (empty($model->imageFile->fullFileUrl))
                return Yii::t('aaa', '...');
              elseif ($model->imageFile->isImage())
                return Html::img($model->imageFile->fullFileUrl, ['style' => ['width' => '50px']]);
              else
                return Html::a(Yii::t('app', 'Download'), $model->imageFile->fullFileUrl);
            },
          ],
          [
            'attribute' => 'usrID',
            'format' => 'raw',
            'value' => function ($model, $key, $index, $widget) {
              return Html::a($model->usrID, ['view', 'id' => $model->usrID]);
            },
          ],
          // 'usrGender',
          'usrFirstName',
          'usrLastName',
          'usrSSID',
          [
            'attribute' => 'usrMobile',
            'contentOptions' => [
              'class' => 'dir-ltr text-start tabular-nums',
            ],
            'format' => 'raw',
            'value' => function ($model, $key, $index, $widget) {
              if (empty($model->usrMobile))
                return null;
              return Yii::$app->formatter->asPhone($model->usrMobile);
              // return Html::a(Yii::$app->formatter->asPhone($model->usrMobile), ['view', 'id' => $model->usrID]);
            },
          ],
          [
            'attribute' => 'usrEmail',
            'contentOptions' => ['class' => 'dir-ltr text-start'],
            // 'format' => 'raw',
            // 'value' => function ($model, $key, $index, $widget) {
            //   if (empty($model->usrEmail))
            //     return null;
            //   return Html::a($model->usrEmail, ['view', 'id' => $model->usrID]);
            // },
          ],
          [
            'attribute' => 'hasPassword',
            'format' => 'boolean',
            // 'value' => function ($model, $key, $index, $widget) {
            //   return $model->hasPassword ? Yii::t('app', 'Has') : Yii::t('app', 'Has not');
            // },
          ],
          [
            'attribute' => 'rolName',
            'label' => 'جایگاه دسترسی',
            'value' => function($model) {
              return $model->role->rolName;
            },
          ],
          [
            'class' => \shopack\base\frontend\common\widgets\grid\EnumDataColumn::class,
            'enumClass' => enuUserStatus::class,
            'attribute' => 'usrStatus',
          ],
          [
            'class' => \shopack\base\frontend\common\widgets\ActionColumn::class,
            'header' => UserModel::canCreate() ? Html::createButton(null, null, [
              'data-popup-size' => 'lg',
            ]) : Yii::t('app', 'Actions'),
            'template' => '{update} {delete}{undelete}',
            'updateOptions' => [
              'modal' => true,
              'data-popup-size' => 'lg',
            ],
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
          'usrCreatedAt:jalaliWithTime',
          'usrUpdatedAt:jalaliWithTime',
          // [
          //   'attribute' => 'rowDate',
          //   'noWrap' => true,
          //   'format' => 'raw',
          //   'label' => 'ایجاد / ویرایش',
          //   'value' => function($model) {
          //     return Html::formatRowDates(
          //       $model->usrCreatedAt,
          //       $model->createdByUser,
          //       $model->usrUpdatedAt,
          //       $model->updatedByUser,
          //       $model->usrRemovedAt,
          //       $model->removedByUser,
          //     );
          //   },
          // ],
        ],
        // 'headerContainer' => ['style' => 'top:50px', 'class' => 'kv-table-header'], // offset from top
        // 'floatHeader' => true, // table header floats when you scroll
        // 'floatPageSummary' => true, // table page summary floats when you scroll
        // 'floatFooter' => false, // disable floating of table footer
        // 'pjax' => false, // pjax is set to always false for this demo
        // // parameters from the demo form
        // 'responsive' => false,
        // 'bordered' => true,
        // 'striped' => false,
        // 'condensed' => true,
        // 'hover' => true,
        // 'showPageSummary' => true,
        // 'panel' => [
        //     'after' => '<div class="float-right float-end"><button type="button" class="btn btn-primary" onclick="var keys = $("#kv-grid-demo").yiiGridView("getSelectedRows").length; alert(keys > 0 ? "Downloaded " + keys + " selected books to your account." : "No rows selected for download.");"><i class="fas fa-download"></i> Download Selected</button></div><div style="padding-top: 5px;"><em>* The page summary displays SUM for first 3 amount columns and AVG for the last.</em></div><div class="clearfix"></div>',
        //     'heading' => '<i class="fas fa-book"></i>  Library',
        //     'type' => 'primary',
        //     'before' => '<div style="padding-top: 7px;"><em>* Resize table columns just like a spreadsheet by dragging the column edges.</em></div>',
        // ],
        // // set export properties
        // 'export' => [
        //   'fontAwesome' => true
        // ],
        // 'exportConfig' => [
        //   'html' => [],
        //   'csv' => [],
        //   'txt' => [],
        //   'xls' => [],
        //   'pdf' => [],
        //   'json' => [],
        // ],
        // // set your toolbar
        // 'toolbar' => [
        //     [
        //         'content' =>
        //             Html::button('<i class="fas fa-plus"></i>', [
        //                 'class' => 'btn btn-success',
        //                 'title' => Yii::t('kvgrid', 'Add Book'),
        //                 'onclick' => 'alert("This should launch the book creation form.\n\nDisabled for this demo!");'
        //             ]) . ' '.
        //             Html::a('<i class="fas fa-redo"></i>', ['grid-demo'], [
        //                 'class' => 'btn btn-outline-secondary',
        //                 'title' => Yii::t('kvgrid', 'Reset Grid'),
        //                 'data-pjax' => 0,
        //             ]),
        //         'options' => ['class' => 'btn-group mr-2 me-2']
        //     ],
        //     '{export}',
        //     '{toggleData}',
        // ],
        // 'toggleDataContainer' => ['class' => 'btn-group mr-2 me-2'],
        // 'persistResize' => false,
        // 'toggleDataOptions' => ['minCount' => 10],
        // 'itemLabelSingle' => 'book',
        // 'itemLabelPlural' => 'books'
        ]);
      ?>
    </div>
  </div>
</div>
