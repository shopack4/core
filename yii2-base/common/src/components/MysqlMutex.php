<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\components;

class MysqlMutex extends \yii\mutex\MysqlMutex
{
	//override
	public function isAcquired($name)
	{
		return $this->db->useMaster(function ($db) use ($name) {
			/** @var \yii\db\Connection $db */
			$nameData = $this->prepareName();
			return (bool)$db->createCommand(
				'SELECT IF(IS_USED_LOCK(' . $nameData[0] . ') IS NULL, 0, 1), :prefix',
				array_merge(
					[':name' => $this->hashLockName($name), ':prefix' => $this->keyPrefix],
					$nameData[1]
				)
			)->queryScalar();
		});
	}
}
