<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\helpers\Json;
use shopack\base\frontend\widgets\tabs\Tabs;
use shopack\base\frontend\helpers\Html;
use shopack\base\frontend\widgets\DetailView;
use shopack\aaa\common\enums\enuUserStatus;
use shopack\aaa\common\enums\enuGender;

$this->title = Yii::t('aaa', 'My Profile');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="user-view w-100">
	<div class='card border-0'>

		<div class='card-tabs'>
			<?php $tabs = Tabs::begin($this); ?>

			<?php $tabs->beginTabPage(Yii::t('aaa', 'My Details'), 'details'); ?>
				<div class='row'>
					<div class='col-sm-9'>
						<div class='card border-default'>
							<div class='card-header bg-default'>
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
										'isVertical' => true,
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
											'usrSSID',
											'usrFirstName',
											'usrFirstName_en',
											'usrLastName',
											'usrLastName_en',
											'usrBirthDate:jalali',
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
											'usrZipCode',
											'usrHomeAddress',
										],
									]);
								?>
							</div>
						</div>
					</div>

					<div class='col-sm-3'>
						<div class='card border-default mb-3'>
							<div class='card-header bg-default'>
								<div class="float-end">
								</div>
								<div class='card-title'><?= Yii::t('aaa', 'Account') ?></div>
								<div class="clearfix"></div>
							</div>
							<div class='card-body text-center'>
								<?= $model->canDelete() ? Html::deleteButton(Yii::t('aaa', 'Delete Account'), ['id' => $model->usrID]) : '' ?>
							</div>
						</div>

						<div class='card border-default'>
							<div class='card-header bg-default'>
								<div class="float-end">
								</div>
								<div class='card-title'><?= Yii::t('aaa', 'Image') ?></div>
								<div class="clearfix"></div>
							</div>
							<div class='card-body text-center'>
								usrImage
							</div>
						</div>

					</div>
				</div>
			<?php $tabs->endTabPage(); ?>

			<?php
				$tabs->beginTabPage(Yii::t('aaa', 'Login'), [
					'login',
					'approval-request',
				]);

				$tabs2 = Tabs::begin($this, [
					'pluginOptions' => [
						'id' => 'tabs_login',
						// 'position' => \kartik\tabs\TabsX::POS_LEFT,
						// 'bordered' => true,
					],
				]);

				//use runaction for proper loading grid expand column
				$tabs2->beginTabPage(Yii::t('aaa', 'Login Information'), 'login');
				$columns = [
					[
						'attribute' => 'usrEmail',
						'format' => 'raw',
						'value' => function() use ($model) {
							$ret = [];

							$ret[] = "<div class='float-end'>";
							if (empty($model->usrEmail)) {
								$ret[] = Html::a(Yii::t('aaa', 'Set Email'), ['email-set'], [
									'class' => ['btn', 'btn-sm', 'btn-outline-primary'],
								]);
							} else {
								$ret[] = Html::a(Yii::t('aaa', 'Change Email'), ['email-change'], [
									'class' => ['btn', 'btn-sm', 'btn-outline-primary'],
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
							if (empty($model->usrEmailApprovedAt)) {
								$ret[] = Html::a(Yii::t('aaa', 'Resend Email Approval'), ['resend-email-approval'], [
									'class' => ['btn', 'btn-sm', 'btn-outline-primary'],
								]);
							}
							$ret[] = "</div>";
							$ret[] = Yii::$app->formatter->asJalaliWithTime($model->usrEmailApprovedAt);
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
								$ret[] = Html::a(Yii::t('aaa', 'Set Mobile'), ['mobile-set'], [
									'class' => ['btn', 'btn-sm', 'btn-outline-primary'],
								]);
							} else {
								$ret[] = Html::a(Yii::t('aaa', 'Change Mobile'), ['mobile-change'], [
									'class' => ['btn', 'btn-sm', 'btn-outline-primary'],
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
							if (empty($model->usrMobileApprovedAt)) {
								$ret[] = Html::a(Yii::t('aaa', 'Approve Mobile'), ['mobile-approve'], [
									'class' => ['btn', 'btn-sm', 'btn-outline-primary'],
								]);
							}
							$ret[] = "</div>";
							$ret[] = Yii::$app->formatter->asJalaliWithTime($model->usrMobileApprovedAt);
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
							if ($model->hasPassword) {
								$ret[] = Html::a(Yii::t('aaa', 'Change Password'), ['password-change'], [
									'class' => ['btn', 'btn-sm', 'btn-outline-primary'],
								]);
							} else {
								$ret[] = Html::a(Yii::t('aaa', 'Set Password'), ['password-set'], [
									'class' => ['btn', 'btn-sm', 'btn-outline-primary'],
								]);
							}
							$ret[] = "</div>";

							$ret[] = Html::div($model->hasPassword ? 'دارد' : 'ندارد');

							$ret[] = "<div class='clearfix'></div>";

							return implode(' ', $ret);

						},

					],
				];

				if ($model->hasPassword)
					$columns[] = 'usrPasswordCreatedAt:jalaliWithTime';

				echo DetailView::widget([
					'model' => $model,
					'enableEditMode' => false,
					'attributes' => $columns,
				]);
				$tabs2->endTabPage();

				$tabs2->newAjaxTabPage(Yii::t('aaa', 'Approval Requests'), [
						'/aaa/approval-request/index',
						'aprUserID' => $model->usrID,
					],
					'approval-request'
				);

				$tabs2->end();

				$tabs->endTabPage();
			?>

			<?php $tabs->beginTabPage(Yii::t('aaa', 'Privileges'), 'privileges'); ?>
			<?php
				echo DetailView::widget([
					'model' => $model,
					'enableEditMode' => false,
					'attributes' => [
						[
							'attribute' => 'usrRoleID',
							'value' => $model->usrRoleID ? $model->role->rolName : null,
						],
						[
							'attribute' => 'usrPrivs',
							'visible' => $model->canViewColumn('usrPrivs'),
							'value' => Json::encode($model->usrPrivs),
						],
					],
				]);
			?>
			<?php $tabs->endTabPage(); ?>

			<?php $tabs->newAjaxTabPage(Yii::t('aaa', 'Deleted Accounts'), [
          '/aaa/profile/deleted-accounts'
        ],
        'accounts'
      ); ?>

      <?php $tabs->end(); ?>
    </div>
	</div>
</div>
