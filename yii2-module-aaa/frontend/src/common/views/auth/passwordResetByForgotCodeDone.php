<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\frontend\helpers\Html;
use shopack\base\frontend\widgets\ActiveForm;
use shopack\base\frontend\widgets\FormBuilder;

// $this->title = Yii::t('aaa', 'Reset Password Done');
// $this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-index w-100 min-h-100 d-grid" style="align-content: center;">
  <div class="jumbotron text-center bg-transparent">
		<p>&nbsp;</p>
		<p>کاربر محترم، رمز شما تغییر کرد.</p>
		<p><?= Html::a('بازگشت', ['/'], [
			'class' => 'btn btn-sm btn-outline-success',
		]) ?></p>
	</div>
</div>
