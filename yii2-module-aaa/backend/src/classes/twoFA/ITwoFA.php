<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\classes\twoFA;

interface ITwoFA
{
	public function generate(?array $args = []);
	public function validate(?array $args = []);
}
