<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\helpers;

class ExceptionHelper
{
	public static function CheckDuplicate(\Throwable $exp, $model)
	{
		$msg = $exp->getMessage();

		if (stripos($msg, 'duplicate entry') === false)
			return $msg;

		$origmsg = $msg;

		$pos = strpos($origmsg, 'The SQL being executed was:');
		if ($pos !== false)
			$origmsg = substr($origmsg, 0, $pos);

		$msg = ['DUPLICATE'];

		foreach ($model->attributes() as $attr) {
			if (strpos($origmsg, $attr) !== false)
				$msg[] = $attr;
		}

		$msg = implode(' ', $msg);

		return $msg;
	}

}

