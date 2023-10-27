<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\widgets\grid;

use Yii;
use yii\web\JsExpression;
use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\ArrayHelper;
use shopack\base\frontend\common\widgets\Select2;
use shopack\aaa\frontend\common\models\UserModel;

class UserDataColumn extends \kartik\grid\DataColumn
{
	protected function renderFilterCellContent()
	{
		$formatJs =<<<JS
var formatUser = function(user) {
	if (user.loading)
		return 'در حال جستجو...'; //user.text;
	return '<div style="overflow:hidden;">' + '<b>' + user.firstname + ' ' + user.lastname + '</b> - ' + user.email + '</div>';
};
var formatUserSelection = function(user) {
	if (user.text)
		return user.text;
	return user.firstname + ' ' + user.lastname + ' - ' + user.email;
}
JS;
			$this->grid->view->registerJs($formatJs, \yii\web\View::POS_HEAD);

			// script to parse the results into the format expected by Select2
			$resultsJs =<<<JS
function(data, params) {
	if ((data == null) || (params == null))
		return;

	// params.page = params.page || 1;
	if (params.page == null)
		params.page = 0;

	return {
		results: data.items,
		pagination: {
			more: ((params.page + 1) * 20) < data.total_count
		}
	};
}
JS;

		$model = $this->grid->filterModel;
		$attribute = $this->attribute;

		if (empty($model->$attribute))
			$initValueText = null;
		else {
			$userModel = UserModel::findOne($model->$attribute);
			$initValueText = $userModel->usrFirstName . ' ' . $userModel->usrLastName . ' - ' . $userModel->usrEmail;
		}

		$widgetOptions = [
			'model' => $model,
			'attribute' => $attribute,
			'initValueText' => $initValueText,
			'pluginOptions' => [
				'allowClear' => true,
				'minimumInputLength' => 2,
				'ajax' => [
					'url' => Yii::$app->getModule('aaa')->searchUserForSelect2ListUrl(),
					'dataType' => 'json',
					'delay' => 50,
					'data' => new JsExpression('function(params) { return { q:params.term, page:params.page }; }'),
					'processResults' => new JsExpression($resultsJs),
					'cache' => true,
				],
				'escapeMarkup' => new JsExpression('function(markup) { return markup; }'),
				'templateResult' => new JsExpression('formatUser'),
				'templateSelection' => new JsExpression('formatUserSelection'),
			],
			'options' => [
				'prompt' => '-- همه --',
			],
		];

		return Select2::widget($widgetOptions);
	}

}
