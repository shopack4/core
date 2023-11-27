<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\frontend\common\widgets\form;

use Yii;
use shopack\base\common\helpers\ArrayHelper;
use shopack\base\frontend\common\widgets\Select2;
use shopack\base\frontend\common\widgets\FormBuilder;
use shopack\aaa\frontend\common\models\GeoCountryModel;

class GeoCountryChooseFormField
{
	public static function field(
		$view,
		$model,
		$attribute,
		$allowClear = true,
		$multiSelect = false
	) {
		// if (!empty($model->$attribute)) {
		// 	if ($multiSelect) {
		// 		$models = GeoCountryModel::findAll($model->$attribute);
		// 		$vals = [];
		// 		$memberDesc = [];
		// 		foreach ($models as $item) {
		// 			$vals[] = $item->mbrUserID;
		// 			$memberDesc[] = $item->displayName('{fn} {ln}');
		// 		}
		// 		$model->$attribute = $vals;
		// 		// $memberDesc = implode('، ', $memberDesc);
		// 	} else {
		// 		$geoCountryModel = GeoCountryModel::findOne($model->$attribute);
		// 		$vals = $model->$attribute;
		// 		$memberDesc = $geoCountryModel->displayName();
		// 	}
		// } else {
		// 	$vals = $model->$attribute;
		// 	$memberDesc = null;
		// }

		return [
			$attribute,
			'type' => FormBuilder::FIELD_WIDGET,
			'widget' => Select2::class,
			'widgetOptions' => [
				// 'value' => $vals,
				// 'initValueText' => $memberDesc,
				'data' => ArrayHelper::map(GeoCountryModel::find()->asArray()->noLimit()->all(), 'cntrID', 'cntrName'),
				'options' => [
					'placeholder' => Yii::t('app', '-- Choose --'),
					'dir' => 'rtl',
					'multiple' => $multiSelect,
				],
				'pluginOptions' => [
					'allowClear' => $allowClear,
				],
			],
		];
	}

}
