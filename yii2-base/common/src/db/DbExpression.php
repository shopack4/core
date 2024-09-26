<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\db;

class DbExpression extends \yii\db\Expression
{
	public static function now()
	{
		return new self('CURRENT_TIMESTAMP');
	}

	public static function null()
	{
		return new self('NULL');
	}

	public static function notNull()
	{
		return new self('NOT NULL');
	}

	public static function concat(...$parts)
	{
		return new self('CONCAT(' . implode(',', $parts) . ')');
	}

}
