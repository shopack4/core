// console.log('createDynamicParamsFormUI 2.0.0');

function array_isset(arr) {
	var i, max_i;
	for (i = 1, max_i = arguments.length; i < max_i; i++) {
		arr = arr[arguments[i]];
		if (arr === undefined) {
			return false;
		}
	}
	return true;
}

function createDynamicParamsFormUI(
	selectedID,
	loadingText,
	getParamsUrl,
	formID,
	paramID,
	formName,
	paramName,
	initialData,
	paramsContainerID,
	labelSpan = 2,
	emptyParamsHint = null
) {
	var paramsContainer = $('#' + paramsContainerID);
	paramsContainer.show();
	paramsContainer.empty();
	paramsContainer.html(loadingText);

	if (getParamsUrl.indexOf('__SEL__') >= 0)
		getParamsUrl = getParamsUrl.replace('__SEL__', selectedID);
	else
		getParamsUrl = getParamsUrl + selectedID;

	// console.log({getParamsUrl, initialData});

	$.ajax(
		getParamsUrl,
		{
			dataType: 'json',
			method: 'POST'
		})
		.done(function(response) {
// console.log(response);
			if (response.count == 0) {
				// paramsContainer.html("<div style='text-align:center'>فاقد پارامتر.</div>");
				if ((emptyParamsHint != null) && (emptyParamsHint != ''))
					paramsContainer.html(emptyParamsHint);
				else {
					paramsContainer.empty();
					paramsContainer.hide();
				}
			} else {
				out = '';
				response.list.forEach(function(val, index, arr) {
					if (formID == null)
						_id = paramID + "-" + val['id'];
					else
						_id = formID + "-" + paramID + "-" + val['id'];

					if (formName == null)
						_name = paramName + "[" + val['id'] + "]";
					else
						_name = formName + "[" + paramName + "][" + val['id'] + "]";

					out += createDynamicParamsFormField(formName, val, _id, _name, initialData, labelSpan);
				}, out);
				paramsContainer.html(out);
			}
		})
		.fail(function(jqXHR, exception) {
			// Our error logic here
			var msg = '';
			if (jqXHR.status === 0)
				msg = 'Not connect. Verify Network.';
			else if (jqXHR.status == 404)
				msg = 'Requested page not found. [404]';
			else if (jqXHR.status == 500)
				msg = 'Internal Server Error [500].';
			else if (exception === 'parsererror')
				msg = 'Requested JSON parse failed.';
			else if (exception === 'timeout')
				msg = 'Time out error.';
			else if (exception === 'abort')
				msg = 'Ajax request aborted.';
			else
				msg = 'Uncaught Error.' + jqXHR.responseText;
			paramsContainer.html('<pre>' + msg + '<br>' + jqXHR.responseText + '</pre>');
		})
	;
}

