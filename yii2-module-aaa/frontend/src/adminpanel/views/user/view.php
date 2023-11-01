<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\common\helpers\Json;
use shopack\base\frontend\common\widgets\PopoverX;
use shopack\base\frontend\common\widgets\DetailView;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\tabs\Tabs;
use shopack\aaa\frontend\common\models\UserModel;
use shopack\aaa\common\enums\enuUserStatus;
use shopack\aaa\common\enums\enuGender;
use shopack\aaa\common\enums\enuUserEducationLevel;
use shopack\aaa\common\enums\enuUserMaritalStatus;
use shopack\aaa\common\enums\enuUserMilitaryStatus;

$this->title = Yii::t('aaa', 'User') . ': ' . $model->displayName();
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = ['label' => Yii::t('aaa', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="user-view w-100">
  <div class='card'>
		<div class='card-header'>
			<div class="float-end">
        <?= UserModel::canCreate() ? Html::createButton(null, null, [
          'data' => [
            'popup-size' => 'lg',
          ],
        ]) : '' ?>
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
              'usrCreatedAt:jalaliWithTime',
              [
                'attribute' => 'usrCreatedBy_User',
                'format' => 'raw',
                'value' => $model->createdByUser->actorName ?? '-',
              ],
              'usrUpdatedAt:jalaliWithTime',
              [
                'attribute' => 'usrUpdatedBy_User',
                'format' => 'raw',
                'value' => $model->updatedByUser->actorName ?? '-',
              ],
              'usrRemovedAt:jalaliWithTime',
              [
                'attribute' => 'usrRemovedBy_User',
                'format' => 'raw',
                'value' => $model->removedByUser->actorName ?? '-',
              ],
            ],
          ]);

          PopoverX::end();
        ?>
			</div>
      <div class='card-title'><?= $this->title ?></div>
			<div class="clearfix"></div>
		</div>

    <div class='card-tabs'>
  		<?php $tabs = Tabs::begin($this); ?>

      <?php $tabs->beginTabPage('مشخصات'); ?>
        <div>
          <div class='row'>
            <div class='col-9'>
              <?php
                echo DetailView::widget([
                  'model' => $model,
                  'enableEditMode' => false,
                  'cols' => 2,
                  'isVertical' => false,
                  'attributes' => [
                    'usrID',
                    [
                      'attribute' => 'usrStatus',
                      'value' => enuUserStatus::getLabel($model->usrStatus),
                    ],
                    [
                      'attribute' => 'usrGender',
                      'value' => enuGender::getLabel($model->usrGender),
                    ],
                    [
                      'group' => true,
                    ],
                    'usrSSID',
                    'usrBirthCertID',
                    'usrFirstName',
                    'usrFirstName_en',
                    'usrLastName',
                    'usrLastName_en',
                    'usrFatherName',
                    'usrFatherName_en',
                    [
                      'attribute' => 'usrBirthCityID',
                      'value' => $model->birthCityOrVillage->ctvName ?? null,
                    ],
                    'usrBirthDate:jalali',

                    [
                      'attribute' => 'usrEducationLevel',
                      'value' => enuUserEducationLevel::getLabel($model->usrEducationLevel),
                    ],
                    'usrFieldOfStudy',
                    'usrYearOfGraduation',
                    'usrEducationPlace',
                    [
                      'attribute' => 'usrMaritalStatus',
                      'value' => enuUserMaritalStatus::getLabel($model->usrMaritalStatus),
                    ],
                    [
                      'attribute' => 'usrMilitaryStatus',
                      'value' => enuUserMilitaryStatus::getLabel($model->usrMilitaryStatus),
                    ],

                    [
                      'group' => 'true',
                      'label' => 'اطلاعات ورود و دسترسی',
                      'isVertical' => false,
                      'groupOptions' => ['class' => 'info-row'],
                    ],
                    [
                      'attribute' => 'usrEmail',
                      'valueColOptions' => ['class' => ['dir-ltr', 'text-start']],
                    ],
                    'usrEmailApprovedAt:jalaliWithTime',
                    [
                      'attribute' => 'usrMobile',
                      'format' => 'phone',
                    ],
                    'usrMobileApprovedAt:jalaliWithTime',
                    [
                      'attribute' => 'hasPassword',
                      'format' => 'boolean',
                    ],
                    'usrPasswordCreatedAt:jalaliWithTime',
                    [
                      'attribute' => 'usrRoleID',
                      'label' => 'جایگاه دسترسی',
                      'value' => $model->role->rolName,
                    ],
                    [
                      'attribute' => 'usrPrivs',
                      'visible' => $model->canViewColumn('usrPrivs'),
                      'value' => Json::encode($model->usrPrivs),
                    ],

                    [
                      'group' => true,
                      'cols' => 1,
                      'label' => 'اطلاعات آدرس',
                      'groupOptions' => ['class' => 'info-row'],
                    ],
                    [
                      'attribute' => 'usrCountryID',
                      'value' => $model->country->cntrName ?? null,
                    ],
                    [
                      'attribute' => 'usrStateID',
                      'value' => $model->state->sttName ?? null,
                    ],
                    [
                      'attribute' => 'usrCityOrVillageID',
                      'value' => $model->cityOrVillage->ctvName ?? null,
                    ],
                    [
                      'attribute' => 'usrTownID',
                      'value' => $model->town->twnName ?? null,
                    ],
                    [
                      'attribute' => 'usrHomeAddress',
                      'value' => $model->usrHomeAddress,
                    ],
                    [
                      'attribute' => 'usrZipCode',
                      'value' => $model->usrZipCode,
                    ],
                    [
                      'attribute' => 'usrPhones',
                      'value' => $model->usrPhones,
                    ],
                    [
                      'attribute' => 'usrWorkAddress',
                      'value' => $model->usrWorkAddress,
                    ],
                    [
                      'attribute' => 'usrWorkPhones',
                      'value' => $model->usrWorkPhones,
                    ],
                    [
                      'attribute' => 'usrWebsite',
                      'value' => $model->usrWebsite,
                    ],
                  ],
                ]);
              ?>
            </div>
            <div class='col-3'>
            <div class='card border-default mb-3'>
                <div class='card-body'>
                  <?php
                    $buttons = [];

                    if ($model->canUpdate()) {
                      $buttons[] = Html::updateButton(null, ['id' => $model->usrID], [
                        'data' => [
                          'popup-size' => 'lg',
                        ],
                      ]);
                      $buttons[] = Html::updateButton('تعیین رمز', ['/aaa/user/password-reset', 'id' => $model->usrID], [
                        'btn' => 'warning',
                      ]);
                    }

                    if ($model->canDelete())
                      $buttons[] = Html::deleteButton(null, ['id' => $model->usrID]);

                    if ($model->canUndelete())
                      $buttons[] = Html::undeleteButton(null, ['id' => $model->usrID]);

                    if (empty($buttons) == false)
                      echo implode(' ', $buttons);
                  ?>
                </div>
              </div>

              <div class='card'>
                <div class='card-header'>
                  <div class="float-end">
                  </div>
                  <div class='card-title'><?= Yii::t('aaa', 'Image') ?></div>
                  <div class="clearfix"></div>
                </div>
                <div class='card-body text-center'>
                  <?php
                    if ($model->usrImageFileID == null)
                      echo Yii::t('app', 'not set');
                    elseif (empty($model->imageFile->fullFileUrl))
                      echo Yii::t('aaa', 'Uploading...');
                    elseif ($model->imageFile->isImage())
                      echo Html::img($model->imageFile->fullFileUrl, ['style' => ['width' => '100%']]);
                    else
                      echo Html::a(Yii::t('app', 'Download'), $model->imageFile->fullFileUrl);
                  ?>
                </div>
              </div>

            </div>
          </div>
        </div>
      <?php $tabs->endTabPage(); ?>

      <?php
        $tabs->beginTabPage(Yii::t('aaa', 'Financial'), [
          'wallets',
          'wallet-transactions',
          'orders',
          'online-payments',
          'offline-payments',
        ]);

        $tabs2 = Tabs::begin($this, [
          'pluginOptions' => [
            'id' => 'tabs_fin',
            // 'position' => \kartik\tabs\TabsX::POS_LEFT,
            // 'bordered' => true,
          ],
        ]);

        $tabs2->newAjaxTabPage(Yii::t('aaa', 'Wallets'), [
            '/aaa/wallet/index',
            'walOwnerUserID' => $model->usrID,
          ],
          'wallets'
        );

        $tabs2->newAjaxTabPage(Yii::t('aaa', 'Wallet Transactions'), [
            '/aaa/wallet-transaction/index',
            'walOwnerUserID' => $model->usrID,
          ],
          'wallet-transactions'
        );

        $tabs2->newAjaxTabPage(Yii::t('aaa', 'Orders'), [
            '/aaa/order/index',
            'vchOwnerUserID' => $model->usrID,
          ],
          'orders'
        );

        $tabs2->newAjaxTabPage(Yii::t('aaa', 'Online Payments'), [
            '/aaa/online-payment/index',
            'vchOwnerUserID' => $model->usrID,
          ],
          'online-payments'
        );

        $tabs2->newAjaxTabPage(Yii::t('aaa', 'Offline Payments'), [
            '/aaa/offline-payment/index',
            'ofpOwnerUserID' => $model->usrID,
          ],
          'offline-payments'
        );

        $tabs2->end();

        $tabs->endTabPage();
      ?>

      <?php $tabs->end(); ?>
    </div>
  </div>
</div>
