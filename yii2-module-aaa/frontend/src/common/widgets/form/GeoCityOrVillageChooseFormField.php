<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\widgets\form;

use Yii;
use yii\web\JsExpression;
use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\ArrayHelper;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\DepDrop;
use shopack\base\frontend\common\widgets\FormBuilder;
use shopack\base\frontend\common\widgets\Select2;
use shopack\aaa\frontend\common\models\GeoCityOrVillageModel;

class GeoCityOrVillageChooseFormField
{
	public static function field(
		$view,
		$model,
		$attribute,
		$allowClear = true,
		$multiSelect = false,
		$dependes = null,
		$options = null
	) {
		if (strpos($attribute, '[') !== false) {
			$parts = explode('[', $attribute, 2);

			$attr = $parts[0];
			$key = str_replace("]", "", str_replace("[", "", str_replace("][", ".", $parts[1])));

			$attrValue = ArrayHelper::getValue($model->$attr, $key);
		} else {
			$attrValue = $model->$attribute ?? null;
		}

		if (empty($dependes)) {
			$formatJs =<<<JS
var formatGeoCity = function(item)
{
	if (item.loading)
		return 'در حال جستجو...'; //item.text;
	return '<div style="overflow:hidden;">' + item.title + '</div>';
};
var formatGeoCitySelection = function(item)
{
	if (item.text)
		return item.text;
	return item.title;
}
JS;
			$view->registerJs($formatJs, \yii\web\View::POS_HEAD);

			// script to parse the results into the format expected by Select2
			$resultsJs =<<<JS
function(data, params)
{
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

			if (empty($attrValue) == false) {
				if ($multiSelect) {
					$models = GeoCityOrVillageModel::findAll($model->$attribute);
					$vals = [];
					$desc = [];
					foreach ($models as $item) {
						$vals[] = $item->ctvID;
						$desc[] = $item->ctvName;
					}
					$attrValue = $vals;
					// $desc = implode('، ', $desc);
				} else {
					$geoCityOrVillageModel = GeoCityOrVillageModel::findOne($attrValue);
					$vals = $attrValue;
					$desc = $geoCityOrVillageModel->ctvName;
				}
			} else {
				$vals = $attrValue;
				$desc = null;
			}

			$field = [
				$attribute,
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => Select2::class,
				'widgetOptions' => [
					'value' => $vals,
					'initValueText' => $desc,
					'pluginOptions' => [
						'allowClear' => $allowClear,
						'minimumInputLength' => 3,
						'ajax' => [
							'url' => Url::to(['/aaa/geo-city-or-village/select2-list']),
							'dataType' => 'json',
							'delay' => 50,
							'data' => new JsExpression('function(params) { return {q:params.term, page:params.page}; }'),
							'processResults' => new JsExpression($resultsJs),
							'cache' => true,
						],
						'escapeMarkup' => new JsExpression('function(markup) { return markup; }'),
						'templateResult' => new JsExpression('formatGeoCity'),
						'templateSelection' => new JsExpression('formatGeoCitySelection'),
					],
					'options' => [
						'placeholder' => Yii::t('app', '-- Search (*** for all) --'),
						'dir' => 'rtl',
						'multiple' => $multiSelect,
					],
				],
			];

		} else { //dep drop
			$deps = [];
			foreach ((array)$dependes as $d) {
				$deps[] = Html::getInputId($model, $d);
			}

			$pluginOptions = [
				'initialize' => true,
				// 'initDepends' => ["{$formName}-usrcountryid"],
				'url' => Url::to(['/aaa/geo-city-or-village/depdrop-list', 'sel' => $attrValue]),
				'loadingText' => Yii::t('app', 'Loading...'),
				'depends' => $deps,
			];

			$field = [
				$attribute,
				'type' => FormBuilder::FIELD_WIDGET,
				'widget' => DepDrop::class,
				'widgetOptions' => [
					'type' => DepDrop::TYPE_SELECT2,
					// 'value' => $vals,
					// 'initValueText' => $desc,
					'select2Options' => [
						'pluginOptions' => [
							'allowClear' => $allowClear,
						],
					],
					'pluginOptions' => $pluginOptions,
					'options' => [
						'placeholder' => Yii::t('app', '-- Choose --'),
						'dir' => 'rtl',
						'multiple' => $multiSelect,
					],
				],
			];
		}

		return array_replace_recursive($field, $options ?? []);
	}

}
