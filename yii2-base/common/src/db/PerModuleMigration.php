<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\db;

class PerModuleMigration extends Migration
{
	public $isPerModule = true;
	public $currentModuleName = null;

	public const ModuleMarker = "{{MODULE}}";

	public function replaceModuleMarker($text)
	{
		if ($this->currentModuleName == null || $this->currentModuleName == "") {
			throw new \Exception("currentModuleName not defined");
		}

		return strtr($text, [
			static::ModuleMarker => strtoupper($this->currentModuleName)
		]);
	}

	public function execute($sql, $params = [])
	{
		$sql = $this->replaceModuleMarker($sql);
		parent::execute($sql, $params);
	}

	public function insert($table, $columns)
	{
		$table = $this->replaceModuleMarker($table);
		parent::insert($table, $columns);
	}

	public function batchInsert($table, $columns, $rows)
	{
		$table = $this->replaceModuleMarker($table);
		parent::batchInsert($table, $columns, $rows);
	}

	public function upsert($table, $insertColumns, $updateColumns = true, $params = [])
	{
		$table = $this->replaceModuleMarker($table);
		parent::upsert($table, $insertColumns, $updateColumns, $params);
	}

	public function update($table, $columns, $condition = '', $params = [])
	{
		$table = $this->replaceModuleMarker($table);
		parent::update($table, $columns, $condition, $params);
	}

	public function delete($table, $condition = '', $params = [])
	{
		$table = $this->replaceModuleMarker($table);
		parent::delete($table, $condition, $params);
	}

	public function createTable($table, $columns, $options = null)
	{
		$table = $this->replaceModuleMarker($table);
		parent::createTable($table, $columns, $options);
	}

	public function renameTable($table, $newName)
	{
		$table = $this->replaceModuleMarker($table);
		parent::renameTable($table, $newName);
	}

	public function dropTable($table)
	{
		$table = $this->replaceModuleMarker($table);
		parent::dropTable($table);
	}

	public function truncateTable($table)
	{
		$table = $this->replaceModuleMarker($table);
		parent::truncateTable($table);
	}

	public function addColumn($table, $column, $type)
	{
		$table = $this->replaceModuleMarker($table);
		parent::addColumn($table, $column, $type);
	}

	public function dropColumn($table, $column)
	{
		$table = $this->replaceModuleMarker($table);
		parent::dropColumn($table, $column);
	}

	public function renameColumn($table, $name, $newName)
	{
		$table = $this->replaceModuleMarker($table);
		parent::renameColumn($table, $name, $newName);
	}

	public function alterColumn($table, $column, $type)
	{
		$table = $this->replaceModuleMarker($table);
		parent::alterColumn($table, $column, $type);
	}

	public function addPrimaryKey($name, $table, $columns)
	{
		$table = $this->replaceModuleMarker($table);
		parent::addPrimaryKey($name, $table, $columns);
	}

	public function dropPrimaryKey($name, $table)
	{
		$table = $this->replaceModuleMarker($table);
		parent::dropPrimaryKey($name, $table);
	}

	public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
	{
		$table = $this->replaceModuleMarker($table);
		parent::addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete, $update);
	}

	public function dropForeignKey($name, $table)
	{
		$table = $this->replaceModuleMarker($table);
		parent::dropForeignKey($name, $table);
	}

	public function createIndex($name, $table, $columns, $unique = false)
	{
		$table = $this->replaceModuleMarker($table);
		parent::createIndex($name, $table, $columns, $unique);
	}

	public function dropIndex($name, $table)
	{
		$table = $this->replaceModuleMarker($table);
		parent::dropIndex($name, $table);
	}

	public function addCommentOnColumn($table, $column, $comment)
	{
		$table = $this->replaceModuleMarker($table);
		parent::addCommentOnColumn($table, $column, $comment);
	}

	public function addCommentOnTable($table, $comment)
	{
		$table = $this->replaceModuleMarker($table);
		parent::addCommentOnTable($table, $comment);
	}

	public function dropCommentFromColumn($table, $column)
	{
		$table = $this->replaceModuleMarker($table);
		parent::dropCommentFromColumn($table, $column);
	}

	public function dropCommentFromTable($table)
	{
		$table = $this->replaceModuleMarker($table);
		parent::dropCommentFromTable($table);
	}
}
