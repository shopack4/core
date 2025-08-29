<?php

/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\console\controllers;

use Yii;
use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\helpers\Console;
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

	protected function includeMigrationFile($class)
	{
		$class = trim($class, '\\');
		if (strpos($class, '\\') === false) {
			if (is_array($this->migrationPath)) {
				foreach ($this->migrationPath as $k => $path) {
					if (is_array($path)) {
						$file = $k . DIRECTORY_SEPARATOR . $class . '.php';
					} else {
						$file = $path . DIRECTORY_SEPARATOR . $class . '.php';
					}

					if (is_file($file)) {
						require_once $file;
						break;
					}
				}
			} else {
				$file = $this->migrationPath . DIRECTORY_SEPARATOR . $class . '.php';
				require_once $file;
			}
		}
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
		$parts = explode("@", $class, 2);
		$_class = $parts[0];
		$module = $parts[1] ?? null;

		$this->includeMigrationFile($_class);

		$migration = Yii::createObject($_class);
		/* >> */
		if (property_exists($migration, "currentModuleName"))
			$migration->currentModuleName = $module;
		/* << */
		if ($migration instanceof BaseObject && $migration->canSetProperty('compact')) {
			$migration->compact = $this->compact;
		}

		return $migration;
	}

	public function addMigrationHistory($version)
	{
		parent::addMigrationHistory($version);
	}

	public function isMigrationInHistory($version)
	{
		$query = (new Query())
			->select(['version', 'apply_time'])
			->from($this->migrationTable)
			->where(['version' => $version]);

		$row = $query->one($this->db);

		return (empty($row) == false);
	}

	protected function migrateUp($class)
	{
		if ($class === self::BASE_MIGRATION) {
			return true;
		}

		$this->stdout("*** applying $class\n", Console::FG_YELLOW);
		$start = microtime(true);
		$migration = $this->createMigration($class);

		/* >> */
		if ($this->isMigrationInHistory($class)) {
			$this->stdout("    applied before $class\n\n", Console::FG_YELLOW);
			return true;
		}
		/* << */

		if ($migration->up() !== false) {
			$this->addMigrationHistory($class);
			$time = microtime(true) - $start;
			$this->stdout("    applied $class (time: " . sprintf('%.3f', $time) . "s)\n\n", Console::FG_GREEN);

			return true;
		}

		$time = microtime(true) - $start;
		$this->stdout("    failed to apply $class (time: " . sprintf('%.3f', $time) . "s)\n\n", Console::FG_RED);

		return false;
	}
}
