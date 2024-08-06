<?php
// if (isset($_POST['done']))
// 	$done = urldecode($_POST['done']);
// else
	$done = urldecode($_GET['done']);
?>

<form id="callbackform" action="<?= $done ?>" method="post">
	<?php
		$bodyParams = Yii::$app->request->getBodyParams();

		foreach ($bodyParams as $k => $v) {
			if ($k != 'done') {
				echo '<input type="hidden" name="'
					. htmlentities($k)
					. '" value="'
					. htmlentities($v)
					. '">';
			}
		}
	?>
	<noscript><input type="submit" value="Click here if you are not redirected."/></noscript>
</form>

<script type="text/javascript">
	document.getElementById('callbackform').submit();
</script>
