<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\rest;

interface ActiveRecordInterface {
	public function primaryKeyValue();
	public function columnsInfo();
}
