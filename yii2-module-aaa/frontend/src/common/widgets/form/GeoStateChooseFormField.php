<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\widgets\form;

use Yii;
use shopack\base\common\helpers\Url;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\frontend\common\widgets\DepDrop;
use shopack\base\frontend\common\widgets\FormBuilder;

class GeoStateChooseFormField
{
	public static function field(
		$view,
		$model,
		$attribute,
		$allowClear = true,
		$multiSelect = false,
		$dependes = null
	) {
// 		$formatJs =<<<JS
// var formatGeoState = function(item)
// {
// 	if (item.loading)
// 		return 'در حال جستجو...'; //item.text;
// 	return '<div style="overflow:hidden;">' + item.name + '</div>';
// };
// var formatGeoStateSelection = function(item)
// {
// 	if (item.text)
// 		return item.text;
// 	return item.name;
// }
// JS;
// 		$view->registerJs($formatJs, \yii\web\View::POS_HEAD);

// 		// script to parse the results into the format expected by Select2
// 		$resultsJs =<<<JS
// function(data, params)
// {
// 	if ((data == null) || (params == null))
// 		return;

// 	// params.page = params.page || 1;
// 	if (params.page == null)
// 		params.page = 0;
// 	return {
// 		results: data.items,
// 		pagination: {
// 			more: ((params.page + 1) * 20) < data.total_count
// 		}
// 	};
// }
// JS;

		// if (!empty($model->$attribute)) {
		// 	if ($multiSelect) {
		// 		$models = GeoStateModel::findAll($model->$attribute);
		// 		$vals = [];
		// 		$memberDesc = [];
		// 		foreach ($models as $item) {
		// 			$vals[] = $item->mbrUserID;
		// 			$memberDesc[] = $item->displayName('{fn} {ln}');
		// 		}
		// 		$model->$attribute = $vals;
		// 		// $memberDesc = implode('، ', $memberDesc);
		// 	} else {
		// 		$geoStateModel = GeoStateModel::findOne($model->$attribute);
		// 		$vals = $model->$attribute;
		// 		$memberDesc = $geoStateModel->displayName();
		// 	}
		// } else {
		// 	$vals = $model->$attribute;
		// 	$memberDesc = null;
		// }

		$pluginOptions = [
			'initialize' => true,
			// 'initDepends' => ["{$formName}-usrcountryid"],
			'url' => Url::to(['/aaa/geo-state/depdrop-list', 'sel' => $model->$attribute]),
			'loadingText' => Yii::t('app', 'Loading...'),
		];

		if (empty($dependes) == false) {
			$deps = [];
			foreach ((array)$dependes as $d) {
				$deps[] = Html::getInputId($model, $d);
			}

			$pluginOptions['depends'] = $deps;
		}

		return [
			$attribute,
			'type' => FormBuilder::FIELD_WIDGET,
			'widget' => DepDrop::class,
			'widgetOptions' => [
				'type' => DepDrop::TYPE_SELECT2,
				// 'value' => $vals,
				// 'initValueText' => $memberDesc,
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

}
