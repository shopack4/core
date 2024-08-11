<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\components;

class FileMutex extends \yii\mutex\FileMutex
{
	// public function isAcquired($name)
	// {
	// 	return isset($this->_files[$name]);
	// }

}
