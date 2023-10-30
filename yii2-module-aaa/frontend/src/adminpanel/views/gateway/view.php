<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\frontend\common\widgets\PopoverX;
use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\HttpHelper;
use shopack\base\frontend\common\widgets\DetailView;
use shopack\base\frontend\common\helpers\Html;
use shopack\aaa\common\enums\enuGatewayStatus;
use shopack\aaa\frontend\common\models\GatewayModel;

$this->title = Yii::t('aaa', 'Gateway') . ': ' . $model->gtwID . ' - ' . $model->gtwName;
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = ['label' => Yii::t('aaa', 'Gateways'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="gateway-view w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
				<?= GatewayModel::canCreate() ? Html::createButton() : '' ?>
        <?= $model->canUpdate()   ? Html::updateButton(null,   ['id' => $model->gtwID]) : '' ?>
        <?= $model->canDelete()   ? Html::deleteButton(null,   ['id' => $model->gtwID]) : '' ?>
        <?= $model->canUndelete() ? Html::undeleteButton(null, ['id' => $model->gtwID]) : '' ?>
        <?php
          PopoverX::begin([
            // 'header' => 'Hello world',
            'closeButton' => false,
            'toggleButton' => [
              'label' => Yii::t('app', 'Logs'),
              'class' => 'btn btn-sm btn-outline-secondary',
            ],
            'placement' => PopoverX::ALIGN_AUTO_BOTTOM,
          ]);

          echo DetailView::widget([
            'model' => $model,
            'enableEditMode' => false,
            'attributes' => [
              'gtwCreatedAt:jalaliWithTime',
              [
                'attribute' => 'gtwCreatedBy_User',
                'format' => 'raw',
                'value' => $model->createdByUser->actorName ?? '-',
              ],
              'gtwUpdatedAt:jalaliWithTime',
              [
                'attribute' => 'gtwUpdatedBy_User',
                'format' => 'raw',
                'value' => $model->updatedByUser->actorName ?? '-',
              ],
              'gtwRemovedAt:jalaliWithTime',
              [
                'attribute' => 'gtwRemovedBy_User',
                'format' => 'raw',
                'value' => $model->removedByUser->actorName ?? '-',
              ],
            ],
          ]);

          PopoverX::end();
        ?>
			</div>
      <div class='card-title'><?= Html::encode($this->title) ?></div>
			<div class="clearfix"></div>
		</div>
    <div class='card-body'>
      <?php
        $attributes = [
          'gtwID',
          [
            'attribute' => 'gtwStatus',
            'value' => enuGatewayStatus::getLabel($model->gtwStatus),
          ],
          'gtwName',
          [
            'attribute' => 'gtwUUID',
            'valueColOptions' => ['class' => ['latin-text']],
          ],
          [
            'attribute' => 'gtwPluginType',
            'value' => Yii::t('aaa', $model->gtwPluginType),
          ],
          'gtwPluginName',
          // 'gtwRemovedAt',
          // 'gtwRemovedBy',
        ];

        $fnShowKindSchema = function($kind, $prop) use($model, &$attributes) {
          if ($model->canViewColumn($prop)) {
            $result = HttpHelper::callApi("aaa/gateway/plugin-{$kind}-schema", HttpHelper::METHOD_GET, [
              'key' => $model->gtwPluginName,
            ]);

            if ($result && $result[0] == 200) {
              $list = $result[1];

              if (empty($list) == false) {
                $tableRows = [];

                $tableRows[] = Html::tag('tr',
                    Html::tag('th', Yii::t('app', 'Name'))
                  . Html::tag('th', Yii::t('app', 'Value'))
                );

                foreach ($list as $item) {
                  if (isset($model->$prop[$item['id']])) {
                    if ($item['type'] == 'password')
                      $paramValue = '********';
                    else if ($item['type'] == 'kvp-multi') {
                      if (empty($model->$prop[$item['id']]))
                        $paramValue = '';
                      else {
                        $kvprows = [];
                        $cols = [];

                        $enableFieldID = null;
                        if (empty($item['typedef']['enableField']) == false) {
                          $enableFieldID = $item['typedef']['enableField']['id'] ?? 'enable';
                        }

                        //-- header
                        if (empty($item['typedef']['enableField']) == false) {
                          $cols[] = Html::tag('th', Yii::t('app', $item['typedef']['enableField']['label'] ?? 'Enable'));
                        }

                        $cols[] = Html::tag('th', Yii::t('app', $item['typedef']['key']['label']));

                        foreach ($item['typedef']['value'] as $col) {
                          $cols[] = Html::tag('th', Yii::t('app', $col['label']));
                        }
                        $kvprows[] = Html::tag('tr', implode('', $cols));

                        //-- values
                        foreach ($model->$prop[$item['id']] as $vp) {
                          $cols = [];

                          if ($enableFieldID) {
                            $cols[] = Html::tag('td',
                              Yii::$app->formatter->asBoolean(($vp[$enableFieldID] ?? false)));
                          }

                          $cols[] = Html::tag('td', $vp['key']);

                          foreach ($vp['value'] as $col) {
                            $cols[] = Html::tag('td', Yii::t('app', $col));
                          }

                          $kvprows[] = Html::tag('tr', implode('', $cols));
                        }
                        $paramValue = Html::tag('table', implode('', $kvprows), [
                          'class' => ['table', 'table-bordered', 'table-striped'],
                        ]);
                      }
                    } else
                      $paramValue = $model->$prop[$item['id']];
                  } else
                    $paramValue = '';

                  $paramValueIsEmptyOrZero = (empty($paramValue) || ($paramValue == 0) || ($paramValue == '0'));

                  if ($paramValueIsEmptyOrZero == false) {
                    if (isset($item['format'])) {
                      $format = 'as' . ucfirst($item['format']);
                      $paramValue = Yii::$app->formatter->$format($paramValue);
                    }

                    if (isset($item['format-suffix'])) {
                      $paramValue .= ' ' . $item['format-suffix'];
                    }
                  }

                  if (isset($item['data']) && is_array($item['data'])) {
                    $paramValue = Yii::t('aaa', $item['data'][$paramValue] ?? $paramValue);
                  }

                  $tableRows[] = Html::tag('tr',
                      Html::tag('td', Yii::t('aaa', $item['label']), ['class' => ['headcell']])
                    . Html::tag('td', $paramValue)
                  );
                }

                array_push($attributes, [
                  'attribute' => $kind,
                  'label' => $model->getAttributeLabel($prop),
                  'format' => 'raw',
                  // 'valueColOptions' => ['class' => ['dir-ltr', 'latin-text']],
                  'value' => Html::tag('table', implode('', $tableRows), [
                    'class' => ['table', 'table-bordered', 'table-striped'],
                  ]),
                ]);
              }
            }
          }
        };

        //params
        $fnShowKindSchema('params', 'gtwPluginParameters');

        //restrictions
        $fnShowKindSchema('restrictions', 'gtwRestrictions');

        //usages
        $fnShowKindSchema('usages', 'gtwUsages');

        //payment
        if ($model->gtwPluginType == 'payment') {
          array_push($attributes, [
            'attribute' => 'callbackurl',
            'label' => Yii::t('aaa', 'Callback Url'),
            'format' => 'raw',
            'valueColOptions' => ['class' => ['dir-ltr', 'latin-text']],
            'value' => Url::to(['/aaa/payment/callback'], true) . '/{ONP-ID}',
          ]);
        }

        //webhooks
        $result = HttpHelper::callApi('aaa/gateway/plugin-webhooks-schema', HttpHelper::METHOD_GET, [
          'key' => $model->gtwPluginName,
        ]);
        if ($result && $result[0] == 200) {
          $list = $result[1];

          if (isset($list)) {
            $tableRows = [];

            $tableRows[] = Html::tag('tr',
                Html::tag('th', Yii::t('aaa', 'Name'))
              . Html::tag('th', Yii::t('aaa', 'Address'))
            );

            foreach ($list as $key => $item) {
              $tableRows[] = Html::tag('tr',
                  Html::tag('td', Yii::t('aaa', $item['title']), ['class' => ['headcell']])
                . Html::tag('td', Url::to(['/aaa/gateway/webhook',
                    'gtwUUID' => $model->gtwUUID,
                    'command' => $key,
                  ], true), ['class' => ['dir-ltr', 'latin-text']])
              );
            }

            array_push($attributes, [
              'attribute' => 'webhooks',
              'label' => Yii::t('aaa', 'Webhooks'),
              'format' => 'raw',
              // 'valueColOptions' => ['class' => ['dir-ltr', 'latin-text']],
              'value' => Html::tag('table', implode('', $tableRows), [
                'class' => ['table', 'table-bordered', 'table-striped'],
              ]),
            ]);

          }
        }

        echo DetailView::widget([
          'model' => $model,
          'enableEditMode' => false,
          'attributes' => $attributes,
        ]);
      ?>
    </div>
  </div>
</div>
