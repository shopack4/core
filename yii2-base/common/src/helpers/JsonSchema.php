<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\common\helpers;

class JsonField
{
	public $owner;

	public $name;
	public $type;
	public $default;
	public $pk;
	public $label;

	public function __construct($_owner, $_name)
	{
		$this->owner = $_owner;
		$this->name = $_name;
	}
	public function type($_type)
	{
		$this->type = $_type;
		return $this;
	}
	public function default($_default)
	{
		$this->default = $_default;
		return $this;
	}
	public function pk()
	{
		$this->pk = true;
		return $this;
	}
	public function label($_label)
	{
		$this->label = $_label;
		return $this;
	}

	public function asArray()
	{
		$ret = array_filter([
			/* 0 */			 $this->name,
			'type'		=> $this->type,
			'default'	=> $this->default,
			'pk'			=> $this->pk,
			'label'		=> $this->label,
		]);

		return [
			'fields' => $ret,
		];
	}

	//next field in owner children
	public function field($name)
	{
		return $this->owner->field($name);
	}
	public function done()
	{
		return $this->owner->done();
	}
}

class JsonSchema
{
	const TYPE_number		= 'num';
	const TYPE_uuid			= 'uuid';
	const TYPE_string		= 'str';
	const TYPE_boolean	= 'bool';
	const TYPE_select		= 'select';

	public $fields = [];

	public static function create()
	{
		return new self();
	}

	public function done()
	{
		$arr = [];

		foreach ($this->fields as $f) {
			$arr[] = $f->asArray();
		}

		return $arr;
	}

	public function field($name)
	{
		$f = new JsonField($this, $name);

		$this->fields[] = $f;

		return $f;
	}
}
