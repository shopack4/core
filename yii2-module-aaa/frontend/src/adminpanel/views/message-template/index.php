<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\widgets\grid\GridView;
use shopack\base\frontend\helpers\Html;
use shopack\base\common\helpers\StringHelper;
use shopack\aaa\common\enums\enuMessageTemplateStatus;
use shopack\aaa\common\enums\enuMessageTemplateMedia;
use shopack\aaa\frontend\common\models\MessageTemplateModel;

$this->title = Yii::t('aaa', 'Message Templates');
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="message-template-index w-100">
  <div class='card border-default'>
		<div class='card-header bg-default'>
			<div class="float-end">
        <?= MessageTemplateModel::canCreate() ? Html::createButton() : '' ?>
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

        'columns' => [
          [
            'class' => 'kartik\grid\SerialColumn',
          ],
          'mstID',
          [
            'class' => 'kartik\grid\ExpandRowColumn',
            'value' => function ($model, $key, $index, $column) {
              return GridView::ROW_COLLAPSED;
            },
            'expandOneOnly' => true,
            'detailAnimationDuration' => 150,
            'detail' => function ($model) {
              if (empty($model->mstBody))
                return '';

              return '<pre class="text-start">' . str_replace('\n', '<br>', $model->mstBody) . '</pre>';
            },
          ],
          'mstKey',
          [
            'class' => \shopack\base\frontend\widgets\grid\EnumDataColumn::class,
            'enumClass' => enuMessageTemplateMedia::class,
            'attribute' => 'mstMedia',
          ],
          'mstLanguage',
          'mstTitle',
          // 'mstParamsPrefix',
          // 'mstParamsSuffix',
          'mstIsSystem:booleanean',
          [
            'class' => \shopack\base\frontend\widgets\grid\EnumDataColumn::class,
            'enumClass' => enuMessageTemplateStatus::class,
            'attribute' => 'mstStatus',
          ],
          [
            'class' => \shopack\base\frontend\widgets\ActionColumn::class,
            'header' => MessageTemplateModel::canCreate() ? Html::createButton() : Yii::t('app', 'Actions'),
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
                $model->mstCreatedAt,
                $model->createdByUser,
                $model->mstUpdatedAt,
                $model->updatedByUser,
                $model->mstRemovedAt,
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
