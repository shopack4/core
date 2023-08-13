<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

use shopack\base\common\helpers\StringHelper;
use shopack\base\frontend\helpers\Html;
use shopack\aaa\common\enums\enuVoucherStatus;

$this->title = Yii::t('aaa', 'Checkout');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="checkout-done w-100">
  <div class='card border-default'>
    <div class='card-body'>
      <p class='text-center'>
				<?php
					if ($voucher->vchStatus == enuVoucherStatus::Finished)
						echo Yii::t('aaa', 'Basket checked out');
					else if ($voucher->vchStatus == enuVoucherStatus::Error)
						echo Yii::t('aaa', 'Error in checkout');
					else
						echo Yii::t('aaa', 'Error in checkout ({0})', [enuVoucherStatus::getLabel($voucher->vchStatus)]);
				?>
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
