<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\aaa\common\enums\enuOnlinePaymentStatus;
use shopack\base\frontend\helpers\Html;
use shopack\base\common\helpers\StringHelper;
use shopack\aaa\frontend\common\models\WalletModel;

$this->title = Yii::t('aaa', 'Wallet Increase');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="wallet-increase-done w-100">
  <div class='card border-default'>
    <div class='card-body'>
      <p class='text-center'>
				<?php
					if ($onlinePaymentModel->onpStatus == enuOnlinePaymentStatus::Paid)
						echo Yii::t('aaa', 'Wallet incresed {0} tomans', [
							Yii::$app->formatter->asDecimal($onlinePaymentModel->onpAmount),
						]);
					else if ($onlinePaymentModel->onpStatus == enuOnlinePaymentStatus::Error)
						echo Yii::t('aaa', 'Error in online payment');
					else
						echo Yii::t('aaa', 'Error in online payment ({0})', [enuOnlinePaymentStatus::getLabel($onlinePaymentModel->onpStatus)]);
				?>
			</p>
			<p class='text-center'>
				<?= Html::a(Yii::t('aaa', 'Return to the wallet'), [
					'view',
					'id' => $onlinePaymentModel->onpWalletID,
				]); ?>
			</p>
			<p class='text-center'>
				<?php
					if (empty($errors) == false)
						echo $errors;
				?>
			</p>
    </div>
  </div>
</div>
