// console.log('createDynamicParamsFormUI 1.2');

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

					out += createDynamicParamsFormField(val, _id, _name, initialData, labelSpan);
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

	var content = '';
	var scriptContent = '';

	function render_section()
	{
		content += "<h4 class='form-section'>";
		content += val['label'];
		content += "</h4>";
	}
	function render_input()
	{
		content += "<input type='" + (val['type'] == 'password' ? 'password' : 'text') + "' class='form-control'"
			+ "id='" + _id + "' "
			+ "name='" + _name + "'";
		if (init_val != null)
			content += " value='" + init_val + "'";
		else if (val['default'] !== undefined)
			content += " value='" + val['default'] + "'";

		var style = '';
		if (val['style'] !== undefined)
			style = val['style'];

		if (val['type'] == 'number') {
			if (style != '')
				style += ';';
			style += 'direction:ltr;';
		}

		if (style != '')
			content += " style='" + style + "'";

		content += ">";
	}
	function render_textArea()
	{
		content += "<textarea class='form-control'"
			+ "id='" + _id + "' "
			+ "name='" + _name + "'";

		if (val['style'] !== undefined)
			content += " style='" + val['style'] + "'";

		if (val['rows'] !== undefined)
			content += " rows='" + val['rows'] + "'";

		content += ">";

		if (init_val != null)
			content += init_val;
		else if (val['default'] !== undefined)
			content += val['default'];

		content += "</textarea>";
	}
	function render_checkbox()
	{
		// content += "<input type='checkbox' value='1'"
			// + "id='" + _id + "' "
			// + "name='" + _name + "'";
		// if ((init_val != null) && (init_val == '1'))
			// content += " checked";
		// else if ((val['default'] !== undefined) && (val['default'] == true))
			// content += " checked";
		// content += ">";

		content += "<label class='radio-inline'>"
			+ "<input type='radio' value='1'"
			// + "id='" + _id + "' "
			+ "name='" + _name + "'";

		if ((init_val != null) && (init_val == '1'))
			content += " checked";
		else if ((val['default'] !== undefined) && (val['default'] == 1))
			content += " checked";

		content += ">";
		content += "بلی";
		content += "</label>";

		content += "<label class='radio-inline'>"
			+ "<input type='radio' value='0'"
			// + "id='" + _id + "' "
			+ "name='" + _name + "'";

		if ((init_val != null) && (init_val == '0'))
			content += " checked";
		else if ((val['default'] !== undefined) && (val['default'] == 0))
			content += " checked";

		content += ">";
		content += "خیر";
		content += "</label>";
	}
	function render_select()
	{
		content += "<select class='form-control'"
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

		content += options;
		content += "</select>";
	}
	function render_radioList()
	{
		data = val['data'];
// console.log(val);
// console.log(data);
// console.log(initialData);

		content += "<div class='form-control' style='padding-top:0; padding-bottom:0;'>";
		for (var v in data) {
			content += "<label class='radio-inline'>"
				+ "<input type='radio' value='" + v + "'"
				// + "id='" + _id + "' "
				+ "name='" + _name + "'";

			if ((init_val != null) && (init_val == v))
				content += " checked";
			else if ((val['default'] !== undefined) && (val['default'] == v))
				content += " checked";

			content += ">";
			content += data[v];
			content += "</label>";
		}
		content += "</div>";
	}
	function render_multiSelect()
	{
		data = val['data'];
// console.log(val);
// console.log(data);
// console.log(initialData);
		if (data.length > 0) { //array
			for (i=0; i<data.length; i++) {
				// content += "<input type='checkbox' value='" + i + "'"
					// + "id='" + _id + "' "
					// + "name='" + _name + "'";
				// if ((init_val != null) && (init_val == '1'))
					// content += " checked";
				// else if ((val['default'] !== undefined) && (val['default'] == true))
					// content += " checked";
				// content += ">";

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
				content += "<div>";
				content += "<input type='checkbox' value='1'"
					+ "id='" + _id + "-" + v + "' "
					+ "name='" + _name + "[" + v + "]'";

				if (initialData !== undefined) {
					if ((init_val != null) && (init_val[v] !== undefined) && (init_val[v] == '1'))
						content += " checked";
				} else if (val['default'] !== undefined) {
					if ((Array.isArray(val['default'])
								&& ((val['default'][v] !== undefined) || ($.inArray(v, val['default']) != -1)))
							|| (val['default'] === v)
						)
						content += " checked";
				}

				content += ">";
				content += "&nbsp;<label class='control-label' for='" + _id + "-" + v + "'>" + data[v] + "</label>";
				content += "</div>";
			}
		}
	}
	function render_kvpMulti()
	{
		content += "<table class='table table-bordered table-striped'>";
		kvptypedef = val['typedef'];

		content += "<tr>";
		if (kvptypedef['enableField']) {
			content += "<th>" + kvptypedef['enableField']['label'] + "</th>";
		}
		content += "<th>" + kvptypedef['key']['label'] + "</th>";
		kvptypedef['value'].forEach(element => {
			content += "<th>" + element['label'] + "</th>";
		});
		content += "</tr>";

		dataindex = 0;

		if (init_val != null)
			dataindex = init_val.length;

		for (i=0; i<dataindex+3; i++) {
			content += "<tr>";

			if (kvptypedef['enableField']) {
				if (kvptypedef['id'])
					enableFieldId = kvptypedef['id'];
				else
					enableFieldId = 'enable';

				content += "<td>";
				content += "<input type='checkbox' value='1'"
					+ " id='" + _id + "-" + i + "-" + enableFieldId + "'"
					+ " name='" + _name + "[" + i + "][" + enableFieldId + "]" + "'";
				if (i < dataindex) {
					if ((init_val != null) && (init_val[i][enableFieldId] !== undefined)
							&& init_val[i][enableFieldId])
						content += " checked";
				} else
					content += " checked";
				content += ">";
				content += "</td>";
			}

			content += "<td>";
			content += "<input type='text' class='form-control'"
				+ " id='" + _id + "-" + i + "-key" + "'"
				+ " name='" + _name + "[" + i + "][key]" + "'";
			if (i < dataindex) {
				if (init_val != null)
					content += " value='" + init_val[i].key + "'";
			}
			content += ">";
			content += "</td>";

			kvptypedef['value'].forEach(element => {
				content += "<td>";
				content += "<input type='text' class='form-control'"
					+ " id='" + _id + "-" + i + "-value-" + element['id'] + "'"
					+ " name='" + _name + "[" + i + "][value][" + element['id'] + "]'";
				if (i < dataindex) {
					if (init_val != null)
						content += " value='" + init_val[i].value[element['id']] + "'";
				}
				content += ">";
				content += "</td>";
			});

			content += "</tr>";
		}

		content += "</table>";
	}
	function render_datetime()
	{
		strvalue = '';
		if (init_val != null)
			strvalue = "value='" + init_val + "' ";
		else if (val['default'] !== undefined)
			strvalue += "value='" + val['default'] + "' ";

		containerID = "date-" + _id;

		content += "<input type='text' "
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
			content += "style='" + style + "' ";

		content += ">\n";

		content += "<input type='hidden' "
			+ "id='" + _id + "' "
			+ "name='" + _name + "' "
			+ strvalue
			+ ">\n";

		dpVarID = "datepicker_" + _id.replaceAll("-", "_");

		scriptContent += "<script>\njQuery(function ($) {\n";
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
		scriptContent += "});\n</script>\n";

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
	} else {
		content += "<div class='mb-3 row highlight-addon form-group field-" + _id + "'>";
		content += "<label class='col-form-label col-md-" + labelSpan + "' for='" + _id + "'>" + val['label'] + "</label>";
		content += "<div class='col-md-" + valueSpan + "'>";

		// if (array_isset(val, 'fieldOptions', 'addon'))
			content += "<div class='input-group'>";

		if (array_isset(val, 'fieldOptions', 'addon', 'prepend')) {
			var v = val['fieldOptions']['addon']['prepend'];
			if (v['content'] !== undefined) {
				content += "<span class='input-group-text'"
					+ (v['asButton'] !== undefined ? " style='padding:0;'" : "")
					+ ">" + v['content'] + "</span>";
			} else {
				for (var i=0; i<v.length; i++) {
					var vv = v[i];
					content += "<span class='input-group-text'"
						+ (vv['asButton'] !== undefined ? " style='padding:0;'" : "")
						+ ">" + vv['content'] + "</span>";
				}
			}
		}

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
	}

	if (array_isset(val, 'fieldOptions', 'addon', 'append')) {
		var v = val['fieldOptions']['addon']['append'];
		if (v['content'] !== undefined) {
			content += "<span class='input-group-text'"
				+ (v['asButton'] !== undefined ? " style='padding:0;'" : "")
				+ ">" + v['content'] + "</span>";
		} else {
			for (var i=0; i<v.length; i++) {
				var vv = v[i];
				content += "<span class='input-group-text'"
					+ (vv['asButton'] !== undefined ? " style='padding:0;'" : "")
					+ ">" + vv['content'] + "</span>";
			}
		}
	}

	// if (array_isset(val, 'fieldOptions', 'addon'))
		content += "</div>";

	// content += "</div>";
	// content += "<div class='col-sm-" + labelSpan + "'>";
	content += "<div class='help-block'></div>";
	// content += "</div>";
	content += "</div>";
	content += "</div>";

	return "<div class='col-md-12'>" + content + "</div>" + scriptContent;
		// "<div class='offset-md-" + labelSpan + " col-md-" + (12 - labelSpan) + "'>"
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
