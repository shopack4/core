<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\PerModuleMigration;

class m231231_000000_accounting_change_slbAvailableFromDate_notnull extends PerModuleMigration
{
  public function safeUp()
  {
    $this->execute(<<<SQL
ALTER TABLE `tbl_{{MODULE}}_Accounting_Saleable`
	CHANGE COLUMN `slbAvailableFromDate` `slbAvailableFromDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `slbDesc`;
SQL
    );

    $this->execute("DROP TRIGGER IF EXISTS `trg_tbl_{{MODULE}}_Accounting_Saleable_before_insert`;");
    $this->execute(<<<SQL
CREATE TRIGGER `trg_tbl_{{MODULE}}_Accounting_Saleable_before_insert` BEFORE INSERT ON `tbl_{{MODULE}}_Accounting_Saleable` FOR EACH ROW BEGIN
	IF NEW.slbCode IS NULL THEN
		SET NEW.slbCode = UUID();
	END IF;
END
SQL
    );

  }

  public function safeDown()
  {
    echo "m231231_000000_accounting_change_slbAvailableFromDate_notnull cannot be reverted.\n";
    return false;
  }

}
