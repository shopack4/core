<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\rest;

use Yii;

class UrlRule extends \yii\rest\UrlRule
{
	public $patterns = [
		'GET,HEAD'				=> 'index',
		'GET,HEAD {id}'		=> 'view',
		'POST'						=> 'create',
		'PUT,PATCH {id}'	=> 'update',
		'DELETE {id}'			=> 'delete',
		'UNDELETE {id}'		=> 'undelete',
		'{id}'						=> 'options',
		''								=> 'options',
	];

	public $predefinedTokens = [
		'{_id}' => '\\d[\\d,]*',
		'{_uuid}' => '('
				. '\{[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\}'
			. '|'
				. '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'
			. '|'
				. '\{[0-9a-f]{8}[0-9a-f]{4}[0-9a-f]{4}[0-9a-f]{4}[0-9a-f]{12}\}'
			. '|'
				. '[0-9a-f]{8}[0-9a-f]{4}[0-9a-f]{4}[0-9a-f]{4}[0-9a-f]{12}'
			. ')',
	];

	public $tokens = [
		'{id}' => '<id:{_id}>',
	];

	protected function createRule($pattern, $prefix, $action)
	{
		$verbs = 'GET|HEAD|POST|PUT|PATCH|DELETE|UNDELETE|OPTIONS';
		if (preg_match("/^((?:($verbs),)*($verbs))(?:\\s+(.*))?$/", $pattern, $matches)) {
			$verbs = explode(',', $matches[1]);
			$pattern = isset($matches[4]) ? $matches[4] : '';
		} else {
			$verbs = [];
		}

		$config = $this->ruleConfig;
		$config['verb'] = $verbs;

		$pattern = rtrim($prefix . '/' . strtr($pattern, $this->tokens), '/');
		$config['pattern'] = rtrim(strtr($pattern, $this->predefinedTokens), '/');

		$config['route'] = $action;
		$config['suffix'] = $this->suffix;

		return Yii::createObject($config);
	}

}
