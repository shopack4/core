<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m240103_133257_aaa_create_ev_clear_tbl_SYS_ActionLogs extends Migration
{
  public function safeUp()
  {
    $this->execute("DROP EVENT IF EXISTS ev_clear_tbl_SYS_ActionLogs;");

    $this->execute(<<<SQL
CREATE EVENT `ev_clear_tbl_SYS_ActionLogs`
	ON SCHEDULE
		EVERY 1 DAY
	ON COMPLETION PRESERVE
	ENABLE
	COMMENT ''
	DO BEGIN
    DELETE FROM tbl_SYS_ActionLogs
		WHERE atlAt <= DATE_SUB(NOW(), INTERVAL 1 MONTH)
		;
END
SQL
    );

  }

  public function safeDown()
  {
    echo "m240103_133257_aaa_create_ev_clear_tbl_SYS_ActionLogs cannot be reverted.\n";
    return false;
  }

}
