<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\common\widgets;

use Yii;
use yii\web\JsExpression;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\common\helpers\Json;
use shopack\base\common\helpers\ArrayHelper;
use yii\base\InvalidArgumentException;
use yii\widgets\InputWidget;
use shopack\base\common\helpers\JsonSchema;

class JsonTableGrid extends InputWidget
{
	public function run()
	{
		$view = $this->getView();

		echo $this->renderWidget() . "\n";
	}

	protected function renderWidget()
	{
		$contents = [];

		$value = ($this->hasModel() ? Html::getAttributeValue($this->model, $this->attribute) : $this->value);

		if ($this->hasModel()) {
			$columnsInfo = $this->model->columnsInfo();
			$jsonSchema_fields = $columnsInfo[$this->attribute]['jsonSchema']['fields'] ?? null;
		}

		if (empty($value) == false) {
			// format value according to dateFormat
			try {
				//BUG: converted to persian digit
				//$value = Yii::$app->formatter->asDate($value, $this->dateFormat);
			} catch(InvalidArgumentException $exp) { }
		}

		//begin table
		$contents[] = Html::beginTag('table', [
			'class' => ['table', 'table-bordered', 'w-100'],
		]);

		//header row
		$contents[] = Html::beginTag('thead');
		$contents[] = Html::beginTag('tr');
		foreach ($jsonSchema_fields as $field) {
			if (isset($field['label'])) {
				if (is_array($field['label'])) {
					$cat = array_shift($field['label']);
					$msg = array_shift($field['label']);
					$label = Yii::t($cat, $msg, $field['label']);
				} else
					$label = Yii::t('app', $field['label']);
			} else
				$label = $field[0];

			$contents[] = Html::tag('th', $label);
		}
		$contents[] = Html::tag('th', "<a href='#' onclick='javascipt::addJSRow(\'{$this->options['id']}\', \'__row__\', \'t\')'>(+)</a>");
		$contents[] = Html::endTag('tr');
		$contents[] = Html::endTag('thead');

		//rows
		$fnRenderDataRow = function($data, $asTemplate = false) use($jsonSchema_fields, &$contents) {

			$rowName = $this->options['id'] . '[__row__]['
				. ($data !== null ? $data['id'] : ($asTemplate ? 't' : 'n1'))
				. ']';
			$rowId = Html::getInputIdByName($rowName);

			$contents[] = Html::beginTag('tr', [
				'id' => $rowId,
				'style' => [
					'display' => ($asTemplate ? 'none' : 'table-row'),
				],
			]);

			foreach ($jsonSchema_fields as $field) {
				$fieldName = $this->options['id'] . "[{$field[0]}]["
					. ($data !== null ? $data['id'] : ($asTemplate ? 't' : 'n1')) //new 1
					. "]";
				$fieldId = Html::getInputIdByName($fieldName);

				$formField = null;

				if (isset($field['pk'])) {
					$formField = Html::tag('div', $data['id'] ?? '[جدید]');
				} else {
					if (isset($field['type'])) {
						switch ($field['type']) {
							case JsonSchema::TYPE_int:
							case jsonSchema::TYPE_string:
								$formField = Html::textInput($fieldName,
									null,
									[
										'id' => $fieldId,
										'class' => [
											'form-control', 'w-100',
										],
									]);
								break;

							case jsonSchema::TYPE_boolean:
								$formField = Html::checkbox($fieldName,
									null,
									[
										'id' => $fieldId,
									]);
								break;
						}
					}
				}

				$contents[] = Html::beginTag('td');
				$contents[] = $formField;
				$contents[] = Html::endTag('td');
			}

			$contents[] = Html::tag('td', $data !== null || $asTemplate
				? "<a href='#' id='remove-{$rowId}' onclick='javascipt::removeJSRow()'>(-)</a>"
				: '');

			$contents[] = Html::endTag('tr');
		};

		$contents[] = Html::beginTag('tbody');

		//data rows
		if (empty($value) == false) {
			foreach ($value as $row) {
				$fnRenderDataRow($row, false);
			}
		}

		//empty data row
		$fnRenderDataRow(null, false);

		//hidden data row template
		$fnRenderDataRow(null, true);

		$contents[] = Html::endTag('tbody');

		//end table
		$contents[] = Html::endTag('table');

		//render
		return implode("\n", $contents);
	}

}
