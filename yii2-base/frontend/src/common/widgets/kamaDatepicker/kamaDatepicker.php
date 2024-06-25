<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\common\widgets\kamaDatepicker;

use Yii;
use yii\base\InvalidParamException;
use yii\widgets\InputWidget;
use yii\web\JsExpression;
use shopack\base\frontend\common\helpers\Html;
use shopack\base\common\helpers\Json;
use shopack\base\common\helpers\ArrayHelper;

//https://www.jqueryscript.net/demo/Persian-Jalali-Calendar-Data-Picker-Plugin-With-jQuery-kamaDatepicker/

//https://github.com/pheroMona13/kamaDatepicker
// -> https://github.com/a-sadegh63/kamaDatepicker

class kamaDatePicker extends InputWidget
{
	public $containerOptions = [];
	public $dateFormat;
	public $attribute;
	public $value;
	public $allowClear = false;

	public function init()
	{
		parent::init();

		if ($this->dateFormat === null)
			$this->dateFormat = Yii::$app->formatter->dateFormat;
	}

	public function run()
	{
		$view = $this->getView();

		kamaDatepickerAsset::register($view);

		//-------------
		$hiddenID = $this->options['id'];
		echo $this->renderWidget() . "\n";

		$containerID = $this->inline ? $this->containerOptions['id'] : $this->options['id'] . '-date';
		// $options = Json::htmlEncode($this->clientOptions);
// die(var_dump($options));
		$dpVarID = 'datepicker_' . strtolower(str_replace('-', '_', $containerID));

		$js = "";
		$onSelect =<<<JS
"onSelect":function onSelect(unix) {
	\$('#{$containerID}').trigger('change');
},
JS;
		if ($this->rangeSelector !== false) {
			$dpOtherVarID = 'datepicker_' . strtolower(str_replace('-', '_', $this->rangeSelector['otherID'])) . '_date';
			$isFrom = (!isset($this->rangeSelector['isFrom']) || $this->rangeSelector['isFrom']);
			if ($isFrom) {
				$js = "var {$dpVarID}, {$dpOtherVarID};\n";
				$dt = 'minDate';
				//focus TO datepicker when FROM selected
				$toID = strtolower($this->rangeSelector['otherID']) . '-date';
				$js2 = "\$('#{$toID}').focus();";
			} else {
				$dt = 'maxDate';
				$js2 = "";
			}

			if (isset($this->clientOptions['defaultDate'])
				&& ($this->clientOptions['defaultDate'] !== null)
				&& !empty($this->clientOptions['defaultDate'])
			) {
				$d = strtotime($this->clientOptions['defaultDate']);
				$view->registerJs("{$dpOtherVarID}.options = {{$dt}: {$d}000};");
			}

			$onSelect =<<<JS
"onSelect":function onSelect(unix) {
	{$dpVarID}.touched = true;
	if ({$dpOtherVarID}) {
		if ({$dpOtherVarID}.options && {$dpOtherVarID}.options.{$dt} != unix) {
			var cachedValue = {$dpOtherVarID}.getState().selected.unixDate;
			{$dpOtherVarID}.options = {{$dt}: unix};
			if ({$dpOtherVarID}.touched) {
				{$dpOtherVarID}.setDate(cachedValue);
			}
		}
		{$js2}
	}
	\$('#{$containerID}').trigger('change');
},
JS;
		}
		$onSet =<<<JS
"onSet":function onSet(unix) {
	\$('#{$containerID}').trigger('change');
},
JS;

		if ($this->inline)
			$this->clientOptions['inline'] = true;
		$options = Json::encode($this->clientOptions);

		$__time__ = ($this->withTime ? "+ ' ' + dt.getHours() + ':' + dt.getMinutes() + ':' + dt.getSeconds()" : '');
		$js .=<<<JS
{$dpVarID} = \$('#{$containerID}').persianDatepicker($.extend({}, $options, {
{$onSelect}
{$onSet}
"altFieldFormatter":function(unixDate) {
	var self = this,
		thisAltFormat = self.altFormat.toLowerCase();
	if (thisAltFormat === 'gregorian' || thisAltFormat === 'g')
		return new Date(unixDate);
	if (thisAltFormat === 'unix' || thisAltFormat === 'u')
		return unixDate;

	var dt = new Date(unixDate);
	return dt.getFullYear() + '/' + (dt.getMonth()+1) + '/' + dt.getDate()
	{$__time__};
}
}));
\$('#{$containerID}').bind('change', function() { if (\$(this).val() == '') \$('#{$hiddenID}').val(''); } );
var v = \$('#{$containerID}').attr('defaultdate');
if ((v == null) || (v == 'undefined') || (v == '')) {
	\$('#{$containerID}').val('');
	\$('#{$hiddenID}').val('');
}
\$('#{$containerID}').bind('remove', function() { {$dpVarID}.destroy(); } );
JS;
		$view->registerJs($js, \yii\web\View::POS_END);

		if ($this->allowClear) {
			if (isset($this->field->addon['append'])) {
				if (!ArrayHelper::isIndexed($this->field->addon['append']))
					$this->field->addon['append'] = [$this->field->addon['append']];
			} else
				$this->field->addon['append'] = [];

			array_unshift($this->field->addon['append'], [
				'asButton' => true,
				'content' =>
					Html::tag('span',
						Html::button('x', [
							'id' => "btn-{$dpVarID}-clear",
							'class' => 'btn btn-sm',
							'onclick' => "clearDatepicker();",
							'data' => [
								'hdn-id' => $this->options['id'],
								'cntr-id' => $containerID,
								'datepicker-id' => $dpVarID,
							],
						]),
						[
							'class' => 'input-group-text',
							'style' => 'padding: 0',
						])
				// 'options' => [
					// 'class' => 'btn   btn-default',
				// ],
			]);

			$js =<<<JS
function clearDatepicker(e)
{
	var target = $(event.target);

	var hiddenid = target.data('hdn-id');
	var containerid = target.data('cntr-id');
	var datepickerid = target.data('datepicker-id');

	$('#' + hiddenid).val('');
	// console.log($('#' + hiddenid).val());
	$('#' + hiddenid + '-date').val('');
	if (containerid != hiddenid)
		$('#' + containerid).val('');
	// eval(datepickerid + '.clear();');
}
JS;
			$view->registerJs($js, \yii\web\View::POS_END);
		}
	}

