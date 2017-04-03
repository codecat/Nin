<?php

namespace Nin\Database;

abstract class QueryBuilder
{
	protected $table = '';
	protected $method;
	protected $where;
	protected $set;
	protected $insertValues;

	public function __construct($table)
	{
		$this->table = $table;
		$this->clear();
	}

	public function clear()
	{
		$this->method = '';
		$this->where = array();
		$this->set = array();
	}

	private function setMethod($method)
	{
		$this->clear();
		$this->method = $method;
	}

	public function select() { $this->setMethod('SELECT'); return $this; }
	public function update() { $this->setMethod('UPDATE'); return $this; }
	public function insert() { $this->setMethod('INSERT'); return $this; }
	public function delete() { $this->setMethod('DELETE'); return $this; }

	public function whereAssoc($arr)
	{
		foreach($arr as $k => $v) {
			$this->where($k, $v);
		}
		return $this;
	}
	public function where($key, $value = null, $oper = '=')
	{
		if(is_array($key)) {
			return $this->whereAssoc($key);
		}
		$this->where[] = array(
			$key, $value, $oper
		);
		return $this;
	}

	public function setAssoc($arr)
	{
		foreach($arr as $k => $v) {
			$this->set($k, $v);
		}
		return $this;
	}
	public function set($key, $value)
	{
		if(is_array($key)) {
			return $this->setAssoc($key);
		}
		$this->set[] = array(
			$key, $value
		);
		return $this;
	}

	public function values($arr)
	{
		$this->insertValues[] = $arr;
		return $this;
	}

	public abstract function build();
}
