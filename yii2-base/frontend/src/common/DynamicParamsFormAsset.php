<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\common;

use shopack\base\frontend\common\widgets\datetime\DatepickerAsset;

class DynamicParamsFormAsset extends \yii\web\AssetBundle
{
	public $depends = [
		DatepickerAsset::class,
	];

	public function init()
	{
		$this->sourcePath = dirname(__FILE__) . '/assets/';
		parent::init();

		$this->js[] = 'js/dynaparamsform.js';
	}

}
