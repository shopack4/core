<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\interface\accounting\common\enums;

use shopack\base\common\base\BaseEnum;

abstract class enuUserAssetStatus extends BaseEnum
{
  const Active 		= 'A';
	// Inactive 'D'
  const Removed 	= 'R';
  const Draft 		= 'D'; //todo: migrate from 'D' to 'F'
  const Pending 	= 'P';
  const Blocked 	= 'B';
  // const Error			= 'E';

	public static $messageCategory = 'aaa';

	public static $list = [
		[
			self::Active		=> 'Active',
			self::Removed 	=> 'Removed',
			self::Draft			=> 'Draft',
			self::Pending		=> 'Pending',
			self::Blocked		=> 'Blocked',
			// self::Error 		=> 'Error',
		],
		'form' => [
			self::Active,
			self::Draft,
			self::Pending,
			self::Blocked,
			// self::Error,
		],
	];

};
