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
		$config['pattern'] = rtrim($prefix . '/' . strtr($pattern, $this->tokens), '/');
		$config['route'] = $action;
		$config['suffix'] = $this->suffix;

		return Yii::createObject($config);
	}

}
