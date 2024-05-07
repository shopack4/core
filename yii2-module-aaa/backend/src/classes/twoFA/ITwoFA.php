<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\classes\twoFA;

interface ITwoFA
{
	public function generate($userID, ?array $args = []);
	public function validate($userID, ?array $args = []);
}