	/**
	 * Renders the DatePicker widget.
	 * @return string the rendering result.
	 */
	protected function renderWidget()
	{
		$contents = [];

		// get formatted date value
		if ($this->hasModel())
			$value = Html::getAttributeValue($this->model, $this->attribute);
		else
			$value = $this->value;

		if (($value !== null) && !empty($value)) {
			// format value according to dateFormat
			try {
				//BUG: converted to persian digit
				//$value = Yii::$app->formatter->asDate($value, $this->dateFormat);
			} catch(InvalidParamException $e) {}

			$this->clientOptions['defaultDate'] = $value;
		}
		$this->clientOptions['initialValue'] = (($value !== null) && !empty($value));
		$this->clientOptions['altField'] = '#' . $this->options['id'];
		$this->clientOptions['autocomplete'] = 'off';
		$options = $this->clientOptions;
		//$options['value'] = $value;

		if ($this->inline === false) {
			//render a text input
			if ($this->hasModel()) {
				$opt = [
					'id' => $this->options['id'],
				];
				$contents[] = Html::activeHiddenInput($this->model, $this->attribute, $opt);
			} else {
				$this->options['id'] = $this->name;
				$opt = [
					'id' => $this->options['id'],
				];
				$contents[] = Html::hiddenInput($this->name, $value, $opt);
			}

			$ii = $this->options['id'];
			$options['id'] = $this->options['id'] = $this->options['id'] . '-date';
			array_unshift($contents, Html::textInput($this->options['id'], $value, $options));
			$options['id'] = $this->options['id'] = $ii;
		} else {
			$contents[] = Html::tag('div', null, $this->containerOptions);

			// render an inline date picker with hidden input
			if ($this->hasModel())
				$contents[] = Html::activeHiddenInput($this->model, $this->attribute, $options);
			else
				$contents[] = Html::hiddenInput($this->name, $value, $options);
		}

		return implode("\n", $contents);
	}

	protected function DatepickerBlueThemeAsset($view)
	{
		DatepickerBlueThemeAsset::register($view);
	}
	protected function DatepickerRedblackThemeAsset($view)
	{
		DatepickerRedblackThemeAsset::register($view);
	}
	protected function DatepickerDarkThemeAsset($view)
	{
		DatepickerDarkThemeAsset::register($view);
	}
	protected function DatepickerCheerupThemeAsset($view)
	{
		DatepickerCheerupThemeAsset::register($view);
	}
}
