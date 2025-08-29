<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\db;

use shopack\base\common\helpers\StringHelper;

class Migration extends \yii\db\Migration
{
	// public function up()
	// {
	// return $this->safeUp();
	// }

	// public $compact = true;

	// public function init()
	// {
	// parent::init();
	// $this->compact = true;
	// }

	protected function beginCommand($description)
	{
		if (!$this->compact) {
			$p = strpos($description, "\n");
			if ($p)
				$description = substr($description, 0, $p - 1);
			echo "    > $description ...";
		}

		return microtime(true);
	}

	public function batchInsertIgnore($table, $columns, $rows)
	{
		$time = $this->beginCommand("insert ignore into $table");
		$cmd = $this->db->createCommand()->batchInsert($table, $columns, $rows);
		$sql = $cmd->getRawSql();
		$sql = 'INSERT IGNORE' . substr($sql, 6);
		$cmd->setRawSql($sql);
		// echo "\n\n$sql\n\n";
		$cmd->execute();
		$this->endCommand($time);
	}

	public function queryAll($sql, $params = [])
	{
		$sqlOutput = $sql;
		if ($this->maxSqlOutputLength !== null) {
			$sqlOutput = StringHelper::truncate($sql, $this->maxSqlOutputLength, '[... hidden]');
		}

		$time = $this->beginCommand("execute SQL: $sqlOutput");
		$result = $this->db->createCommand($sql)->bindValues($params)->queryAll();
		$this->endCommand($time);

		return $result;
	}

	public function queryOne($sql, $params = [])
	{
		$sqlOutput = $sql;
		if ($this->maxSqlOutputLength !== null) {
			$sqlOutput = StringHelper::truncate($sql, $this->maxSqlOutputLength, '[... hidden]');
		}

		$time = $this->beginCommand("execute SQL: $sqlOutput");
		$result = $this->db->createCommand($sql)->bindValues($params)->queryOne();
		$this->endCommand($time);

		return $result;
	}
}