function createDynamicParamsFormField(
	formName,
	val,
	_id,
	_name,
	initialData,
	labelSpan = 2
) {
	var init_val = null;
	if (initialData !== undefined) {
		if (initialData[val['id']] !== undefined)
			init_val = initialData[val['id']];
		else if (initialData[_name] !== undefined)
			init_val = initialData[_name];
	}

	if ((init_val == null) && (val['init_val'] !== undefined))
		init_val = val['init_val'];

	var valueSpan = 12 - labelSpan;

	var inputContent = '';
	var scriptContent = '';

	function render_section()
	{
		inputContent += "<h4 class='form-section'>";
		inputContent += val['label'];
		inputContent += "</h4>";
	}
	function render_input()
	{
		inputContent += "<input type='" + (val['type'] == 'password' ? 'password' : 'text') + "' class='form-control'"
			+ "id='" + _id + "' "
			+ "name='" + _name + "'";
		if (init_val != null)
			inputContent += " value='" + init_val + "'";
		else if (val['default'] !== undefined)
			inputContent += " value='" + val['default'] + "'";

		var style = '';
		if (val['style'] !== undefined)
			style = val['style'];

		if (val['type'] == 'number') {
			if (style != '')
				style += ';';
			style += 'direction:ltr;';
		}

		if (style != '')
			inputContent += " style='" + style + "'";

		inputContent += ">";
	}
	function render_textArea()
	{
		inputContent += "<textarea class='form-control'"
			+ "id='" + _id + "' "
			+ "name='" + _name + "'";

		if (val['style'] !== undefined)
			inputContent += " style='" + val['style'] + "'";

		if (val['rows'] !== undefined)
			inputContent += " rows='" + val['rows'] + "'";

		inputContent += ">";

		if (init_val != null)
			inputContent += init_val;
		else if (val['default'] !== undefined)
			inputContent += val['default'];

		inputContent += "</textarea>";
	}
	function render_checkbox()
	{
		// inputContent += "<input type='checkbox' value='1'"
			// + "id='" + _id + "' "
			// + "name='" + _name + "'";
		// if ((init_val != null) && (init_val == '1'))
			// inputContent += " checked";
		// else if ((val['default'] !== undefined) && (val['default'] == true))
			// inputContent += " checked";
		// inputContent += ">";

		inputContent += "<label class='radio-inline'>"
			+ "<input type='radio' value='1'"
			// + "id='" + _id + "' "
			+ "name='" + _name + "'";

		if ((init_val != null) && (init_val == '1'))
			inputContent += " checked";
		else if ((val['default'] !== undefined) && (val['default'] == 1))
			inputContent += " checked";

		inputContent += ">";
		inputContent += "بلی";
		inputContent += "</label>";

		inputContent += "<label class='radio-inline'>"
			+ "<input type='radio' value='0'"
			// + "id='" + _id + "' "
			+ "name='" + _name + "'";

		if ((init_val != null) && (init_val == '0'))
			inputContent += " checked";
		else if ((val['default'] !== undefined) && (val['default'] == 0))
			inputContent += " checked";

		inputContent += ">";
		inputContent += "خیر";
		inputContent += "</label>";
	}
	function render_select()
	{
		inputContent += "<select class='form-control'"
			+ "id='" + _id + "' "
			+ "name='" + _name + "'"
			+ ">";
		options = '';
		if (val['allowNone'] !== undefined) {
			options += "<option value=''>" + val['allowNone'] + "</option>";
		}

		data = val['data'];
// console.log(val);
// console.log(init_val);
// console.log(data);
// console.log(initialData);
// console.log(typeof data);
		if (data.length > 0) { //array
			for (i=0; i<data.length; i++) {
				//this is for CategoryModel::getListForDropdown -> browsers reorder array keys
				if (data[i].key === undefined)
					options += "<option value='" + i + "'>" + data[i] + "</option>";
				else
					options += "<option value='" + data[i].key + "'>" + data[i].value + "</option>";
			}
		} else { //object
			for (var v in data) {
				options += "<option value='" + v + "'>" + data[v] + "</option>";
			}
		}

		if (init_val != null)
			options = options.replace('value=\'' + init_val + '\'', 'value=\'' + init_val + '\' selected');
		else if (val['default'] !== undefined)
			options = options.replace('value=\'' + val['default'] + '\'', 'value=\'' + val['default'] + '\' selected');

		inputContent += options;
		inputContent += "</select>";
	}
	function render_radioList()
	{
		data = val['data'];
// console.log(val);
// console.log(data);
// console.log(initialData);

		inputContent += "<div class='form-control' style='padding-top:0; padding-bottom:0;'>";
		for (var v in data) {
			inputContent += "<label class='radio-inline'>"
				+ "<input type='radio' value='" + v + "'"
				// + "id='" + _id + "' "
				+ "name='" + _name + "'";

			if ((init_val != null) && (init_val == v))
				inputContent += " checked";
			else if ((val['default'] !== undefined) && (val['default'] == v))
				inputContent += " checked";

			inputContent += ">";
			inputContent += data[v];
			inputContent += "</label>";
		}
		inputContent += "</div>";
	}
	function render_multiSelect()
	{
		data = val['data'];
// console.log(val);
// console.log(data);
// console.log(initialData);
		if (data.length > 0) { //array
			for (i=0; i<data.length; i++) {
				// inputContent += "<input type='checkbox' value='" + i + "'"
					// + "id='" + _id + "' "
					// + "name='" + _name + "'";
				// if ((init_val != null) && (init_val == '1'))
					// inputContent += " checked";
				// else if ((val['default'] !== undefined) && (val['default'] == true))
					// inputContent += " checked";
				// inputContent += ">";

				// options += "<option value='" + i + "'>" + data[i] + "</option>";
			}
		} else { //object
			for (var v in data) {
// if (Array.isArray(val['default']))
// {
// console.log(v);
// console.log(val);
// console.log(Array.isArray(val['default']));
// console.log(val['default'][v]);
// console.log($.inArray(v, val['default']));
// }
				// options += "<option value='" + v + "'>" + data[v] + "</option>";
				inputContent += "<div>";
				inputContent += "<input type='checkbox' value='1'"
					+ "id='" + _id + "-" + v + "' "
					+ "name='" + _name + "[" + v + "]'";

				if (initialData !== undefined) {
					if ((init_val != null) && (init_val[v] !== undefined) && (init_val[v] == '1'))
						inputContent += " checked";
				} else if (val['default'] !== undefined) {
					if ((Array.isArray(val['default'])
								&& ((val['default'][v] !== undefined) || ($.inArray(v, val['default']) != -1)))
							|| (val['default'] === v)
						)
						inputContent += " checked";
				}

				inputContent += ">";
				inputContent += "&nbsp;<label class='control-label' for='" + _id + "-" + v + "'>" + data[v] + "</label>";
				inputContent += "</div>";
			}
		}
	}
	function render_kvpMulti()
	{
		inputContent += "<table class='table table-bordered table-striped'>";
		kvptypedef = val['typedef'];

		inputContent += "<tr>";
		if (kvptypedef['enableField']) {
			inputContent += "<th>" + kvptypedef['enableField']['label'] + "</th>";
		}
		inputContent += "<th>" + kvptypedef['key']['label'] + "</th>";
		kvptypedef['value'].forEach(element => {
			inputContent += "<th>" + element['label'] + "</th>";
		});
		inputContent += "</tr>";

		dataindex = 0;

		if (init_val != null)
			dataindex = init_val.length;

		for (i=0; i<dataindex+3; i++) {
			inputContent += "<tr>";

			if (kvptypedef['enableField']) {
				if (kvptypedef['id'])
					enableFieldId = kvptypedef['id'];
				else
					enableFieldId = 'enable';

				inputContent += "<td>";
				inputContent += "<input type='checkbox' value='1'"
					+ " id='" + _id + "-" + i + "-" + enableFieldId + "'"
					+ " name='" + _name + "[" + i + "][" + enableFieldId + "]" + "'";
				if (i < dataindex) {
					if ((init_val != null) && (init_val[i][enableFieldId] !== undefined)
							&& init_val[i][enableFieldId])
						inputContent += " checked";
				} else
					inputContent += " checked";
				inputContent += ">";
				inputContent += "</td>";
			}

			inputContent += "<td>";
			inputContent += "<input type='text' class='form-control'"
				+ " id='" + _id + "-" + i + "-key" + "'"
				+ " name='" + _name + "[" + i + "][key]" + "'";
			if (i < dataindex) {
				if (init_val != null)
					inputContent += " value='" + init_val[i].key + "'";
			}
			inputContent += ">";
			inputContent += "</td>";

			kvptypedef['value'].forEach(element => {
				inputContent += "<td>";
				inputContent += "<input type='text' class='form-control'"
					+ " id='" + _id + "-" + i + "-value-" + element['id'] + "'"
					+ " name='" + _name + "[" + i + "][value][" + element['id'] + "]'";
				if (i < dataindex) {
					if (init_val != null)
						inputContent += " value='" + init_val[i].value[element['id']] + "'";
				}
				inputContent += ">";
				inputContent += "</td>";
			});

			inputContent += "</tr>";
		}

		inputContent += "</table>";
	}
	function render_datetime()
	{
		strvalue = '';
		if (init_val != null)
			strvalue = "value='" + init_val + "' ";
		else if (val['default'] !== undefined)
			strvalue += "value='" + val['default'] + "' ";

		containerID = "date-" + _id;

		inputContent += "<input type='text' "
			+ "id='" + containerID + "' "
			// + "name='" + _name + "-date' "
			+ "readonly='readonly' "
			+ "format='YYYY/MM/DD' "
			+ "altformat='YYYY/MM/DD' "
			+ "altfield='#" + _id + "' "
			+ "autoclose "
			+ "observer "
			+ "theme='default' "
			+ "autocomplete='off' "
			+ strvalue
			+ "class='form-control' "
		;

		var style = '';
		if (val['style'] !== undefined)
			style = val['style'];

		if (style != '')
			inputContent += "style='" + style + "' ";

		inputContent += ">\n";

		inputContent += "<input type='hidden' "
			+ "id='" + _id + "' "
			+ "name='" + _name + "' "
			+ strvalue
			+ ">\n";

		dpVarID = "datepicker_" + _id.replaceAll("-", "_");

		// scriptContent += "window.persianDatepickerDebug=true;\n";
		scriptContent += dpVarID + "=$('#" + containerID + "').persianDatepicker({ "
			+ "'format':'YYYY/MM/DD',"
			+ "'altFormat':'YYYY/MM/DD',"
			+ "'class':'form-control',"
			+ "'autoClose':true,"
			+ "'observer':true,"
			+ "'readonly':'readonly',"
			+ "'theme':'default',"
			+ "'initialValue':false,"
			+ "'altField':'#" + _id + "',"
			+ "'autocomplete':'off',"
			+ "'onSelect':function onSelect(unix) { $('#" + containerID + "').trigger('change'); },\n"
			+ "'onSet':function onSet(unix) { $('#" + containerID + "').trigger('change'); },\n"
			+ "'altFieldFormatter':function(unixDate) {\n"
			+ "		var self = this,\n"
			+ "			thisAltFormat = self.altFormat.toLowerCase();\n"
			+ "		if (thisAltFormat === 'gregorian' || thisAltFormat === 'g')\n"
			+ "			return new Date(unixDate);\n"
			+ "		if (thisAltFormat === 'unix' || thisAltFormat === 'u')\n"
			+ "			return unixDate;\n"
			+ "		var dt = new Date(unixDate);\n"
			+ "		return dt.getFullYear() + '/' + (dt.getMonth()+1) + '/' + dt.getDate();\n"
			+ "	}\n"
			+ "});\n";
			scriptContent += "$('#" + containerID + "').bind('change', function() { if ($(this).val() == '') $('#" + _id + "').val(''); } );\n";
			// scriptContent += "$('#" + containerID + "').bind('remove', function() { " + dpVarID + ".destroy(); } );\n";

		if (array_isset(val, 'fieldOptions') == false)
			val['fieldOptions'] = [];

		if (array_isset(val, 'fieldOptions', 'addon') == false)
			val['fieldOptions']['addon'] = [];

		if (array_isset(val, 'fieldOptions', 'addon', 'append') == false)
			val['fieldOptions']['addon']['append'] = [];

		val['fieldOptions']['addon']['append'].push({
			'asButton' : 1,
			'content' : "" //"<span class='input-group-text' style='padding:0'>"
				+ "<button type='button' "
					+ "id='btn-" + dpVarID + "-clear' "
					+ "class='btn btn-sm' "
					+ "onclick='clearDatepicker();' "
					+ "data-hdn-id='" + _id + "' "
					+ "data-cntr-id='" + containerID + "' "
					+ "data-datepicker-id='" + dpVarID + "' "
				+ ">x</button>"
				//+ "</span>"
		});
	}

	if (val['type'] == 'section') {
		render_section();
		templatedContent = '<div class="col-sm-12">' + templatedContent + '</div>';
	} else {
		var inputContent = '';
		if (['string', 'text', 'password', 'number'].includes(val['type'])) {
			render_input();
		} else if (['multi-string', 'multi-text'].includes(val['type'])) {
			render_textArea();
		} else if (['bool', 'boolean'].includes(val['type'])) {
			render_checkbox();
		} else if (['dropdown', 'combo', 'select'].includes(val['type'])) {
			render_select();
		} else if (val['type'] == 'radio-list') {
			render_radioList();
		} else if (val['type'] == 'multi-select') {
			render_multiSelect();
		} else if (val['type'] == 'kvp-multi') { //key-value-pair
			render_kvpMulti();
		} else if (['date', 'time', 'datetime'].includes(val['type'])) {
			render_datetime();
		}

		var prepend = '';
		var append = '';

		if (array_isset(val, 'fieldOptions', 'addon', 'prepend')) {
			var v = val['fieldOptions']['addon']['prepend'];
			if (v['content'] !== undefined) {
				prepend += "<span class='input-group-text'"
					+ (v['asButton'] !== undefined ? " style='padding:0;'" : "")
					+ ">" + v['content'] + "</span>";
			} else {
				for (var i=0; i<v.length; i++) {
					var vv = v[i];
					prepend += "<span class='input-group-text'"
						+ (vv['asButton'] !== undefined ? " style='padding:0;'" : "")
						+ ">" + vv['content'] + "</span>";
				}
			}
		}

		if (array_isset(val, 'fieldOptions', 'addon', 'append')) {
			var v = val['fieldOptions']['addon']['append'];
			if (v['content'] !== undefined) {
				append += "<span class='input-group-text'"
					+ (v['asButton'] !== undefined ? " style='padding:0;'" : "")
					+ ">" + v['content'] + "</span>";
			} else {
				for (var i=0; i<v.length; i++) {
					var vv = v[i];
					append += "<span class='input-group-text'"
						+ (vv['asButton'] !== undefined ? " style='padding:0;'" : "")
						+ ">" + vv['content'] + "</span>";
				}
			}
		}

		var template = `
<div class="col-sm-12">
<div class="mb-3 row highlight-addon field-{{id}} {{required}}">
<label class="col-form-label {{has-star}} col-md-4" for="{{id}}">{{label}}</label>
<div class="col-md-8">
{{input}}
<div class='help-block'></div>
<div class="invalid-feedback"></div>
</div>
</div>
</div>
`;

		var templateWithAddon = `
<div class="col-sm-12">
<div class="mb-3 row highlight-addon form-group field-{{id}} {{required}}">
<label class="col-form-label {{has-star}} col-md-4" for="{{id}}">{{label}}</label>
<div class="col-md-8">
<div class='input-group'>
{{prepend}}{{input}}{{append}}
</div>
<div class='help-block'></div>
<div class="invalid-feedback"></div>
</div>
</div>
</div>
`;

		var templatedContent = ((prepend != '') || (append != '') ? templateWithAddon : template);

		templatedContent = templatedContent.replaceAll('{{input}}', inputContent);
		templatedContent = templatedContent.replaceAll('{{id}}', _id);
		templatedContent = templatedContent.replaceAll('{{name}}', _name);
		templatedContent = templatedContent.replaceAll('{{label}}', val['label']);

		var isMandatory = ((val['mandatory'] !== undefined) && val['mandatory']);
		templatedContent = templatedContent.replaceAll('{{required}}', isMandatory ? 'required' : '');
		templatedContent = templatedContent.replaceAll('{{has-star}}', isMandatory ? 'has-star' : '');

		templatedContent = templatedContent.replaceAll('{{prepend}}', prepend);
		templatedContent = templatedContent.replaceAll('{{append}}', append);

		if (isMandatory) {
			scriptContent += "jQuery('#" + formName + "').yiiActiveForm('add', {\n";
			scriptContent += "  'id'        : '" + _id + "',\n";
			scriptContent += "  'name'      : '" + _name + "',\n";
			scriptContent += "  'container' : '.field-" + _id + "',\n";
			scriptContent += "  'input'     : '#" + _id + "',\n";
			scriptContent += "  'error'     : '.invalid-feedback',\n";
			scriptContent += "  'validate'  : function (attribute, value, messages, deferred, $form) {\n";
			scriptContent += "    yii.validation.required(value, messages, {'message':'" + val['label'] + " نمی‌تواند خالی باشد.'});\n";
			scriptContent += "  }\n";
			scriptContent += "});\n";

			scriptContent += "$('#" + _id + "').bind('remove', function() {\n";
			scriptContent += "  jQuery('#" + formName + "').yiiActiveForm('remove', '" + _id + "');\n";
			scriptContent += "});\n";
		}

		if (scriptContent != '')
			scriptContent = "<script>\njQuery(function ($) {\n" + scriptContent + "});\n</script>\n";

// console.log(scriptContent);
	}

	return templatedContent + scriptContent;
}

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
