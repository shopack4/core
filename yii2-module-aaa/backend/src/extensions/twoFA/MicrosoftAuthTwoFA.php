<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\aaa\backend\extensions\twoFA;

use shopack\aaa\backend\classes\twoFA\BaseTwoFA;
use shopack\aaa\backend\classes\twoFA\ITwoFA;

class MicrosoftAuthTwoFA
	extends BaseTwoFA
	implements ITwoFA
{
	public function generate(?array $args = [])
	{
		return true;
	}

	public function validate(?array $args = [])
	{
		return false;
	}

}
