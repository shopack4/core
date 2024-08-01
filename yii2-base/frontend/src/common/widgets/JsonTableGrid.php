<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\common\widgets;

use Closure;
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
	var newtableid = tableid + '-__row__-n' + lastrowidx;
	newrow.attr('id', newtableid);

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
	newrow.find('[id^=remjsrow-]').attr('id', 'remjsrow-' + newtableid).on('click', removeJSRow);
}

function removeJSRow()
{
	var sender = $(this);
	console.log(sender);
	var row = sender.closest('tr');
	console.log(row);
	row.remove();
}
JS;

		$view->registerJs($js, \yii\web\View::POS_END);

		$js =<<<JS
$('[id^=addjsrow-]').each(function() { $(this).on('click', addJSRow) });
$('[id^=remjsrow-]').each(function() { $(this).on('click', removeJSRow) });
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

		$fnGetLabel = function($label) {
			if (is_array($label)) {
				$cat = array_shift($label);
				$msg = array_shift($label);
				return Yii::t($cat, $msg, $label);
			}

			return Yii::t('app', $label);
		};

		//header row
		$contents[] = Html::beginTag('thead');
		$contents[] = Html::beginTag('tr');
		foreach ($jsonSchema_fields as $field) {
			if (isset($field['label']))
				$label = $fnGetLabel($field['label']);
			else
				$label = $field[0];

			$contents[] = Html::tag('th', $label);
		}
		$contents[] = Html::tag('th', "<button id='addjsrow-{$this->options['id']}' type='button' class='btn btn-sm btn-success'><span class='fa fa-plus'></span></button>");
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
					$formField = Html::tag('div', $dataValues['id'] ?? '[جدید]', [
						'style' => ['word-break' => 'break-all'],
					]);

					if (isset($dataValues['id'])) {
						$formField .= Html::hiddenInput($fieldName, $dataValues['id'], [
							'id' => $fieldId,
						]);
					}

				} else {
					if (isset($field['type'])) {
						switch ($field['type']) {
							case JsonSchema::TYPE_number:
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
								foreach ($selectData as $k => $v) {
									if (is_array($v))
										$selectData[$k] = Yii::t(array_shift($v), array_shift($v), $v);
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
				? "<button id='remjsrow-{$rowId}' type='button' class='btn btn-sm btn-danger'><span class='fa fa-minus'></span></button>"
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

	public static function formatParamsSchemaAsTable($model, $attribute)
	{
		$value = Html::getAttributeValue($model, $attribute);
		if (empty($value['rows']))
			return null;

		$columnsInfo = $model->getColumnsInfo();
		$jsonSchema_fields = $columnsInfo[$attribute][enuColumnInfo::jsonSchema]['fields'] ?? null;
		if (empty($jsonSchema_fields))
			return null;

		//begin table
		$contents = [
			Html::beginTag('table', ['class' => ['table', 'table-bordered', 'table-striped', 'w-100']]),
		];

		$fnGetLabel = function($label) {
			if (is_array($label)) {
				$cat = array_shift($label);
				$msg = array_shift($label);
				return Yii::t($cat, $msg, $label);
			}

			return Yii::t('app', $label);
		};

		//header row
		$contents[] = Html::beginTag('thead');
		$contents[] = Html::beginTag('tr');
		foreach ($jsonSchema_fields as $field) {
			$label = $fnGetLabel($field['label'] ?? $field[0]);
			$contents[] = Html::tag('th', $label);
		}
		$contents[] = Html::endTag('tr');
		$contents[] = Html::endTag('thead');

		//data rows
		foreach ($value['rows'] as $row) {
			$contents[] = Html::beginTag('tr');

			foreach ($jsonSchema_fields as $field) {
				$contents[] = Html::beginTag('td');
				if (isset($row[$field[0]])) {
					if ($field['type'] == jsonSchema::TYPE_boolean) {
						$contents[] = Yii::$app->formatter->asBoolean($row[$field[0]]);
					} else if ($field['type'] == jsonSchema::TYPE_select) {
						$contents[] = $fnGetLabel($field['data'][$row[$field[0]]] ?? $row[$field[0]]);
					} else
						$contents[] = $row[$field[0]];
				}
				$contents[] = Html::endTag('td');
			}

			$contents[] = Html::endTag('tr');
		}

		//end table
		$contents[] = Html::endTag('table');

		//render
		return implode("\n", $contents);
	}

	public static function generateDynamicParamsForm($model, $attribute, ?Closure $fnGetTypeData = null)
	{
		$result = ['count' => 0, 'list' => []];

    if (empty($model->$attribute))
	    return $result;

		$value = Html::getAttributeValue($model, $attribute);
		if (empty($value['rows']))
			return $result;

		$columnsInfo = $model->getColumnsInfo();
		$jsonSchema_fields = $columnsInfo[$attribute][enuColumnInfo::jsonSchema]['fields'] ?? null;
		if (empty($jsonSchema_fields))
			return $result;

		foreach ($jsonSchema_fields as $field)
		{
			if (empty($field['pk']) == false)
				$pkField = $field;

			if ($field[0] == 'type')
				$typeField = $field;
		}
		if (empty($pkField) || empty($typeField))
			return $result;

		foreach ($value['rows'] as $row) {
			$type = $row['type'];
			$data = null;
			if ($fnGetTypeData != null)
				list($type, $data) = $fnGetTypeData($type);

			$item = [
				'id'				=> $row[$pkField[0]],
				'label'			=> $row['name'],
				'type'			=> $type,
				'data'			=> $data,
				'mandatory'	=> $row['mandatory'] ?? 0,
			];

			$result['list'][] = $item;
		}

		$result['count'] = count($value['rows']);

		return $result;
	}

	public static function formatParamsDataAsTable(
		$extraParamsData,
		$extraParamsSchema,
		?Closure $fnGetValue = null
	) {
		/*
			$extraParamsData:
				{
					"3": "2024/7/30",
					"4": "26"
				}

			$extraParamsSchema:
				{
					"rows": [
						{"id": "3", "name": "تاریخ صدور", "type": "date"},
						{"id": "4", "name": "کانون مربوطه", "type": "mha:kanoon", "mandatory": "1"}
					],
					"lastid": 4
				}
		*/

		if (empty($extraParamsData) || empty($extraParamsSchema))
			return '';

		if (is_string($extraParamsData))
			$extraParamsData = json_decode($extraParamsData, true);

		if (is_string($extraParamsSchema))
			$extraParamsSchema = json_decode($extraParamsSchema, true);

		//todo: find pk : ['id'] -> [pk]
		$schemaMap = [];
		foreach($extraParamsSchema['rows'] as $row) {
			$schemaMap[$row['id']] = $row;
		}

		//begin table
		$contents = [
			Html::beginTag('table', ['class' => ['table', 'table-bordered', 'table-striped', 'w-100']]),
		];

		//header row
		$contents[] = Html::beginTag('thead');
		$contents[] = Html::beginTag('tr');
		$contents[] = Html::tag('th', 'پارامتر');
		$contents[] = Html::tag('th', 'مقدار');
		$contents[] = Html::endTag('tr');
		$contents[] = Html::endTag('thead');

		//data rows
		foreach ($extraParamsData as $id => $value) {
			$contents[] = Html::beginTag('tr');

			$contents[] = Html::beginTag('td');
			$contents[] = $schemaMap[$id]['name'];
			$contents[] = Html::endTag('td');

			$contents[] = Html::beginTag('td');
			switch ($schemaMap[$id]['type']) {
				case 'text':
					// $value = $row;
					break;

				case 'date':
					$value = Yii::$app->formatter->asJalali($value);
					break;

				case 'time':
					// $value = $row;
					break;

				default:
					$value = $fnGetValue($value, $schemaMap[$id]);
					break;
			}
			$contents[] = $value;
			$contents[] = Html::endTag('td');

			$contents[] = Html::endTag('tr');
		}

		//end table
		$contents[] = Html::endTag('table');

		//render
		return implode("\n", $contents);
	}

}
