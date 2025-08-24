<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\console\controllers;

use Yii;
use yii\db\Connection;
use yii\di\Instance;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\helpers\FileHelper;

class MigrateController extends \yii\console\controllers\MigrateController
{
	public $templateFile = '@shopack/base/common/console/views/migration.php';
	public $isPerModule = false;

	public function init()
	{
		parent::init();

		if ($this->isPerModule)
			$this->templateFile = '@shopack/base/common/console/views/perModuleMigration.php';
	}

	public function beforeAction($action)
	{
		if (Controller::beforeAction($action)) {
			$this->db = Instance::ensure($this->db, Connection::className());

			if (empty($this->migrationNamespaces) && empty($this->migrationPath)) {
				throw new InvalidConfigException('At least one of `migrationPath` or `migrationNamespaces` should be specified.');
			}

			$this->migrationNamespaces = (array) $this->migrationNamespaces;

			foreach ($this->migrationNamespaces as $key => $value) {
				$this->migrationNamespaces[$key] = trim($value, '\\');
			}

			if (is_array($this->migrationPath)) {
				$migrationPath = $this->migrationPath;
				$this->migrationPath = [];

				foreach ($migrationPath as $i => $path) {
					if (is_array($path)) {
						$this->migrationPath[Yii::getAlias($i)] = $path;
					} else {
						$this->migrationPath[] = Yii::getAlias($path);
					}
				}
			} elseif ($this->migrationPath !== null) {
				$path = Yii::getAlias($this->migrationPath);
				if (!is_dir($path)) {
					if ($action->id !== 'create') {
						throw new InvalidConfigException("Migration failed. Directory specified in migrationPath doesn't exist: {$this->migrationPath}");
					}
					FileHelper::createDirectory($path);
				}
				$this->migrationPath = $path;
			}

			$version = Yii::getVersion();
			$this->stdout("Yii Migration Tool (based on Yii v{$version})\n\n");

			return true;
		}

		return false;
	}

	protected function getNewMigrations()
	{
		$applied = [];
		foreach ($this->getMigrationHistory(null) as $class => $time) {
			$applied[trim($class, '\\')] = true;
		}

		$migrationPaths = [];
		if (is_array($this->migrationPath)) {
			foreach ($this->migrationPath as $k => $path) {
				if (is_array($path)) {
					$migrationPaths[] = [$k, '', $path];
				} else {
					$migrationPaths[] = [$path, '', null];
				}
			}
		} elseif (!empty($this->migrationPath)) {
			$migrationPaths[] = [$this->migrationPath, '', null];
		}

		foreach ($this->migrationNamespaces as $namespace) {
			$migrationPaths[] = [$this->getNamespacePath($namespace), $namespace, null];
		}

		$migrations = [];
		foreach ($migrationPaths as $item) {
			list($migrationPath, $namespace, $modules) = $item;
			if (!file_exists($migrationPath)) {
				continue;
			}
			$handle = opendir($migrationPath);
			while (($file = readdir($handle)) !== false) {
				if ($file === '.' || $file === '..') {
					continue;
				}
				$path = $migrationPath . DIRECTORY_SEPARATOR . $file;
				if (preg_match('/^(m(\d{6}_?\d{6})\D.*?)\.php$/is', $file, $matches) && is_file($path)) {
					$class = $matches[1];
					$time = str_replace('_', '', $matches[2]);

					if (!empty($namespace)) {
						$class = $namespace . '\\' . $class;
					}

					if (empty($modules) == false) {
						foreach ($modules as $module) {
							$class = $class . "@" . $module;
							if (!isset($applied[$class])) {
								$migrations[$time . '\\' . $class] = $class;
							}
						}
					} else {
						if (!isset($applied[$class])) {
							$migrations[$time . '\\' . $class] = $class;
						}
					}
				}
			}
			closedir($handle);
		}
		ksort($migrations);

		return array_values($migrations);
	}

	protected function createMigration($class)
	{
		list ($_class, $module) = explode("@", $class, 2);

		$this->includeMigrationFile($_class);

		$migration = Yii::createObject($_class);
		$migration->currentModuleName = $module;

		if ($migration instanceof BaseObject && $migration->canSetProperty('compact')) {
			$migration->compact = $this->compact;
		}

		return $migration;
	}
}
