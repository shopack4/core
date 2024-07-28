<?php
/**
 * This view is used by console/controllers/MigrateController.php.
 *
 * The following variables are available in this view:
 */
/* @var $className string the new migration class name without namespace */
/* @var $namespace string the new migration class namespace */

echo <<<PHP
<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

PHP;

if (!empty($namespace)) {
	echo "\nnamespace {$namespace};\n";
}
?>

use shopack\base\common\db\Migration;

class <?= $className ?> extends Migration
{
	public function safeUp()
	{
		throw new \Exception('not completed yet!');

    $this->execute(<<<SQL
SQL
		);

	}

	public function safeDown()
	{
		echo "<?= $className ?> cannot be reverted.\n";
		return false;
	}

	/*
	// Use up()/down() to run migration code without a transaction.
	public function up()
	{
	}

	public function down()
	{
		echo "<?= $className ?> cannot be reverted.\n";
		return false;
	}
	*/

}
