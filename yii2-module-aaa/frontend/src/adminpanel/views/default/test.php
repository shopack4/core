<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

/** @var yii\web\View $this */

$this->title = Yii::t('aaa', 'Test');
$this->params['breadcrumbs'][] = Yii::t('aaa', 'System');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="test w-100">
  <div id='div_1'></div>
  <div id='div_2'></div>
  <div id='div_3'></div>
  <div id='div_4'></div>
  <div id='div_5'></div>

<?php
  $js =<<<JS
function runTest(id)
{
  console.log('run test (' + id + ')');

	$.ajax({
		url: 'test-multi-request-per-one-session?id=' + id,
		type: "POST",
		async: true,
	})
	.done(function(result) {
    console.log('test (' + id + '): done', result);

		$('#div_' + id).html(result);
	})
	.fail(function(jqXHR, exception) {
    console.log('test (' + id + '): error', jqXHR, exception);

		// Our error logic here
		var msg = '';
		// if (jqXHR.status === 0)
			// msg = 'Not connect. Verify Network.';
		// else if (jqXHR.status == 404)
			// msg = 'Requested page not found. [404]';
		// else if (jqXHR.status == 500)
			// msg = 'Internal Server Error [500].';
		// else if (exception === 'parsererror')
			// msg = 'Requested JSON parse failed.';
		// else if (exception === 'timeout')
			// msg = 'Time out error.';
		// else if (exception === 'abort')
			// msg = 'Ajax request aborted.';
		// else
			// msg = 'Uncaught Error: ' + jqXHR.responseText;
			msg = '<p class="color-red">' + jqXHR.responseText + '</p>';
    $('#div_' + id).html(msg);
	})
	;
}

jQuery(function($) {
  runTest(1);
  runTest(2);
  runTest(3);
  runTest(4);
  runTest(5);
});
JS;

  $this->registerJs($js);
?>
</div>
