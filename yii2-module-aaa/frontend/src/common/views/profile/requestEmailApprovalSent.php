<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\frontend\common\helpers\Html;
?>

<div class="site-index w-100 min-h-100 d-grid" style="align-content: center;">
  <div class="jumbotron text-center bg-transparent">
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>کاربر محترم، ایمیل تایید عملیات به آدرس زیر ارسال شد:</p>
		<p><?= Html::encode($email) ?></p>
	</div>
</div>
