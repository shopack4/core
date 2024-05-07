<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\helpers\Json;
use shopack\aaa\common\enums\enuTwoFAType;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\DetailView;
use shopack\base\frontend\common\widgets\tabs\Tabs;
use shopack\aaa\common\enums\enuGender;
use shopack\aaa\common\enums\enuUserStatus;
use shopack\base\common\helpers\GeneralHelper;
use shopack\aaa\common\enums\enuUserEducationLevel;
use shopack\aaa\common\enums\enuUserMaritalStatus;
use shopack\aaa\common\enums\enuUserMilitaryStatus;
use shopack\base\frontend\common\widgets\grid\GridView;
use yii\data\ArrayDataProvider;

$this->title = Yii::t('aaa', 'My Profile');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="profile-view w-100">
	<div class='card border-0'>

		<div class='card-tabs'>
			<?php $tabs = Tabs::begin($this); ?>

			<?php $tabs->beginTabPage(Yii::t('aaa', 'My Details'), 'details'); ?>
				<div class='row'>
					<div class='col-sm-9'>
						<div class='card'>
							<div class='card-header'>
								<div class="float-end">
									<?= $model->canUpdate() ? Html::updateButton(null, ['update-user'], [
										'data' => [
											'popup-size' => 'lg',
										],
									]) : '' ?>
								</div>
								<div class='card-title'><?= Yii::t('aaa', 'My Details') ?></div>
								<div class="clearfix"></div>
							</div>
							<div class='card-body'>
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
												'label' => 'اطلاعات ورود',
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
											// [
											// 	'attribute' => 'usrRoleID',
											// 	'label' => 'جایگاه دسترسی',
											// 	'value' => $model->role->rolName,
											// ],
											// [
											// 	'attribute' => 'usrPrivs',
											// 	'visible' => $model->canViewColumn('usrPrivs'),
											// 	'value' => Json::encode($model->usrPrivs),
											// ],

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
						</div>
					</div>

					<div class='col-sm-3'>
						<div class='card border-default mb-3'>
							<div class='card-header'>
								<div class="float-end">
								</div>
								<div class='card-title'><?= Yii::t('aaa', 'Account') ?></div>
								<div class="clearfix"></div>
							</div>
							<div class='card-body text-center'>
								<?= $model->canDelete() ? Html::deleteButton(Yii::t('aaa', 'Delete Account'), ['id' => $model->usrID]) : '' ?>
							</div>
						</div>

						<div class='card'>
							<div class='card-header'>
								<div class="float-end">
									<?= Html::updateButton(Yii::t('aaa', 'Update Image'), ['update-image'], [
										// 'modal' => false,
									]) ?>
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
			<?php $tabs->endTabPage(); ?>

			<?php
				$tabs->beginTabPage(Yii::t('aaa', 'Login Information'), 'login');

				$columns = [
					[
						'attribute' => 'usrEmail',
						'format' => 'raw',
						'value' => function() use ($model) {
							$ret = [];

							$ret[] = "<div class='float-end'>";
							if (empty($model->usrEmail)) {
								$ret[] = Html::a(Yii::t('aaa', 'Set Email'), ['email-change'], [
									'class' => ['btn', 'btn-sm', 'btn-outline-primary'],
									'modal' => true,
								]);
							} else {
								$ret[] = Html::a(Yii::t('aaa', 'Change Email'), ['email-change'], [
									'class' => ['btn', 'btn-sm', 'btn-outline-primary'],
									'modal' => true,
								]);
							}
							$ret[] = "</div>";
							$ret[] = Html::div($model->usrEmail, ['class' => ['dir-ltr', 'text-start']]);
							$ret[] = "<div class='clearfix'></div>";

							return implode(' ', $ret);
						},
					],
					[
						'attribute' => 'usrEmailApprovedAt',
						'format' => 'raw',
						'value' => function() use ($model) {
							$ret = [];

							$ret[] = "<div class='float-end'>";
							if ((empty($model->usrEmail) == false) && empty($model->usrEmailApprovedAt)) {
								$ret[] = Html::confirmButton(Yii::t('aaa', 'Resend Email Approval'), ['resend-email-approval'],
								Yii::t('aaa', 'Do you want the email verification code to be sent to {email}?', ['email' => Html::span($model->usrEmail, ['class' => ['d-inline-block', 'dir-ltr']])]),
								[
									'class' => ['btn', 'btn-sm', 'btn-outline-primary'],
									'ajax' => 'post',
								]);
							}
							$ret[] = "</div>";
							$ret[] = Yii::$app->formatter->asJalaliWithTime($model->usrEmailApprovedAt, 'تایید نشده');
							$ret[] = "<div class='clearfix'></div>";

							return implode(' ', $ret);
						},
					],
					[
						'attribute' => 'usrMobile',
						'format' => 'raw',
						'value' => function() use ($model) {
							$ret = [];

							$ret[] = "<div class='float-end'>";
							if (empty($model->usrMobile)) {
								$ret[] = Html::a(Yii::t('aaa', 'Set Mobile'), ['mobile-change'], [
									'class' => ['btn', 'btn-sm', 'btn-outline-primary'],
									'modal' => true,
								]);
							} else {
								$ret[] = Html::a(Yii::t('aaa', 'Change Mobile'), ['mobile-change'], [
									'class' => ['btn', 'btn-sm', 'btn-outline-primary'],
									'modal' => true,
								]);
							}
							$ret[] = "</div>";
							$ret[] = Html::div($model->usrMobile, ['class' => ['dir-ltr', 'text-start']]);
							$ret[] = "<div class='clearfix'></div>";

							return implode(' ', $ret);
						},
					],
					[
						'attribute' => 'usrMobileApprovedAt',
						'format' => 'raw',
						'value' => function() use ($model) {
							$ret = [];

							$ret[] = "<div class='float-end'>";
							if ((empty($model->usrMobile) == false) && empty($model->usrMobileApprovedAt)) {
								$ret[] = Html::confirmButton(Yii::t('aaa', 'Resend Mobile Approval'), ['resend-mobile-approval'],
								Yii::t('aaa', 'Do you want the mobile verification code to be sent to {mobile}?', ['mobile' => Html::span($model->usrMobile, ['class' => ['d-inline-block', 'dir-ltr']])]),
								[
									'class' => ['btn', 'btn-sm', 'btn-outline-primary'],
									'ajax' => 'post',
								]);
								$ret[] = Html::a(Yii::t('aaa', 'Approve Mobile'), ['approve-code', 'kt' => GeneralHelper::PHRASETYPE_MOBILE], [
									'class' => ['btn', 'btn-sm', 'btn-outline-primary'],
									'modal' => true,
									'id' => 'mobile-approve-link',
								]);
							}
							$ret[] = "</div>";
							$ret[] = Yii::$app->formatter->asJalaliWithTime($model->usrMobileApprovedAt, 'تایید نشده');
							$ret[] = "<div class='clearfix'></div>";

							return implode(' ', $ret);
						},
					],
					[
						'attribute' => 'usrPassword',
						'format' => 'raw',
						'value' => function() use ($model) {
							$ret = [];

							$ret[] = "<div class='float-end'>";
							if ($model->hasPassword)
								$caption = Yii::t('aaa', 'Change Password');
							else
								$caption = Yii::t('aaa', 'Set Password');

							$ret[] = Html::a($caption, ['auth/password-change'], [
								'class' => ['btn', 'btn-sm', 'btn-outline-primary'],
								'modal' => true,
							]);
							// } else {
							// 	$ret[] = Html::a(Yii::t('aaa', 'Set Password'), ['auth/password-set'], [
							// 		'class' => ['btn', 'btn-sm', 'btn-outline-primary'],
							// 		'modal' => true,
							// 	]);

							$ret[] = "</div>";

							$ret[] = Html::div($model->hasPassword ? 'دارد' : 'ندارد');

							$ret[] = "<div class='clearfix'></div>";

							return implode(' ', $ret);

						},

					],
				];

				// if ($model->hasPassword)
					$columns[] = 'usrPasswordCreatedAt:jalaliWithTime';

				echo DetailView::widget([
					'model' => $model,
					'enableEditMode' => false,
					'cols' => 2,
					'isVertical' => false,
					'attributes' => $columns,
				]);
			?>

			<div class='card border-default mb-3'>
				<div class='card-header'>
					<div class="float-end"></div>
					<div class='card-title'><?= Yii::t('aaa', 'Two Factor Authentication') ?></div>
					<div class="clearfix"></div>
				</div>
				<div class='card-body'>
					<?php
						$twoFaRows = [
							[ 'key' => enuTwoFAType::SSID, ],
							[ 'key' => enuTwoFAType::BirthCertID, ],
							[ 'key' => enuTwoFAType::BirthDate, ],
							[ 'key' => enuTwoFAType::SMSOTP, ],
							// [ 'key' => enuTwoFAType::GoogleAuth, ],
							// [ 'key' => enuTwoFAType::MSAuth, ],
						];

						$config = [
							'allModels' => $twoFaRows,
						];
						$dataProvider = new ArrayDataProvider($config);
						$dataProvider->setModels($twoFaRows);

						$columns = [
							[
								'attribute' => 'key',
								'label' => 'نوع',
								'value' => function($twofamodel) use ($model) {
									return enuTwoFAType::getLabel($twofamodel['key']);
								},
							],
							[
								'attribute' => 'status',
								'label' => 'وضعیت',
								'format' => 'raw',
								'value' => function($twofamodel) use ($model) {
									if (isset($model->usr2FA[$twofamodel['key']])) {
										return Yii::$app->formatter->asBoolean(true) . ' فعال';
									} else {
										return Yii::$app->formatter->asBoolean(false) . ' تنظیم نشده';
									}
								},
							],
							[
								'class' => \shopack\base\frontend\common\widgets\ActionColumn::class,
								'header' => Yii::t('app', 'Actions'),
								'template' => '{active-2fa}{inactive-2fa}',
								'updateOptions' => [
									'modal' => true,
								],
								'buttons' => [
									'active-2fa' => function ($url, $twofamodel) use ($model) {
										return Html::createButton(Yii::t('aaa', 'Active'), [
											'active-2fa',
											'type' => $twofamodel['key'],
										], [
											'btn' => 'success',
											'modal' => true,
											'title' => Yii::t('aaa', 'Activate') . ' ' . enuTwoFAType::getLabel($twofamodel['key'])
										]);
									},
									'inactive-2fa' => function ($url, $twofamodel) use ($model) {
										return Html::confirmButton(Yii::t('aaa', 'Inactive'), [
											'inactive-2fa',
											'type' => $twofamodel['key'],
										],
										Yii::t('aaa', 'Are you sure you want to disable authentication method "{method}"?', ['method' => enuTwoFAType::getLabel($twofamodel['key'])]),
										[
											'btn' => 'danger',
											'ajax' => 'post',
										]);
									},
								],

								'visibleButtons' => [
									'active-2fa' => function ($twofamodel) use ($model) {
										return (isset($model->usr2FA[$twofamodel['key']]) == false);
									},
									'inactive-2fa' => function ($twofamodel) use ($model) {
										return isset($model->usr2FA[$twofamodel['key']]);
									},
								],
							],
						];

						echo GridView::widget([
							// 'id' => StringHelper::generateRandomId(),
							'dataProvider' => $dataProvider,
							// 'filterModel' => $searchModel,
							'columns' => $columns,
						]);
					?>
					</table>

					<?php
						if (empty($model->usr2FA)) {
							// echo Html::div('تنظیم نشده است', ['class' => 'text-center']);
						} else {

						}
					?>
				</div>
			</div>

			<?php
				$tabs->endTabPage();
			?>

			<?php
				// $tabs->beginTabPage(Yii::t('aaa', 'Privileges'), 'privileges');
				// echo DetailView::widget([
				// 	'model' => $model,
				// 	'enableEditMode' => false,
				// 	'attributes' => [
				// 		[
				// 			'attribute' => 'usrRoleID',
				// 			'value' => $model->usrRoleID ? $model->role->rolName : null,
				// 		],
				// 		[
				// 			'attribute' => 'usrPrivs',
				// 			'visible' => $model->canViewColumn('usrPrivs'),
				// 			'value' => Json::encode($model->usrPrivs),
				// 		],
				// 	],
				// ]);
				// $tabs->endTabPage();
			?>

			<?php /* $tabs->newAjaxTabPage(Yii::t('aaa', 'Deleted Accounts'), [
          '/aaa/profile/deleted-accounts'
        ],
        'accounts'
      ); */ ?>

      <?php $tabs->end(); ?>
    </div>
	</div>
</div>
