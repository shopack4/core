<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\common\widgets;

use Yii;
use yii\base\InvalidArgumentException;
use yii\widgets\InputWidget;
use shopack\base\common\helpers\JsonSchema;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\frontend\common\helpers\Html;

class JsonTableGrid extends InputWidget
{
	public function run()
	{
		$view = $this->getView();

		$this->registerAssets($view);

		echo $this->renderWidget() . "\n";
	}

	protected function registerAssets($view)
	{
		$js =<<<JS
function addJSRow()
{
	var sender = $(this);
	var jstable = sender.closest('table');
	var tableid = jstable.data('key');

	var lastrowidx = 0;
	$('[id^=' + tableid + '-__row__-n]').each(function() {
    var el = $(this);
    var parts = el.attr('id').split('-');
		var id = parseInt(parts.pop().substring(1));
		if (id > lastrowidx)
			lastrowidx = id;
  });
	++lastrowidx;

	var newrow = $('#' + tableid + '-__row__-t').clone();
	newrow.attr('id', tableid + '-__row__-n' + lastrowidx);

	newrow.find('[id^=' + tableid + '-]').each(function() {
    var el = $(this);

		var id = el.attr('id');
		if (id.includes('-t') == false)
			return;

		el.attr('id', id.replace('-t-', '-n' + lastrowidx + '-'));
		el.attr('name', el.attr('name').replace('[t]', '[n' + lastrowidx + ']'));
  });

	jstable.append(newrow);
	newrow.show();
	newrow.find('#remjsrow').on('click', removeJSRow);
}

function removeJSRow()
{
	var sender = $(this);
	var row = sender.closest('tr');
	row.remove();
}
JS;

		$view->registerJs($js, \yii\web\View::POS_END);

		$js =<<<JS
$('#addjsrow').each(function() { $(this).on('click', addJSRow) });
$('#remjsrow').each(function() { $(this).on('click', removeJSRow) });
JS;

		$view->registerJs($js, \yii\web\View::POS_READY);
	}

	protected function renderWidget()
	{
		$contents = [];

		$value = ($this->hasModel() ? Html::getAttributeValue($this->model, $this->attribute) : $this->value);

		if ($this->hasModel()) {
			$columnsInfo = $this->model->getColumnsInfo();
			$jsonSchema_fields = $columnsInfo[$this->attribute][enuColumnInfo::jsonSchema]['fields'] ?? null;
			$widgetName = Html::getInputName($this->model, $this->attribute);
		} else {
			$widgetName = $this->options['id'];
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
			'id' => $this->options['id'] . '-table',
			'data' => [
				'key' => $this->options['id'],
			],
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
		$contents[] = Html::tag('th', "<a id='addjsrow' href='#' data-table-id='{$this->options['id']}'>[+]</a>");
		$contents[] = Html::endTag('tr');
		$contents[] = Html::endTag('thead');

		//rows
		$fnRenderDataRow = function($dataValues, $asTemplate = false)
			use($jsonSchema_fields, &$contents, $widgetName)
		{
			$rowKey = ($dataValues !== null ? $dataValues['id'] : ($asTemplate ? 't' : 'n1'));
			$rowName = $widgetName . '[__row__][' . $rowKey . ']';
			$rowId = Html::getInputIdByName($rowName);

			$contents[] = Html::beginTag('tr', [
				'id' => $rowId,
				'style' => [
					'display' => ($asTemplate ? 'none' : 'table-row'),
				],
			]);

			foreach ($jsonSchema_fields as $field) {
				$fieldName = $widgetName . '['
					. ($dataValues !== null ? $dataValues['id'] : ($asTemplate ? 't' : 'n1')) //new 1
					. ']['
					. $field[0] . ']';

				$fieldId = Html::getInputIdByName($fieldName);

				$formField = null;

				if (isset($field['pk'])) {
					$formField = Html::tag('div', $dataValues['id'] ?? '[جدید]');

					if (isset($dataValues['id'])) {
						$formField .= Html::hiddenInput($fieldName, $dataValues['id'], [
							'id' => $fieldId,
						]);
					}

				} else {
					if (isset($field['type'])) {
						switch ($field['type']) {
							case JsonSchema::TYPE_int:
							case jsonSchema::TYPE_string:
								$formField = Html::textInput($fieldName,
									$dataValues[$field[0]] ?? null,
									[
										'id' => $fieldId,
										'class' => [
											'form-control', 'w-100',
										],
									]);
								break;

							case jsonSchema::TYPE_select:
								$selectData = $field['data'];
								foreach ($selectData as $k => &$v) {
									if (is_array($v))
										$v = Yii::t(array_shift($v), array_shift($v), $v);
								}
								$formField = Html::dropDownList($fieldName,
									$dataValues[$field[0]] ?? null,
									$selectData,
									[
										'id' => $fieldId,
										'class' => [
											'form-control', 'w-100',
										],
									]);
								break;

							case jsonSchema::TYPE_boolean:
								$formField = Html::checkbox($fieldName,
									$dataValues[$field[0]] ?? false,
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

			$contents[] = Html::tag('td', $dataValues !== null || $asTemplate
				? "<a id='remjsrow' href='#'>[-]</a>"
				// ? "<a id='remjsrow' href='#' data-row-id='{$rowId}'>[-]</a>"
				: '');

			$contents[] = Html::endTag('tr');
		};

		$contents[] = Html::beginTag('tbody');

		//data rows
		if (empty($value['rows']) == false) {
			foreach ($value['rows'] as $row) {
				$fnRenderDataRow($row, false);
			}
		}

		//empty new data row
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
