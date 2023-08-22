<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\widgets\grid\GridView;
use shopack\base\frontend\helpers\Html;
use shopack\base\common\helpers\StringHelper;
use shopack\aaa\common\enums\enuMessageStatus;
use shopack\aaa\frontend\common\models\MessageModel;

$this->title = Yii::t('aaa', 'Messages');
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="message-index w-100">
  <div class='card border-default'>
		<div class='card-header bg-default'>
			<div class="float-end">
        <?php /* MessageModel::canCreate() ? Html::createButton() : '' */ ?>
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
          [
            'class' => 'kartik\grid\ExpandRowColumn',
            'value' => function ($model, $key, $index, $column) {
              return GridView::ROW_COLLAPSED;
            },
            'expandOneOnly' => true,
            'detailAnimationDuration' => 150,
            'detail' => function ($model) {
              $details = [];

              if (empty($model->msgInfo) == false) {
                $details[] = '<pre class="dir-ltr">Info: ' . json_encode($model->msgInfo, JSON_UNESCAPED_UNICODE) . '</pre>';
              }

              if (empty($model->msgResult) == false) {
                $details[] = '<pre class="dir-ltr">Result: ' . json_encode($model->msgResult, JSON_UNESCAPED_UNICODE) . '</pre>';
              }

              return implode('', $details);
            },
          ],
          'msgID',
          [
            'attribute' => 'msgUserID',
            'format' => 'raw',
            'value' => function ($model, $key, $index, $widget) {
              if (empty($model->msgUserID))
                return null;
              return Html::a($model->user->displayName(), Yii::$app->getModule('aaa')->createUserViewUrl($model->msgUserID));
            },
          ],
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
            'class' => \shopack\base\frontend\widgets\grid\EnumDataColumn::class,
            'enumClass' => enuMessageStatus::class,
            'attribute' => 'msgStatus',
          ],
          // [
          //   'class' => \shopack\base\frontend\widgets\ActionColumn::class,
          //   'header' => MessageModel::canCreate() ? Html::createButton() : Yii::t('app', 'Actions'),
          //   'template' => '', //'{update} {delete}{undelete}',
          //   'visibleButtons' => [
          //     'update' => function ($model, $key, $index) {
          //       return $model->canUpdate();
          //     },
          //     'delete' => function ($model, $key, $index) {
          //       return $model->canDelete();
          //     },
          //     'undelete' => function ($model, $key, $index) {
          //       return $model->canUndelete();
          //     },
          //   ],
          // ],
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
        ],
      ]);
      ?>
    </div>
  </div>
</div>
