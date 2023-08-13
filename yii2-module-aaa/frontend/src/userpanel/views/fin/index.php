<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\frontend\helpers\Html;
use shopack\base\frontend\widgets\DetailView;
use shopack\base\frontend\widgets\tabs\Tabs;
use shopack\aaa\common\enums\enuGender;
use shopack\aaa\common\enums\enuUserStatus;

$this->title = Yii::t('aaa', 'Financial');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="fin-index w-100">
	<div class='card border-0'>
		<div class='card-tabs'>
			<?php $tabs = Tabs::begin($this); ?>

			<?php $tabs->newAjaxTabPage(Yii::t('aaa', 'Wallets'), [
          '/aaa/wallet',
					// 'justForMe' => true,
        ],
        'wallets'
			); ?>

			<?php $tabs->newAjaxTabPage(Yii::t('aaa', 'Wallet Transactions'), [
          '/aaa/wallet-transaction',
					// 'justForMe' => true,
        ],
        'wallet-transactions'
			); ?>

			<?php $tabs->newAjaxTabPage(Yii::t('aaa', 'Orders'), [
					'/aaa/order',
					// 'justForMe' => true,
				],
				'orders'
			); ?>

			<?php /*$tabs->newAjaxTabPage(Yii::t('aaa', 'Vouchers'), [
          '/aaa/voucher',
					// 'justForMe' => true,
        ],
        'vouchers'
			);*/ ?>

			<?php $tabs->newAjaxTabPage(Yii::t('aaa', 'Online Payments'), [
          '/aaa/online-payment',
					// 'justForMe' => true,
        ],
        'online-payments'
			); ?>

			<?php $tabs->newAjaxTabPage(Yii::t('aaa', 'Offline Payments'), [
          '/aaa/offline-payment',
					// 'justForMe' => true,
        ],
        'offline-payments'
			); ?>

      <?php $tabs->end(); ?>
    </div>
	</div>
</div>
