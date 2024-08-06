<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\common\widgets;

use Yii;
use yii\web\JsExpression;

class DepDrop extends \kartik\widgets\DepDrop
{
	public function init()
	{
		$bodyParams = Yii::$app->request->getBodyParams();

		$isModal = Yii::$app->request->isAjax;
		if ($isModal) {
			if (isset($_GET['ajax_popupSize']))
				$ajax_popupSize = $_GET['ajax_popupSize'];
			else if (isset($bodyParams['ajax_popupSize']))
				$ajax_popupSize = $bodyParams['ajax_popupSize'];
			else
				$ajax_popupSize = 'sm';

			$this->select2Options = array_replace_recursive($this->select2Options, [
				'pluginOptions' => [
					'dropdownParent' => new JsExpression("$('#modal-{$ajax_popupSize}')"),
				],
			]);
		}

		parent::init();
	}

}
